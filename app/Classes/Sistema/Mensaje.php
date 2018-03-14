<?php

namespace App\Classes\Sistema;

use App;
use Log;
use Firebase\JWT\JWT;
use GuzzleHttp\Client as GuzzleClient;

/**
 * Clase para enviar mensajes entre sistemas
 *
 * @author ahfer
 */
class Mensaje
{
    // {{{ properties

    /*
     * @var array $aConfig Configuración de servidores
     */
    protected $aConfig;

    /*
     * @var string $sEnv Ambiente de la aplicación.
     */
    protected $sEnv;

    /*
     * @var GuzzleHttp\Client $this->oHttpClient Cliente Guzzle para transmisión de datos HTTP.
     */
    protected $oHttpClient;

    // }}}}

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

    // }}}

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos privados
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ private functions

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
     * @param GuzzleClient $oGuzzleClient  Cliente HTTP para hacer las llamadas a los otros sistemas
     */
    public function __construct(GuzzleClient $oGuzzleClient = null)
    {
        // Cliente HTTP
        $this->oHttpClient = $oGuzzleClient ?? $this->getDefaultHttpClient();
        // Define variables comunes
        $this->sEnv = App::environment();
        // Carga configuración de servidores dependiendo del ambiente
        $this->aConfig = config('claropagos.' . $this->sEnv . '.server');
    }

    /**
     * Envía mensaje a una aplicación y retorna la respuesta
     *
     * @var string Aplicación a la cual mandar el mensaje
     * @var string $sUrl URL al cual enviar el mensaje
     * @var string $sMetodo Método
     * @var string $sMensaje Mensaje a enviar
     *
     * @return \stdClass Objeto de respuesta
     */
    public function envia(string $sAplicacion, string $sUrl, string $sMetodo, string $sMensaje = ""): \stdClass
    {
        // Define parámetros para la comunicación
        $aGuzzleRequestOptions = [
            'base_uri' => $this->aConfig[$sAplicacion]['url'],
            'headers' => [
                'Accept-Language' => 'en-us',
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($sMensaje),
                'Cache-Control' => 'no-cache',
                'Connection' => 'Keep-Alive',
                'Authorization' => 'Bearer ' . $this->aConfig[$sAplicacion]['token'],
            ],
            'body' => $sMensaje,
            'timeout' => 25,
            'http_errors' => false,
        ];
        try {
            $oGuzzleResponse = $this->oHttpClient->request($sMetodo, $sUrl, $aGuzzleRequestOptions);
            // Formatea respuesta
            $aResponseResult = [
                'status' => 'success',
                'status_message' => 'Successful request.',
                'status_code' => $oGuzzleResponse->getStatusCode(),
                'response' => $oGuzzleResponse->getBody()->getContents(),
            ];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $sErrorMessage = $e->getMessage();
            Log::error('Error en '.__METHOD__.' línea '.__LINE__.':' . $sErrorMessage);
                $aResponseResult = [
                    'status' => 'fail',
                    'status_message' => 'ClientException: ' . $sErrorMessage,
                    'status_code' => '504',
                    'response' => null,
                ];
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $sErrorMessage = $e->getMessage();
            Log::error('Error en '.__METHOD__.' línea '.__LINE__.':' . $sErrorMessage);
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
                    'status_message' => 'Error de conexión, bad gateway: ' . $sErrorMessage,
                    'status_code' => '502',
                    'response' => null,
                ];
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $sErrorMessage = $e->getMessage();
            Log::error('Error en '.__METHOD__.' línea '.__LINE__.':' . $sErrorMessage);
            $aResponseResult = [
                'status' => 'fail',
                'status_message' => 'Error desconocido en request: ' . $sErrorMessage,
                'status_code' => '520',
                'response' => null,
            ];
        } catch (Exception $e) {
            $sErrorMessage = $e->getMessage();
            Log::error('Error en '.__METHOD__.' línea '.__LINE__.':' . $sErrorMessage);
            $aResponseResult = [
                'status' => 'fail',
                'status_message' => 'Error desconocido: ' . $sErrorMessage,
                'status_code' => '520',
                'response' => null,
            ];
        }

        // Regresa objeto con respuesta
        return (object) $aResponseResult;
    }

    // }}}
}