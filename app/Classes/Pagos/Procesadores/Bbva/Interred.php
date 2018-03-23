<?php

namespace App\Classes\Pagos\Procesadores\Bbva;

use App;
use Log;
use Exception;
use App\Classes\Pagos\Procesadores\Bbva\Mensaje;
use GuzzleHttp\Client as GuzzleClient;

class Interred
{

    // {{{ properties

    /*
     * @var GuzzleHttp\Client $this->oHttpClient Cliente Guzzle para transmisión de datos HTTP.
     */
    protected $oHttpClient;

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
    protected function getDefaultHttpClient()
    {
        return new GuzzleClient();
    }

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
        // Config
        $sUrl = $this->aConfig['api_url']; // "https://qwww318.americanexpress.com/IPPayments/inter/CardAuthorization.do";

        // Prepare content
        $sMessage = $hEbcdicMessage;
        $sHeader = strlen($hEbcdicMessage);
        $sRequestMessage = "AuthorizationRequestParam=" . $hEbcdicMessage;

        // Envía request
        try {
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $sErrorMessage = $e->getMessage();
            //Log::error('Error on '.__METHOD__.' line '.__LINE__.':' . $sErrorMessage);
            //echo "\n<br>Error on " . __METHOD__ . ' line ' . __LINE__ . ':' . $sErrorMessage;
            if (strpos($sErrorMessage, 'cURL error 28') !== false) {
                $aResponseResult = [
                    'status' => 'fail',
                    'status_message' => 'Gateway Timeout.',
                    'status_code' => '504',
                    'response' => null,
                ];
            } else {
                $aResponseResult = [
                    'status' => 'fail',
                    'status_message' => 'Connection error, bad gateway: ' . $sErrorMessage,
                    'status_code' => '502',
                    'response' => null,
                ];
            }
        } catch (Exception $e) {
            $sErrorMessage = $e->getMessage();
            Log::error('Error on '.__METHOD__.' line '.__LINE__.':' . $sErrorMessage);
            //echo "\n<br>Error on " . __METHOD__ . ' line ' . __LINE__ . ':' . $sErrorMessage;
            //echo "\n<br>Exception: " .  get_class($e) . "";
            $aResponseResult = [
                'status' => 'fail',
                'status_message' => 'Unknown error: ' . $sErrorMessage,
                'status_code' => '520',
                'response' => null,
            ];
        }

        // Regresa objeto con respuesta
        return (object) $aResponseResult;
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
     * @param GHDC $oGhdc Contenedor de mensaje GHDC necesario para Bbva (ISO-8583)
     * @param GuzzleClient $oGuzzleClient  Cliente HTTP para hacer las llamadas al API
     */
    public function __construct(Mensaje $oMensaje = null, GuzzleClient $oGuzzleClient = null)
    {
        $this->oMensaje = $oMensaje ?? $this->getDefaultMensaje();
        $this->oHttpClient = $oGuzzleClient ?? $this->getDefaultHttpClient();
        // Define variables comunes
        $this->sEnv = App::environment();
        // Carga configuración de servidores dependiendo del ambiente
        $this->aConfig = config('claropagos.' . $this->sEnv . '.procesadores_pago.amex');
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
            $this->oMensaje->setData(7, date('mdhis')); // Date & time
        }
        $this->oMensaje->setData(11, $sSystemsTraceAuditNumber); // Systems Trace Audit Number
        if (in_array($aBbvaOverride['mti'], ['1200', '1420'])) {
            $this->oMensaje->setData(28, date('ymd')); // Date, Reconciliation
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