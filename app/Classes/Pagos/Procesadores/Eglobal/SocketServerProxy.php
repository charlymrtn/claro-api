<?php

namespace App\Classes\Pagos\Procesadores\Eglobal;

use App;
use Log;
use Carbon\Carbon;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\StreamSelectLoop;
use App\Classes\Pagos\Procesadores\Bbva\Interred as BBVAInterred;

/**
 * Implementación de servidor proxy para peticiones a eglobal.
 */
class SocketServerProxy implements MessageComponentInterface
{
    // {{{ properties

    /*
     * @var array $aConfig Configuración de servicio
     */
    protected $aConfig;

    /*
     * @var array $aStats Estadísticas generales
     */
    protected $aStats = [
        'transacciones' => 0,
        'eglobal' => [
            // Estatus de la conexión a eglobal
            'conectado' => false,
            // Objeto Carbon de creación de la conexión
            'created_at' => null,
            // Numero de reconexiones a eglobal
            'conexiones' => 0,
            // Transacciones desde la última reconexión eglobal
            'transacciones' => 0,
            // Transacciones a eglobal desde el inicio del servidor proxy
            'transacciones_totales' => 0,
            // Ultima transacción
            'ultima_transaccion' => null,
        ],
    ];

    /*
     * @var \SplObjectStorage $oConexiones Arreglo de oConexiones
     */
    public $oConexiones;

    /*
     * @var array $aClientesConectados Arreglo de clientes conectados
     */
    private $aClientesConectados;

    /*
     * @var array $aMensajesEviados Arreglo de mensajes enviados y los clientes correspondientes
     */
    private $aMensajesEviados;

    /*
     * @var resource $oEglobalCliente Stream resource del cliente del servidor Eglobal
     */
    private $oEglobalCliente = null;

    /*
     * @var array $aEglobalClienteErrores Arreglo de errores en el socket del servidor Eglobal
     */
    private $aEglobalClienteErrores = [];

    // }}}}

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos protegidos
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ protected functions
    // }}}

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos privados
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ private functions

    /**
     * Envía mensae al usuario conectado al proxy
     *
     * @param ConnectionInterface $user Usuario conectado al cual enviar el mensaje.
     * @param array $aMensaje Mensaje a enviar en arreglo para ser transformado en json.
     *
     * @return bool Resultado del envío del mensajes.
     */
    private function sendMessage(ConnectionInterface $user, array $aMensaje)
    {
        $jResponse = json_encode([
            'status' => 'success',
            'data' => $aMensaje,
            'datetime' => Carbon::now()->toDateTimeString(),
            'timestamp' => time(),
        ]);
        $this->loguea("Enviando mensaje a usuario {$user->resourceId}: {$jResponse}", 'debug');
        return $user->send($jResponse);
    }

    /**
     * Regresa estadísticas generales del estado del proxy
     *
     * @return array Arreglo con estadísticas generales del estado del proxy.
     */
    private function getEstadisticasProxy(): array
    {
        // Estatus del servidor proxy
        // Estatus del servidor eglobal
        $oUpdated = Carbon::now();
        $aStats = [
            'proxy' => [
                'creado' => $this->aStats['created_at']->toDateTimeString(),
                'actualizado' => $oUpdated->toDateTimeString(),
                'transacciones' => $this->aStats['transacciones'],
            ],
            'clientes' => [
                'conectados' => count($this->aClientesConectados),
                'lista' => [],
            ],
            'eglobal' => [
                'conectado' => $this->aStats['eglobal']['conectado'],
                'creado' => $this->aStats['eglobal']['created_at']->toDateTimeString(),
                'conexiones' => $this->aStats['eglobal']['conexiones'],
                'transacciones' => $this->aStats['eglobal']['transacciones'],
                'transacciones_totales' => $this->aStats['eglobal']['transacciones_totales'],
            ],
        ];
        $aStats['proxy']['uptime'] = $this->aStats['created_at']->diff($oUpdated)->format('%H:%I:%S');
        $aStats['proxy']['desde'] = $this->aStats['created_at']->diffForHumans();
        $aStats['eglobal']['uptime'] = $this->aStats['eglobal']['created_at']->diff($oUpdated)->format('%H:%I:%S');
        $aStats['eglobal']['desde'] = $this->aStats['eglobal']['created_at']->diffForHumans();
        $aStats['eglobal']['ultima_transaccion'] = $this->aStats['eglobal']['ultima_transaccion']->diffForHumans();
        // Clientes conectados
        foreach ($this->aClientesConectados as $user) {
            $aStats['clientes']['lista'][] = $user->resourceId;
        }
        return $aStats;
    }

    /**
     * Convierte una cadena ASCII en hexadecimal
     *
     * @param string $ascii String a convertir.
     * @param string $separator Separador de pares hexadecimales para mejor visualización.
     *
     * @return string String con el mensaje en hexadecimal.
     */
    private function ascii2hex(string $ascii, string $separator = ' '): string
    {
        $hex = '';
        for ($i = 0; $i < strlen($ascii); $i++) {
            $byte = strtoupper(dechex(ord($ascii{$i})));
            $byte = str_repeat('0', 2 - strlen($byte)) . $byte;
            $hex .= $byte . $separator;
        }
        return $hex;
    }

    /**
     * Conecta al servidor egobal.
     *
     * @param int $iMaxIntentos Número máximo de intentos.
     * @param int $iTiempoEspera Segundos a esperar entre intentos de conexión.
     *
     * @return bool Resultado de la conexión.
     */
    private function conectaEglobal($iMaxIntentos = 200, $iTiempoEspera = 5): bool
    {
        // Variables
        $iIntentos = 0;
        // Inicializa estatus
        $this->aStats['eglobal']['conectado'] = false;
        $this->aStats['eglobal']['created_at'] = null;
        $this->aStats['eglobal']['transacciones'] = 0;
        // Conecta a eglobal
        while ($this->aStats['eglobal']['conectado'] == false && $iIntentos <= $iMaxIntentos) {
            $this->oEglobalCliente = @stream_socket_client(
            'tcp://' . $this->aConfig['ip'] . ':' . $this->aConfig['puerto'], $this->aEglobalClienteErrores['number'], $this->aEglobalClienteErrores['error'], $this->aConfig['timeout'],
            STREAM_CLIENT_CONNECT
            );
            if ($this->oEglobalCliente === false) {
                $this->loguea("Conexión a eglobal: '" . $this->aConfig['ip'] . ":" . $this->aConfig['puerto'] . "': ERROR", 'error');
                $iIntentos += 1;
                sleep($iTiempoEspera);
            } else {
                $this->loguea("Conexión a eglobal: '" . $this->aConfig['ip'] . ":" . $this->aConfig['puerto'] . "': OK", 'info');
                $this->aStats['eglobal']['conectado'] = true;
                $this->aStats['eglobal']['created_at'] = Carbon::now();
                $this->aStats['eglobal']['conexiones'] += 1;
                $this->aStats['eglobal']['transacciones'] = 0;
                stream_set_timeout($this->oEglobalCliente, $this->aConfig['timeout']);
                stream_set_blocking($this->oEglobalCliente, 0);
                stream_set_read_buffer($this->oEglobalCliente, 0);
                // Recibe mensaje de conexión
                //$this->recibeEglobal(); // EGlobal no envía nada al realizar la conexión
                // Envía mensaje de sign on
                $oInterred = new BBVAInterred();
                $this->enviaEglobal(null, 0, 0, $oInterred->mensajeSignOn());
                // Recibe mensajes en el socket
                $this->escuchaEglobal();
            }
        }
        // Regresa resultado de conexión
        return $this->aStats['eglobal']['conectado'];
    }

    /**
     * Envía mensaje a eglobal.
     *
     * @param string $sMensaje Mensaje a enviar.
     *
     * @return boolean Verificación del envío del mensaje.
     */
    private function enviaEglobal($from, string $sTrxId, string $sStan, string $sMensaje): bool
    {
        // Valida conexión
        if ($this->aStats['eglobal']['conectado'] == false) {
            $this->conectaEglobal();
        }
        // Si no hay conexión regresa fallo en envío
        if ($this->aStats['eglobal']['conectado'] == false) {
            return false;
        }
        // Envía mensaje
        try {
            if (!empty($sStan) && !empty($sTrxId)) {
                $this->aMensajesEviados[$sStan] = ['transaccion_id' => $sTrxId, 'stan' => $sStan, 'from' => $from, 'enviado' => null];
            }
            $iMessageSize = strlen($sMensaje);
            $this->loguea("  Enviando mensaje a eglobal (str): " . $sMensaje, 'debug');
            $this->loguea("  Enviando mensaje a eglobal (hex): " . $this->ascii2hex($sMensaje), 'debug');
            // Procesa mensaje ISO
            $iMessageBytes = fwrite($this->oEglobalCliente, $sMensaje, $iMessageSize);
            if ($iMessageBytes === false || $iMessageBytes < $iMessageSize) {
                throw new \Exception("No se envió el mensaje completo: ({$iMessageBytes}) bytes de ({$iMessageSize})");
            } else {
                $oNow = Carbon::now();
                $this->aStats['eglobal']['transacciones'] += 1;
                $this->aStats['eglobal']['ultima_transaccion'] = $oNow;
                $this->aMensajesEviados[$sStan]['enviado'] = $oNow;
                $this->loguea("      Mensaje enviado correctamente a eglobal. (Bytes enviados: {$iMessageBytes})", 'info');
            }
            unset($iMessageSize, $iMessageBytes);
        } catch (\Exception $e) {
            $this->loguea("ERROR: Error al escribir en el socket de eglobal: " . $e->getMessage() . ' ' . $e->getLine(), 'error');
            $this->aStats['eglobal']['conectado'] = false;
            return false;
        }
        return true;
    }

    private function escuchaEglobal(): void
    {
        // Prepara variables
        $oTime = Carbon::now();
        $bMensajes = true;
        // Recibe datos
        $this->loguea("      Esperando respuesta de eglobal...", 'debug');
        while ($bMensajes) {
            usleep(10000);
            // Lee primeros dos bytes indicando el tamaño del mensaje
            $sMensajeBytes = stream_get_contents($this->oEglobalCliente, 2);
            if (!empty($sMensajeBytes)) {
                $iMensajeBytes = hexdec($this->ascii2hex($sMensajeBytes)) - 2;
                // Obtiene mensaje de tamaño $iMensajeBytes
                $sMensaje = stream_get_contents($this->oEglobalCliente, $iMensajeBytes);
                // Mensaje raw
                $this->loguea("      Respuesta recibida (str): " . $sMensaje, 'debug');
                $this->loguea("      Respuesta recibida (hex): " . $this->ascii2hex($sMensaje), 'debug');
                // Revisa si el mensaje es un ISO Adecuado
                if (substr($sMensaje, 0, 3) == 'ISO') {
                    // Obtiene STAN
                    $oRespuesta = new BBVAInterred();
                    $aRespuestaMensajeISO = $oRespuesta->procesaMensaje($sMensajeBytes . $sMensaje);
                    $sRespuestaStan = $aRespuestaMensajeISO['iso_parsed'][11];
                    if (!empty($sRespuestaStan)) {
                        $this->loguea("      Respuesta STAN: " . $sRespuestaStan, 'debug');
                        // Envía respuesta a cliente conectado
                        if (isset($this->aMensajesEviados[$sRespuestaStan]) && isset($this->aMensajesEviados[$sRespuestaStan]['from'])) {
                            $this->sendMessage($this->aMensajesEviados[$sRespuestaStan]['from'], ['conexion' => 'success', 'encoding' => 'base64', 'respuesta' => base64_encode($sMensajeBytes . $sMensaje)]);
                            // Mensaje enviado respondido, limpiando datos
                            unset($this->aMensajesEviados[$sRespuestaStan]);
                        } else {
                            $this->loguea("      Mensaje interno descartado.", 'debug');
                        }
                    } else {
                        $this->loguea("      Mensaje BBVA ISO inválido.", 'error');
                    }
                } else {
                    $this->loguea("      Mensaje BBVA ISO inválido.", 'error');
                }
            } else {
                $bMensajes = false;
            }
        }
    }

    /**
     * Logea y despliega mensaje
     *
     * @param string $sMensaje String a imprimir y/o loguear.
     * @param string $sVerboseLevel Nivel de verbose. ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug']
     *
     */
    private function loguea(string $sMensaje, string $sVerboseLevel = 'debug'): void
    {
        // Valida nivel de logueo del mensaje
        if (!in_array($sVerboseLevel, ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'])) {
            $sVerboseLevel = 'info';
        }
        // Escribe log en log configurado en laravel
        Log::$sVerboseLevel($sMensaje);
        // Valida nivel de logueo a terminal del config
        if (!in_array($this->aConfig['proxy']['verbose'], ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'])) {
            $this->aConfig['proxy']['verbose'] = 'info';
        }
        // Variables
        $sTTag = [
            'reset' => "\033[0m", // Yellow
            'alert' => "\033[01;93m", // Yellow
            'warning' => "\033[01;93m", // Yellow
            'emergency' => "\033[01;31m", // Red
            'critical' => "\033[01;31m", // Red
            'error' => "\033[01;31m", // Red
            'notice' => "\033[01;36m", // Cyan
            'info' => "\033[01;36m", // Cyan
            'debug' => "", // Blank
        ];
        // Loguea en terminal
        if ($sVerboseLevel == 'emergency' && in_array($this->aConfig['proxy']['verbose'], ['emergency'])) {
            echo "   " . Carbon::now() . ' ' . $sTTag[$sVerboseLevel] . $sMensaje . $sTTag['reset'] . "\n";
        } else if ($sVerboseLevel == 'alert' && in_array($this->aConfig['proxy']['verbose'], ['emergency', 'alert'])) {
            echo "   " . Carbon::now() . ' ' . $sTTag[$sVerboseLevel] . $sMensaje . $sTTag['reset'] . "\n";
        } else if ($sVerboseLevel == 'critical' && in_array($this->aConfig['proxy']['verbose'], ['emergency', 'alert', 'critical'])) {
            echo "   " . Carbon::now() . ' ' . $sTTag[$sVerboseLevel] . $sMensaje . $sTTag['reset'] . "\n";
        } else if ($sVerboseLevel == 'error' && in_array($this->aConfig['proxy']['verbose'], ['emergency', 'alert', 'critical', 'error'])) {
            echo "   " . Carbon::now() . ' ' . $sTTag[$sVerboseLevel] . $sMensaje . $sTTag['reset'] . "\n";
        } else if ($sVerboseLevel == 'warning' && in_array($this->aConfig['proxy']['verbose'], ['emergency', 'alert', 'critical', 'error', 'warning'])) {
            echo "   " . Carbon::now() . ' ' . $sTTag[$sVerboseLevel] . $sMensaje . $sTTag['reset'] . "\n";
        } else if ($sVerboseLevel == 'notice' && in_array($this->aConfig['proxy']['verbose'], ['emergency', 'alert', 'critical', 'error', 'warning', 'notice'])) {
            echo "   " . Carbon::now() . ' ' . $sTTag[$sVerboseLevel] . $sMensaje . $sTTag['reset'] . "\n";
        } else if ($sVerboseLevel == 'info' && in_array($this->aConfig['proxy']['verbose'], ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info'])) {
            echo "   " . Carbon::now() . ' ' . $sTTag[$sVerboseLevel] . $sMensaje . $sTTag['reset'] . "\n";
        } else if ($sVerboseLevel == 'debug' && in_array($this->aConfig['proxy']['verbose'], ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'])) {
            echo "   " . Carbon::now() . ' ' . $sTTag[$sVerboseLevel] . $sMensaje . $sTTag['reset'] . "\n";
        }
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
     * @param StreamSelectLoop $oLoop Loop de React para manipulación posterior.
     */
    public function __construct(StreamSelectLoop $oLoop)
    {
        // Inicializa datos para estadísticas
        $this->aStats['created_at'] = Carbon::now();
        // Carga configuración de servidores dependiendo del ambiente
        $this->sEnv = App::environment();
        $this->aConfig = config('claropagos.' . $this->sEnv . '.procesadores_pago.eglobal');
        // Inicializa variables
        $this->aClientesConectados = [];
        $this->aMensajesEviados = [];
        // Inicia arreglo de oConexiones
        $this->oConexiones = new \SplObjectStorage;
        // Guarda referencia a loop
        $this->oLoop = $oLoop;
        // Abre cliente a socket eglobal
        $this->conectaEglobal();
    }

    /**
     * Destructor. Cierra conexión a Eglobal.
     */
    public function __destruct()
    {
        // Cierra cliente
        $this->loguea("Cerrando conexion a eglobal.", 'debug');
        fclose($this->oEglobalCliente);
        $this->oEglobalCliente = null;
    }

    /**
     * Método que se ejecuta al recibir una nueva conexión al servidor proxy
     *
     * @param ConnectionInterface $conn Conexión entrante.
     */
    public function onOpen(ConnectionInterface $conn)
    {
        // Agrega conección a storage
        $this->oConexiones->attach($conn);
        // Agrega conexión a clientes conectados
        $this->aClientesConectados[$conn->resourceId] = $conn;
        // Envía a terminal mensaje de conexión nueva
        $this->loguea("Nuevo cliente conectado: {$conn->resourceId}", 'debug');
        // Envía mensaje de bienvenida
        $this->sendMessage($conn, ['conexion' => 'success', 'mensaje' => 'Conectado a Eglobal Socket Server Proxy']);
        #$this->loguea(json_encode($this->getEstadisticasProxy()), 'debug');
    }

    public function onMessage(ConnectionInterface $from, $sMensaje)
    {
        $this->loguea("    Mensaje de cliente {$from->resourceId}: {$sMensaje}", 'debug');
        // Decodifica mensaje
        $jMensaje = json_decode($sMensaje);
        if (empty($jMensaje)) {
            $this->loguea("Mensaje desconocido de {$conn->resourceId}:" . $sMensaje, 'debug');
        }        if ($oData->encoding == 'base64') {
            $sRespuesta = base64_decode($oData->respuesta);
        } else {
            $sRespuesta = $oData->respuesta;
        }
        // Revisa encoding
        if ($jMensaje->encoding == 'base64') {
            $sMensaje = base64_decode($jMensaje->mensaje_b64);
        } else {
            $sMensaje = $jMensaje->mensaje;
        }

        // Revisa que tipo de operación va a realizar
        $this->aStats['transacciones'] += 1;
        $this->loguea("    Acción {$from->resourceId}: {$jMensaje->accion}", 'debug');
        if ($jMensaje->accion == 'send') {
            // Revisa si el mensaje a enviar es un ISO Adecuado
            if (substr($sMensaje, 2, 12) == 'ISO023400070') {
                // Envía mensaje
                $bEnvioEglobal = $this->enviaEglobal($from, $jMensaje->transaccion_id, $jMensaje->stan, $sMensaje);
                // Regresa resultado de envío de mensaje
                //$this->sendMessage($from, ['envio' => $bEnvioEglobal ? 'success' : 'fail', 'error' => $bEnvioEglobal ? '' : 'Conexión interrumpida']);
            } else {
                $this->loguea("Mensaje ISO inválido", 'error');
                $this->sendMessage($from, ['envio' => 'fail', 'error' => 'Mensaje ISO inválido']);
            }
        } else {
            $this->loguea("Acción desconocida de {$conn->resourceId}:" . $jMensaje->accion, 'debug');
            $this->sendMessage($from, ['envio' => 'fail', 'error' => "Acción {$jMensaje->accion} desconocida"]);
        }
        // Recibe mensajes en el socket
        $this->escuchaEglobal();
    }

    public function onClose(ConnectionInterface $conn)
    {
        // Quita conección de storage
        $this->oConexiones->detach($conn);
        // Quita conección de clientes conectados
        unset($this->aClientesConectados[$conn->resourceId]);
        // Enía a terminal mensaje de cierre de conexión
        $this->loguea("Cliente desconectado: {$conn->resourceId}", 'debug');
        #$this->loguea(json_encode($this->getEstadisticasProxy()), 'debug');
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->loguea("ERROR: " . $e->getMessage(), 'error');
        $conn->close();
    }

    /**
     * Envía mensaje de keepalive para eglobal.
     *
     * @return bool Resultado del envío del mensaje.
     */
    public function keepalive(): bool
    {
        // Valida conexión
        if ($this->aStats['eglobal']['conectado'] == false) {
            $this->conectaEglobal();
        }
        // Evalúa tiempo de la última transacción
        $iUltimaTrx = $this->aStats['eglobal']['ultima_transaccion']->diffInSeconds();
        $this->loguea("Ultima transacción hace: {$iUltimaTrx} seg.", 'debug');
        if ($iUltimaTrx >= ($this->aConfig['keepalive'])) {
            $this->loguea("Enviando keepalive a eglobal.", 'debug');
            $oInterred = new BBVAInterred();
            return $this->enviaEglobal(null, 0, 0, $oInterred->mensajeEcho());
        }
        // Revisa si hay mensajes en el socket
        $this->escuchaEglobal();
        // Termina
        return false;
    }

    // }}}
}
