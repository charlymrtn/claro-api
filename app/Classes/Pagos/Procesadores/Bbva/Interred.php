<?php

namespace App\Classes\Pagos\Procesadores\Bbva;

use App;
use Log;
use Exception;
use App\Classes\Pagos\Procesadores\Bbva\Mensaje;

class Interred
{

    // {{{ properties

    /*
     * @var Bbva\Mensaje
     */
    protected $oMensaje;

    /*
     * @var array $aConfig Configuración de servicio
     */
    protected $aConfig;

    /*
     * @var string $sEnv Ambiente de la aplicación.
     */
    protected $sEnv;

    // }}}

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos protegidos
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ protected functions

    /**
     * Obtiene el cliente HTTP por default (guzzle).
     *
     * @return Client
     */
    protected function getDefaultMensaje()
    {
        return new Mensaje();
    }

    // }}}

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos privados
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ private functions

    private function sendData(string $hEbcdicMessage)
    {
    }

	public function preparaMensaje(Mensaje $oMensaje)
	{
        $sIso = $oMensaje->getISO(false);
		$sMensaje = 'ISO023400070' . $sIso;
		return $this->isoLength(strlen('00' . $sMensaje)) . $sMensaje;
	}

	private function isoLength(int $size)
	{
		$part = str_split(sprintf("%04s", dechex($size)), 2);
		return chr(hexdec($part[0])) . chr(hexdec($part[1]));
	}


	private function mensajeTipoRed($sNMICode, $sMTI = '0800', $aOpt = [])
	{
		// Define campos
		$oMensaje = new Mensaje();
		$oMensaje->setMTI($sMTI);
		$oMensaje->setData(7, gmdate('mdHis')); // Date & time
		$oMensaje->setData(11, $aOpt['stan'] ?? $oMensaje->generateSystemsTraceAuditNumber()); // Systems Trace Audit Number
		$oMensaje->setData(15, date('md')); // Date & time
        if ($sMTI == '0810') {
            $oMensaje->setData(39, '00'); // Date & time
        } else {
            if ($sNMICode != '301') {
                $oMensaje->setData(48, '50NNNY2010000   '); // Additional DataRetailer Data - Define la afiliación del Establecimiento
            }
        }
		$oMensaje->setData(70, $sNMICode); // Network Management Information Code
		return $oMensaje;
	}

	private function mensajeTipoCompra(PeticionCargo $oPeticionCargo, array $aTipo = [])
	{
		// Define campos
		$oMensaje = new Mensaje();
		$oMensaje->setMTI('0200');
		$oMensaje->setData(3, $oMensaje->formateaCampo3($aTipo)); // Processing Code
		$oMensaje->setData(4, $oMensaje->formateaCampo4($oPeticionCargo->monto)); // Transaction Amount - Monto de la transacción con centavos
		$oMensaje->setData(7, gmdate('mdHis')); // Date & time
		$oMensaje->setData(11, $oMensaje->generateSystemsTraceAuditNumber()); // Systems Trace Audit Number
		$oMensaje->setData(12, date('his')); // Hora local de la transacción
		$oMensaje->setData(13, date('md')); // Date & time - Día local de la transacción
		$oMensaje->setData(17, date('md')); // Date & time - Día en el cual la transacción es registrada por el Adquirente
		$oMensaje->setData(22, '012'); // PoS Entry Mode
		#$oMensaje->setData(23, ''); //
        if (in_array($oPeticionCargo->plan, ['msi', 'mci', 'diferido'])) {
            // Deaparece el campo 25... Puff! Motivo: Pos nomas.
        } else {
            $oMensaje->setData(25, '59'); // Point of Service Condition Code - 59 = Comercio Electrónico
        }
		$oMensaje->setData(32, '12'); // Acquiring Institution Identification Code
		$oMensaje->setData(35, $oMensaje->formateaCampo35($oPeticionCargo->tarjeta)); // Track 2 Data
        $oMensaje->setData(37, date('ymdhis')); // Retrieval Reference Number
		$oMensaje->setData(41, '0000CP01        '); // Card Acceptor Terminal Identification
		//$oMensaje->setData(43, 'Radiomovil DIPSA SA CVCMXCMXMX'); //  Card Acceptor Name/Location
		$oMensaje->setData(48, '5462742            00000000'); // Additional DataRetailer Data - Define la afiliación del Establecimiento
		$oMensaje->setData(49, '484'); // Transaction Currency Code.
		//$oMensaje->setData(54, '000000000000')); // Additional Amounts - Monto del cash advance/back con centavos
		#$oMensaje->setData(55, ''); //
        if (!empty($aTipo['tipo']) && $aTipo['tipo'] == 'puntos_compra') {
            if (in_array($oPeticionCargo->plan, ['msi', 'mci', 'diferido'])) {
                $oMensaje->setData(58, $oMensaje->formateaCampo58(['importe_total' => 0, 'importe_puntos' => 0]));
            } else {
                $oMensaje->setData(58,
                $oMensaje->formateaCampo58([
                    'importe_total' => $oMensaje->formateaCampo4($oPeticionCargo->monto),
                    //'importe_puntos' => $oPeticionCargo->puntos, // Cantidad de puntos debe enviarse con ceros a petición de EGlobal y BBVA
                    'importe_puntos' => 0,
                ]));
            }
        }
		#$oMensaje->setData(59, ''); //
		$oMensaje->setData(60, 'CLPGTES1+0000000'); // POS Terminal Data
		$oMensaje->setData(63, $oMensaje->formateaCampo63([
            'mti' => '0200',
            // C0
            'cvv2' => $oPeticionCargo->tarjeta->cvv2,
            'indicador_cvv2' => $oPeticionCargo->tarjeta->cvv2 ? 'presente' : 'no_presente',
            // Q6
            'parcialidades' => $oPeticionCargo->parcialidades,
            'diferimiento' => $oPeticionCargo->diferido,
            'plan' => $oPeticionCargo->plan,
        ], $aTipo)); // POS Additional Data
		#$oMensaje->setData(103, ''); //
		return $oMensaje;
	}

	public function ascii2hex($ascii) {
	  $hex = '';
	  for ($i = 0; $i < strlen($ascii); $i++) {
		$byte = strtoupper(dechex(ord($ascii{$i})));
		$byte = str_repeat('0', 2 - strlen($byte)).$byte;
		$hex.=$byte." ";
	  }
	  return $hex;
	}

    // }}}

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos públicos
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ public functions

    /**
     * Constructor
     *
     * @param Mensaje $oMensaje Contenedor de mensaje BBVA (ISO-8583)
     */
    public function __construct(Mensaje $oMensaje = null)
    {
        $this->oMensaje = $oMensaje ?? $this->getDefaultMensaje();
        // Define variables comunes
        $this->sEnv = App::environment();
        // Carga configuración de servidores dependiendo del ambiente
        $this->aConfig = config('claropagos.' . $this->sEnv . '.procesadores_pago.eglobal');
    }

    public function procesaMensaje($sMensaje): array
    {
        // Tamaño del mensaje
        $iMensajeBytes = hexdec($this->ascii2hex(substr($sMensaje, 0, 2)));
        // Encabezado de mensaje
        $sMensajeHeader = substr($sMensaje, 2, 12);
        // ISO
        $sMensajeIso = substr($sMensaje, 14, $iMensajeBytes - 14); #, $iMensajeBytes);
        // Parsea ISO
        $oParsedIso = new Mensaje();
        $oParsedIso->setISO($sMensajeIso);
        // Formatea resultado
        $aResultado = [
            'bytes' => $iMensajeBytes,
            'header' => $sMensajeHeader,
            'iso' => $oParsedIso,
            'iso_string' => $sMensajeIso,
            'iso_mti' => $oParsedIso->getMTI(),
            'iso_parsed' => $oParsedIso->getDataArray(),
            'iso_validation' => $oParsedIso->getIsoValidation(),
        ];
        return $aResultado;
    }

	public function mensajeEcho(): string
	{
		return $this->preparaMensaje($this->mensajeTipoRed('301'));
	}

	public function mensajeSignOn(): string
	{
		return $this->preparaMensaje($this->mensajeTipoRed('001'));
	}

	public function mensajeSignOff(): string
	{
		return $this->preparaMensaje($this->mensajeTipoRed('002'));
	}

	public function mensajeCutoff(): string
	{
		return $this->preparaMensaje($this->mensajeTipoRed('201'));
	}

	public function respuestaSignOn($aOpt = []): string
	{
		return $this->preparaMensaje($this->mensajeTipoRed('001', '0810', $aOpt));
	}

	public function respuestaEcho($aOpt = []): string
	{
		return $this->preparaMensaje($this->mensajeTipoRed('301', '0810', $aOpt));
	}

    /**
     * @todo: Cambiar arreglos por objeto de pago amex
     */
    public function sendAAV(array $aBbvaPago, array $aBbvaOverride = [])
    {
        // Datos default
        $aDefaultBbvaPago = [
        ];
        $aBbvaPago = array_merge($aDefaultBbvaPago, $aBbvaPago);
        // Datos default overrides
        $aDefaultBbvaOverride = [
            'mti' => 1100,
            'processing_code' => '174800',
        ];
        $aBbvaOverride = array_merge($aDefaultBbvaOverride, $aBbvaOverride);
        // Envía petición
        return $this->sendMessage($aBbvaPago, $aBbvaOverride);
    }

    /**
     * @todo: Cambiar arreglos por objeto de pago amex
     */
    public function sendTransactionAAV(array $aBbvaPago, array $aBbvaOverride = [])
    {
        // Datos default
        $aDefaultBbvaPago = [
        ];
        $aBbvaPago = array_merge($aDefaultBbvaPago, $aBbvaPago);
        // Datos default overrides
        $aDefaultBbvaOverride = [
            'mti' => 1200,
            'processing_code' => '174800',
        ];
        $aBbvaOverride = array_merge($aDefaultBbvaOverride, $aBbvaOverride);
        // Envía petición
        return $this->sendMessage($aBbvaPago, $aBbvaOverride);
    }

    /**
     * @todo: Cambiar arreglos por objeto de pago amex
     */
    public function sendAuthorization(array $aBbvaPago, array $aBbvaOverride = [])
    {
        // Datos default
        $aDefaultBbvaPago = [
        ];
        $aBbvaPago = array_merge($aDefaultBbvaPago, $aBbvaPago);
        // Datos default overrides
        $aDefaultBbvaOverride = [
            'mti' => '1100',
            'processing_code' => '004800',
        ];
        $aBbvaOverride = array_merge($aDefaultBbvaOverride, $aBbvaOverride);

        // Envía petición
        return $this->sendMessage($aBbvaPago, $aBbvaOverride);
    }

    /**
     * @todo: Cambiar arreglos por objeto de pago amex
     */
    public function sendTransaction(array $aBbvaPago, array $aBbvaOverride = [])
    {
        // Datos default
        $aDefaultBbvaPago = [
        ];
        $aBbvaPago = array_merge($aDefaultBbvaPago, $aBbvaPago);
        // Datos default overrides
        $aDefaultBbvaOverride = [
            'mti' => '1200',
            'processing_code' => '004800',
        ];
        $aBbvaOverride = array_merge($aDefaultBbvaOverride, $aBbvaOverride);

        // Envía petición
        return $this->sendMessage($aBbvaPago, $aBbvaOverride);
    }

    /**
     * Envía reverso de una operación que con respuesta.
     *
     * @todo: Cambiar arreglos por objeto de pago amex
     */
    public function sendRefund(array $aBbvaPago, array $aBbvaOverride = [])
    {
        // Datos default
        $aDefaultBbvaPago = [
            'original_mti' => '1100',
        ];
        $aBbvaPago = array_merge($aDefaultBbvaPago, $aBbvaPago);
        // Datos default overrides
        $aDefaultBbvaOverride = [
            'mti' => '1420',
            'processing_code' => '024000',// 024000 Reversal Void
        ];
        $aBbvaOverride = array_merge($aDefaultBbvaOverride, $aBbvaOverride);

        // Envía petición
        return $this->sendMessage($aBbvaPago, $aBbvaOverride);
    }

    /**
     * Envía reverso de una operación que no se concluyó por lo que no se recibió una respuesta.
     *
     * @todo: Cambiar arreglos por objeto de pago amex
     */
    public function sendReversal(array $aBbvaPago, array $aBbvaOverride = [])
    {
        // Datos default
        $aDefaultBbvaPago = [
            'trace_num' => '      ',
            'acqu_id' => '666',
            'original_mti' => '1100',
        ];
        $aBbvaPago = array_merge($aDefaultBbvaPago, $aBbvaPago);
        // Datos default overrides
        $aDefaultBbvaOverride = [
            'mti' => '1420',
            'processing_code' => '004000',// Reversal Advice

        ];
        $aBbvaOverride = array_merge($aDefaultBbvaOverride, $aBbvaOverride);

        // Envía petición
        return $this->sendMessage($aBbvaPago, $aBbvaOverride);
    }

    /**
     * @todo: Cambiar arreglos por objeto de pago amex
     */
    public function sendMessage(array $aBbvaPago, array $aBbvaOverride = [])
    {
        // Datos default
        $aDefaultBbvaPago = [
        ];
        $aBbvaPago = array_merge($aDefaultBbvaPago, $aBbvaPago);
        // Datos default overrides
        $aDefaultBbvaOverride = [
            'processing_code' => '004800',
        ];
        $aBbvaOverride = array_merge($aDefaultBbvaOverride, $aBbvaOverride);

        // Valida datos

        // Formatea datos
        // @todo: cambiar datos del request por un objeto y que valide datos
        $sSystemsTraceAuditNumber = $this->oMensaje->generateSystemsTraceAuditNumber();

        // Campo 48 - Additional Data Private (Mensualidades
        $sAdditionalDataPrivate = $this->oMensaje->formatAdditionalDataPrivate([
            'plan_pago' => $aBbvaPago['plan_pago'] ?? '03',
            'parcialidades' => $aBbvaPago['parcialidades'] ?? '00',
        ]);

        // Campo 47 - Additional Data National
        $sAdditionalDataNational = $this->oMensaje->formatAdditionalDataNational([
            'secondary_id' => 'ITD',
            'direccion_envio' => [
                'pais_n3' => $aBbvaPago['direccion_envio']['pais_n3'] ?? '484',
                'envio_tipo' => $aBbvaPago['direccion_envio']['envio_tipo'] ?? '05',
            ],
            'producto' => [
                'sku' => $aBbvaPago['producto']['sku'] ?? '0',
            ],
            'cliente' => [
                'ip' => $aBbvaPago['cliente']['ip'] ?? '0.0.0.0',
                'hostname' => $aBbvaPago['cliente']['hostname'] ?? '',
                'browser' => $aBbvaPago['cliente']['browser'] ?? 'UNKNOWN',
                'email' => $aBbvaPago['cliente']['email'] ?? 'UNKNOWN@UNKNOWN.COM',
                'telefono' => $aBbvaPago['cliente']['telefono'] ?? '',
                'prefijo' => $aBbvaPago['cliente']['prefijo'] ?? '00',
            ],
        ]);
        // Campo 63 - Private Use Data
        $sPrivateUseData = $this->oMensaje->formatPrivateUseData([
            'req_type_id' => $aBbvaPago['req_type_id'] ?? 'AD',
            'direccion' => [
                'cp' => $aBbvaPago['direccion']['cp'] ?? '',
                'linea1' => $aBbvaPago['direccion']['linea1'] ?? '',
                'nombre' => $aBbvaPago['direccion']['nombre'] ?? '',
                'apellido_paterno' => $aBbvaPago['direccion']['apellido_paterno'] ?? '',
                'apellido_materno' => $aBbvaPago['direccion']['apellido_materno'] ?? '',
                'telefono' => $aBbvaPago['direccion']['telefono'] ?? '          ',
            ],
            'direccion_envio' => [
                'cp' => $aBbvaPago['direccion_envio']['cp'] ?? '',
                'linea1' => $aBbvaPago['direccion_envio']['linea1'] ?? '',
                'nombre' => $aBbvaPago['direccion_envio']['nombre'] ?? '',
                'apellido_paterno' => $aBbvaPago['direccion_envio']['apellido_paterno'] ?? '',
                'apellido_materno' => $aBbvaPago['direccion_envio']['apellido_materno'] ?? '',
                'telefono' => $aBbvaPago['direccion_envio']['telefono'] ?? '          ',
                'pais_n3' => $aBbvaPago['direccion_envio']['pais_n3'] ?? '484',
            ],
        ]);

        // Define datos para el mensaje GHDC

        // Atributos de pago
        $this->oMensaje->setData( 2, $aBbvaPago['pan']); // PAN
        $this->oMensaje->setData( 4, $aBbvaPago['amount']); // Transaction Amount
        $this->oMensaje->setData(12, $aBbvaPago['datetime']); // Date & Time, Local Transaction
        if (in_array($aBbvaOverride['mti'], ['1100', '1200']) && !empty($aBbvaPago['date_eff'])) {
            $this->oMensaje->setData(13, $aBbvaPago['date_eff']); // CARD Date, Effective (since)
        }
        $this->oMensaje->setData(14, $aBbvaPago['date_exp']); // CARD Date, Expiration
        if (in_array($aBbvaOverride['mti'], ['1100', '1200'])) {
            $this->oMensaje->setData(53, $aBbvaPago['cvv']); // Security Related Control Information (CVV4)
            $this->oMensaje->setData(63, $sPrivateUseData); // Private use data
            if (isset($aBbvaPago['tarjeta_tipo']) && $aBbvaPago['tarjeta_tipo'] == 'prepago') {
                $this->oMensaje->setData(24, 181); // Function Code
            } else {
                $this->oMensaje->setData(24, 100); // Function Code
            }
        }
        if (in_array($aBbvaOverride['mti'], ['1420'])) {
            if (in_array($aBbvaOverride['processing_code'], ['024000'])) {
                $this->oMensaje->setData(31, $aBbvaPago['reference']); // Additional Data, Private
            }
            $this->oMensaje->setData(56, $aBbvaPago['original_mti'] . $aBbvaPago['system_trace_num'] . $aBbvaPago['dt_local'] . $aBbvaPago['acqu_id']); //
        }

        // Atributos del pedido / transacción / usuario
        $this->oMensaje->setData(37, substr(md5(rand()), 0, 12)); // Retrieval Reference Number
        if (in_array($aBbvaOverride['mti'], ['1100', '1200'])) {
            $this->oMensaje->setData(47, $sAdditionalDataNational); // Additional Data-National
            if (!in_array($sAdditionalDataPrivate, ['0300', '0301'])) {
                $this->oMensaje->setData(48, $sAdditionalDataPrivate); // Additional Data, Private
            }
        }
        $this->oMensaje->setData(49, 484); // Currency Code, Transaction


        // Atributos de configuración del cliente
        $nMerchantNumber = 1354722167;
        $this->oMensaje->setData(26, 5045); // Card Acceptor Business Code
        $this->oMensaje->setData(32, 666); // Acquiring Institution ID Code
        $this->oMensaje->setData(41, "CPAY"); // Card Acceptor Terminal ID

        // Atributos de sistema
        if (in_array($aBbvaOverride['mti'], ['1100', '1200'])) {
            $this->oMensaje->setData(7, gmdate('mdHis')); // Date & time
        }
        $this->oMensaje->setData(11, $sSystemsTraceAuditNumber); // Systems Trace Audit Number
        if (in_array($aBbvaOverride['mti'], ['1200', '1420'])) {
            $this->oMensaje->setData(28, gmdate('ymd')); // Date, Reconciliation
        }
        $this->oMensaje->setData(33, 111); // Forwarding Institution ID Code
        $this->oMensaje->setData(42, $nMerchantNumber);  // Card Acceptor Identification Code

        // Atributos de mensaje de autorización
        $this->oMensaje->setMTI($aBbvaOverride['mti']); // MTI
        $this->oMensaje->setData(3, $aBbvaOverride['processing_code']); // Procesing code 004800|174800
        $this->oMensaje->setData(19, 484); // Country Code, Acquiring Institution
        $this->oMensaje->setData(22, "100SS0S00110"); // Point of Service Data Code
        $this->oMensaje->setData(25, 1900); // Message Reason Code
        if (in_array($aBbvaOverride['mti'], ['1100', '1200'])) {
            $this->oMensaje->setData(27, 6); // Approval Code Length
        }


//echo "\n<br>ISO Message: '" . $this->oMensaje->getISO(true) . "'";


        // Envía mensaje
        $oResponse = $this->sendData($nMerchantNumber, "GHDC", $this->oMensaje->getISO(true));
        // Revisa errores
        if ($oResponse->status == 'fail') {
            $aResponseResult = [
                'status' => $oResponse->status,
                'status_code' => $oResponse->status_code,
                'status_message' => $oResponse->status_message,
                'system_trace_num' => $sSystemsTraceAuditNumber,
            ];
        } else {
            // Obtiene y procesa respuesta ISO
            $oMensaje_response = new Mensaje();
            $oMensaje_response->setISO($oResponse->response, true);
            // @todo: Vaciar resultados a un ResponseResults object
            $aResponseResult = [
                'status' => $oResponse->status,
                'status_code' => $oResponse->status_code,
                'status_message' => $oResponse->status_message,
                'system_trace_num' => $sSystemsTraceAuditNumber,
                'action_code' => $oMensaje_response->getValue(39),
                'reference' => $oMensaje_response->getValue(31),
                'approval_code' => $oMensaje_response->getValue(38),
                'trace_num' => $oMensaje_response->getValue(11),
                'dt_local' => $oMensaje_response->getValue(12),
                'acqu_id' => $oMensaje_response->getValue(32),
                'response' => $oMensaje_response->getDataArray(),
            ];
        }

        return (object) $aResponseResult;
    }

    // }}}

}