<?php

namespace App\Classes\Pagos\Procesadores\Bbva;

use App;
use Log;
use Exception;
use App\Classes\Pagos\Procesadores\Bbva\Mensaje;

class InterredProxy
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

    private function enviaMensaje(string $sMensaje)
    {
        // Prepara resultado
        $aResponseResult = [
            'status' => 'fail',
            'status_message' => 'Unknown error.',
            'status_code' => '520',
            'response' => null,
        ];

		// Conecta
		try {
			$oEglobalProxyCliente = @stream_socket_client('tcp://' . $this->config['proxy']['ip'] . ':' . $this->configv['port'], $aResponseResult['status_code'], $aResponseResult['status_message'], $this->config['proxy']['timeout'], STREAM_CLIENT_CONNECT);
			if ($oEglobalProxyCliente === false) {
                $aResponseResult = [
                    'status' => 'fail',
                    'status_message' => socket_strerror(socket_last_error()),
                    //'status_code' => '500',
                ];
			}
			// Define timeout
			stream_set_timeout($oEglobalProxyCliente, $this->config['proxy']['timeout']);
			stream_set_blocking($oEglobalProxyCliente, 0);
			stream_set_read_buffer($oEglobalProxyCliente, 0);
			// Espera respuesta
            usleep(500);
            $jData = stream_get_contents($this->client);
            echo "\n<br>Respuesta  (str): " . $jData;
            $aData = json_decode($jData);
            echo "\n<br>Respuesta (jdc): " . print_r($aData);

		} catch (\Exception $e) {
			echo "\n<br>Error al crear el socket: " . $e->getMessage();
			return false;
		}
		if (fclose($this->client) === false) {
			echo "<br>Error al cerrar el socket: " . socket_strerror(socket_last_error()) . "\n";
			throw new \Exception("Error al cerrar el socket: " . socket_strerror(socket_last_error()));
		} else {
			$this->client = false;
			echo "<br>Socket cerrado OK.\n";
		}
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

    // }}}

}