<?php

namespace App\Classes\Pagos\Procesadores\Amex;

use GuzzleHttp\Client as GuzzleClient;
use Exception;
use Log;
use App\Classes\Pagos\Procesadores\Amex\GHDC;

class InternetDirect
{

    // {{{ properties

    /*
     * @var GuzzleHttp\Client $this->oHttpClient Cliente Guzzle para transmisión de datos HTTP.
     */
    protected $oHttpClient;

    /*
     * @var Amex\GHDC $this->oHttpClient Cliente Guzzle para transmisión de datos HTTP.
     */
    protected $oGHDC;

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
    protected function getDefaultGhdcMessage()
    {
        return new GHDC();
    }

    // }}}

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos privados
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ private functions

    private function sendData(int $nMerchantNumber, string $sMessageType, string $hEbcdicMessage)
    {
        // Config
        $sUrl = "https://qwww318.americanexpress.com/IPPayments/inter/CardAuthorization.do";
        $sOrigin = "AMERICAMOVIL-28705";
        $sCountry = "484";
        $sRegion = "LAC";
        $sRtInd = "050";

        // Prepare content
        $sRequestMessage = "AuthorizationRequestParam=" . $hEbcdicMessage;
        $aGuzzleRequestOptions = [
            'headers' => [
                'Accept-Language' => 'en-us',
                'Content-Type' => 'plain/text',
                'User-Agent' => ' Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
                'Host' => 'qwww318.americanexpress.com',
                'Content-Length' => strlen($sRequestMessage),
                'Cache-Control' => 'no-cache',
                'Connection' => 'Keep-Alive',
                'origin' => $sOrigin,
                'country' => $sCountry,
                'region' => $sRegion,
                'message' => $sMessageType,
                'MerchNbr' => $nMerchantNumber,
                'RtInd' => $sRtInd,
            ],
            'body' => $sRequestMessage,
            'timeout' => 15,
        ];

        // Envía request
        try {
            $oGuzzleResponse = $this->oHttpClient->request('POST', $sUrl, $aGuzzleRequestOptions);
            // Formatea respuesta
            $aResponseResult = [
                'status' => 'success',
                'status_message' => 'Successful request.',
                'status_code' => $oGuzzleResponse->getStatusCode(),
                'response' => $oGuzzleResponse->getBody()->getContents(),
            ];
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $sErrorMessage = $e->getMessage();
            Log::error('Error on '.__METHOD__.' line '.__LINE__.':' . $sErrorMessage);
            echo "\n<br>Error on " . __METHOD__ . ' line ' . __LINE__ . ':' . $sErrorMessage;
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
            echo "\n<br>Error on " . __METHOD__ . ' line ' . __LINE__ . ':' . $sErrorMessage;
            echo "\n<br>Exception: " .  get_class($e) . "";
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
     * @param GHDC $oGhdc Contenedor de mensaje GHDC necesario para Amex (ISO-8583)
     * @param GuzzleClient $oGuzzleClient  Cliente HTTP para hacer las llamadas al API
     */
    public function __construct(GHDC $oGhdc = null, GuzzleClient $oGuzzleClient = null)
    {
        $this->oGHDC = $oGhdc ?? $this->getDefaultGhdcMessage();
        $this->oHttpClient = $oGuzzleClient ?? $this->getDefaultHttpClient();
    }

    /**
     * @todo: Cambiar arreglos por objeto de pago amex
     */
    public function sendAAV(array $aAmexPago, array $aAmexOverride = [])
    {
        // Datos default
        $aDefaultAmexPago = [
        ];
        $aAmexPago = array_merge($aDefaultAmexPago, $aAmexPago);
        // Datos default overrides
        $aDefaultAmexOverride = [
            'mti' => 1100,
            'processing_code' => '174800',
        ];
        $aAmexOverride = array_merge($aDefaultAmexOverride, $aAmexOverride);
        // Envía petición
        return $this->sendMessage($aAmexPago, $aAmexOverride);
    }

    /**
     * @todo: Cambiar arreglos por objeto de pago amex
     */
    public function sendTransactionAAV(array $aAmexPago, array $aAmexOverride = [])
    {
        // Datos default
        $aDefaultAmexPago = [
        ];
        $aAmexPago = array_merge($aDefaultAmexPago, $aAmexPago);
        // Datos default overrides
        $aDefaultAmexOverride = [
            'mti' => 1200,
            'processing_code' => '174800',
        ];
        $aAmexOverride = array_merge($aDefaultAmexOverride, $aAmexOverride);
        // Envía petición
        return $this->sendMessage($aAmexPago, $aAmexOverride);
    }

    /**
     * @todo: Cambiar arreglos por objeto de pago amex
     */
    public function sendAuthorization(array $aAmexPago, array $aAmexOverride = [])
    {
        // Datos default
        $aDefaultAmexPago = [
        ];
        $aAmexPago = array_merge($aDefaultAmexPago, $aAmexPago);
        // Datos default overrides
        $aDefaultAmexOverride = [
            'mti' => '1100',
            'processing_code' => '004800',
        ];
        $aAmexOverride = array_merge($aDefaultAmexOverride, $aAmexOverride);

        // Envía petición
        return $this->sendMessage($aAmexPago, $aAmexOverride);
    }

    /**
     * @todo: Cambiar arreglos por objeto de pago amex
     */
    public function sendTransaction(array $aAmexPago, array $aAmexOverride = [])
    {
        // Datos default
        $aDefaultAmexPago = [
        ];
        $aAmexPago = array_merge($aDefaultAmexPago, $aAmexPago);
        // Datos default overrides
        $aDefaultAmexOverride = [
            'mti' => '1200',
            'processing_code' => '004800',
        ];
        $aAmexOverride = array_merge($aDefaultAmexOverride, $aAmexOverride);

        // Envía petición
        return $this->sendMessage($aAmexPago, $aAmexOverride);
    }

    /**
     * Envía reverso de una operación que con respuesta.
     *
     * @todo: Cambiar arreglos por objeto de pago amex
     */
    public function sendRefund(array $aAmexPago, array $aAmexOverride = [])
    {
        // Datos default
        $aDefaultAmexPago = [
            'original_mti' => '1100',
        ];
        $aAmexPago = array_merge($aDefaultAmexPago, $aAmexPago);
        // Datos default overrides
        $aDefaultAmexOverride = [
            'mti' => '1420',
            'processing_code' => '024000',// 024000 Reversal Void
        ];
        $aAmexOverride = array_merge($aDefaultAmexOverride, $aAmexOverride);

        // Envía petición
        return $this->sendMessage($aAmexPago, $aAmexOverride);
    }

    /**
     * Envía reverso de una operación que no se concluyó por lo que no se recibió una respuesta.
     *
     * @todo: Cambiar arreglos por objeto de pago amex
     */
    public function sendReversal(array $aAmexPago, array $aAmexOverride = [])
    {
        // Datos default
        $aDefaultAmexPago = [
            'trace_num' => '      ',
            'acqu_id' => '666',
            'original_mti' => '1100',
        ];
        $aAmexPago = array_merge($aDefaultAmexPago, $aAmexPago);
        // Datos default overrides
        $aDefaultAmexOverride = [
            'mti' => '1420',
            'processing_code' => '004000',// Reversal Advice

        ];
        $aAmexOverride = array_merge($aDefaultAmexOverride, $aAmexOverride);

        // Envía petición
        return $this->sendMessage($aAmexPago, $aAmexOverride);
    }

    /**
     * @todo: Cambiar arreglos por objeto de pago amex
     */
    public function sendMessage(array $aAmexPago, array $aAmexOverride = [])
    {
        // Datos default
        $aDefaultAmexPago = [
        ];
        $aAmexPago = array_merge($aDefaultAmexPago, $aAmexPago);
        // Datos default overrides
        $aDefaultAmexOverride = [
            'processing_code' => '004800',
        ];
        $aAmexOverride = array_merge($aDefaultAmexOverride, $aAmexOverride);

        // Valida datos
        $ghdc = new GHDC();

        // Formatea datos
        // @todo: cambiar datos del request por un objeto y que valide datos
        $sSystemsTraceAuditNumber = $ghdc->generateSystemsTraceAuditNumber();

        // Campo 48 - Additional Data Private (Mensualidades
        $sAdditionalDataPrivate = $ghdc->formatAdditionalDataPrivate([
            'plan_pago' => $aAmexPago['plan_pago'] ?? '03',
            'parcialidades' => $aAmexPago['parcialidades'] ?? '00',
        ]);

        // Campo 47 - Additional Data National
        $sAdditionalDataNational = $ghdc->formatAdditionalDataNational([
            'secondary_id' => 'ITD',
            'direccion_envio' => [
                'pais_n3' => $aAmexPago['direccion_envio']['pais_n3'] ?? '484',
                'envio_tipo' => $aAmexPago['direccion_envio']['envio_tipo'] ?? '05',
            ],
            'producto' => [
                'sku' => $aAmexPago['producto']['sku'] ?? '0',
            ],
            'cliente' => [
                'ip' => $aAmexPago['cliente']['ip'] ?? '0.0.0.0',
                'hostname' => $aAmexPago['cliente']['hostname'] ?? '',
                'browser' => $aAmexPago['cliente']['browser'] ?? 'UNKNOWN',
                'email' => $aAmexPago['cliente']['email'] ?? 'UNKNOWN@UNKNOWN.COM',
                'telefono' => $aAmexPago['cliente']['telefono'] ?? '',
                'prefijo' => $aAmexPago['cliente']['prefijo'] ?? '00',
            ],
        ]);
        // Campo 63 - Private Use Data
        $sPrivateUseData = $ghdc->formatPrivateUseData([
            'req_type_id' => $aAmexPago['req_type_id'] ?? 'AD',
            'direccion' => [
                'cp' => $aAmexPago['direccion']['cp'] ?? '',
                'linea1' => $aAmexPago['direccion']['linea1'] ?? '',
                'nombre' => $aAmexPago['direccion']['nombre'] ?? '',
                'apellido_paterno' => $aAmexPago['direccion']['apellido_paterno'] ?? '',
                'apellido_materno' => $aAmexPago['direccion']['apellido_materno'] ?? '',
                'telefono' => $aAmexPago['direccion']['telefono'] ?? '          ',
            ],
            'direccion_envio' => [
                'cp' => $aAmexPago['direccion_envio']['cp'] ?? '',
                'linea1' => $aAmexPago['direccion_envio']['linea1'] ?? '',
                'nombre' => $aAmexPago['direccion_envio']['nombre'] ?? '',
                'apellido_paterno' => $aAmexPago['direccion_envio']['apellido_paterno'] ?? '',
                'apellido_materno' => $aAmexPago['direccion_envio']['apellido_materno'] ?? '',
                'telefono' => $aAmexPago['direccion_envio']['telefono'] ?? '          ',
                'pais_n3' => $aAmexPago['direccion_envio']['pais_n3'] ?? '484',
            ],
        ]);

        // Define datos para el mensaje GHDC

        // Atributos de pago
        $ghdc->setData( 2, $aAmexPago['pan']); // PAN
        $ghdc->setData( 4, $aAmexPago['amount']); // Transaction Amount
        $ghdc->setData(12, $aAmexPago['datetime']); // Date & Time, Local Transaction
        if (in_array($aAmexOverride['mti'], ['1100', '1200'])) {
            $ghdc->setData(13, $aAmexPago['date_eff']); // CARD Date, Effective (since)
        }
        $ghdc->setData(14, $aAmexPago['date_exp']); // CARD Date, Expiration
        if (in_array($aAmexOverride['mti'], ['1100', '1200'])) {
            $ghdc->setData(53, $aAmexPago['cvv']); // Security Related Control Information (CVV4)
            $ghdc->setData(63, $sPrivateUseData); // Private use data
            if (isset($aAmexPago['tarjeta_tipo']) && $aAmexPago['tarjeta_tipo'] == 'prepago') {
                $ghdc->setData(24, 181); // Function Code
            } else {
                $ghdc->setData(24, 100); // Function Code
            }
        }
        if (in_array($aAmexOverride['mti'], ['1420'])) {
            if (in_array($aAmexOverride['processing_code'], ['024000'])) {
                $ghdc->setData(31, $aAmexPago['reference']); // Additional Data, Private
            }
            $ghdc->setData(56, $aAmexPago['original_mti'] . $aAmexPago['system_trace_num'] . $aAmexPago['dt_local'] . $aAmexPago['acqu_id']); //
        }

        // Atributos del pedido / transacción / usuario
        $ghdc->setData(37, substr(md5(rand()), 0, 12)); // Retrieval Reference Number
        if (in_array($aAmexOverride['mti'], ['1100', '1200'])) {
            $ghdc->setData(47, $sAdditionalDataNational); // Additional Data-National
            if (!in_array($sAdditionalDataPrivate, ['0300', '0301'])) {
                $ghdc->setData(48, $sAdditionalDataPrivate); // Additional Data, Private
            }
        }
        $ghdc->setData(49, 484); // Currency Code, Transaction


        // Atributos de configuración del cliente
        $nMerchantNumber = 1354722167;
        $ghdc->setData(26, 5045); // Card Acceptor Business Code
        $ghdc->setData(32, 666); // Acquiring Institution ID Code
        $ghdc->setData(41, "CPAY"); // Card Acceptor Terminal ID

        // Atributos de sistema
        if (in_array($aAmexOverride['mti'], ['1100', '1200'])) {
            $ghdc->setData(7, date('mdhis')); // Date & time
        }
        $ghdc->setData(11, $sSystemsTraceAuditNumber); // Systems Trace Audit Number
        if (in_array($aAmexOverride['mti'], ['1200', '1420'])) {
            $ghdc->setData(28, date('ymd')); // Date, Reconciliation
        }
        $ghdc->setData(33, 111); // Forwarding Institution ID Code
        $ghdc->setData(42, $nMerchantNumber);  // Card Acceptor Identification Code

        // Atributos de mensaje de autorización
        $ghdc->setMTI($aAmexOverride['mti']); // MTI
        $ghdc->setData(3, $aAmexOverride['processing_code']); // Procesing code 004800|174800
        $ghdc->setData(19, 484); // Country Code, Acquiring Institution
        $ghdc->setData(22, "100SS0S00110"); // Point of Service Data Code
        $ghdc->setData(25, 1900); // Message Reason Code
        if (in_array($aAmexOverride['mti'], ['1100', '1200'])) {
            $ghdc->setData(27, 6); // Approval Code Length
        }


echo "\n<br>ISO Message: '" . $ghdc->getISO(true) . "'";


        // Envía mensaje
        $oResponse = $this->sendData($nMerchantNumber, "GHDC", $ghdc->getISO(true));
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
            $ghdc_response = new GHDC();
            $ghdc_response->setISO($oResponse->response, true);
            // @todo: Vaciar resultados a un ResponseResults object
            $aResponseResult = [
                'status' => $oResponse->status,
                'status_code' => $oResponse->status_code,
                'status_message' => $oResponse->status_message,
                'system_trace_num' => $sSystemsTraceAuditNumber,
                'action_code' => $ghdc_response->getValue(39),
                'reference' => $ghdc_response->getValue(31),
                'approval_code' => $ghdc_response->getValue(38),
                'trace_num' => $ghdc_response->getValue(11),
                'dt_local' => $ghdc_response->getValue(12),
                'acqu_id' => $ghdc_response->getValue(32),
                'response' => $ghdc_response->getDataArray(),
            ];
        }

        return (object) $aResponseResult;
    }

    // }}}

}