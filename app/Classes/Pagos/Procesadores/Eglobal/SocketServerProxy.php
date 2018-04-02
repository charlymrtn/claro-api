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
     * @var array $aClientesConectados Arreglo de datos de los clientes conectados
     */
    private $aClientesData;

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
                // Recibe mensaje de sign on
                $this->enviaEglobal($oInterred->mensajeSignOn());
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
     * @return string Respuesta del mensaje.
     */
    public function enviaEglobal(string $sMensaje)
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
            $iMessageSize = strlen($sMensaje);
            $this->loguea("  Enviando mensaje a eglobal (str): " . $sMensaje, 'debug', 'error');
            $this->loguea("  Enviando mensaje a eglobal (hex): " . $this->ascii2hex($sMensaje), 'debug');
            // Procesa mensaje ISO
            $oInterred = new BBVAInterred();
            $aMensajeISO = $oInterred->procesaMensaje($sMensaje);
            $this->loguea("  Enviando mensaje a eglobal (iso): " . json_encode($aMensajeISO['header']), 'debug');
            $this->loguea("  Enviando mensaje a eglobal (iso): " . json_encode($aMensajeISO['iso_mti']), 'debug');
            $this->loguea("  Enviando mensaje a eglobal (iso): " . json_encode($aMensajeISO['iso_parsed']), 'debug');
            $iMessageBytes = fwrite($this->oEglobalCliente, $sMensaje, $iMessageSize);
            if ($iMessageBytes === false || $iMessageBytes < $iMessageSize) {
                $this->loguea("ERROR: Error al escribir en el socket de eglobal.", 'error');
                $this->aStats['eglobal']['conectado'] = false;
                throw new \Exception("ERROR: Error al escribir en el socket de eglobal.");
                return false;
            } else {
                $this->aStats['eglobal']['transacciones'] += 1;
                $this->aStats['eglobal']['ultima_transaccion'] = Carbon::now();
                $this->loguea("      Mensaje enviado correctamente a eglobal. (Bytes enviados: {$iMessageBytes})", 'debug');
                // Recibe respuesta
                return $this->recibeEglobal();
            }
        } catch (\Exception $e) {
            $this->loguea("ERROR: Error al escribir en el socket de eglobal: " . $e->getMessage(), 'error');
            $this->aStats['eglobal']['conectado'] = false;
            return false;
        }
    }

    /**
     * Recibe mensaje de eglobal.
     *
     * @return string Datos recibidos de eglobal..
     */
    public function recibeEglobal(): string
    {
        // Prepara variables
        $oTime = Carbon::now();
        // Recibe datos
        $this->loguea("      Esperando respuesta de eglobal...", 'debug');
        while (empty($sMensaje)) {
            usleep(10000);
            $sMensaje = stream_get_contents($this->oEglobalCliente, 1024);
            if ($oTime->diffInSeconds() > 10) {
                break;
            }
        }
        // Mensaje raw
        $this->loguea("      Respuesta recibida (str): " . $sMensaje, 'debug');
        $this->loguea("      Respuesta recibida (hex): " . $this->ascii2hex($sMensaje), 'debug');
        // Procesa mensaje
        if (!empty($sMensaje)) {
            try {
                $oInterred = new BBVAInterred();
                $aMensajeISO = $oInterred->procesaMensaje($sMensaje);
                $this->loguea("      Respuesta recibida (iso): " . json_encode($aMensajeISO['header']), 'debug');
                $this->loguea("      Respuesta recibida (iso): " . json_encode($aMensajeISO['iso_mti']), 'debug');
                $this->loguea("      Respuesta recibida (iso): " . json_encode($aMensajeISO['iso_parsed']), 'debug');
            } catch (\Exception $e) {
                $this->loguea("ERROR: Error al parsear mensaje: " . $e->getMessage(), 'error');
            }
        } else {
            $this->loguea("ERROR: Mensaje desconocido: " . $sMensaje, 'error');
        }
        return $sMensaje;
    }

    /**
     * Logea y despliega mensaje
     *
     * @param string $sMensaje String a imprimir y/o loguear.
     * @param string $sVerboseLevel Nivel de verbose. ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug']
     *
     */
    private function loguea(string $sMensaje, string $sVerboseLevel = 'debug')
    {
        // Variables
        $sTermResetTag = "\033[0m"; // Reset
        // Verifica si se envía al STDOUT
        if ($this->aConfig['proxy']['verbose']) {
            if (in_array($sVerboseLevel, ['debug'])) {
                $sTermTag = "";
            } else if (in_array($sVerboseLevel, ['alert', 'warning'])) {
                $sTermTag = "\033[01;93m"; // Yellow
            } else if (in_array($sVerboseLevel, ['emergency', 'critical', 'error'])) {
                $sTermTag = "\033[01;31m"; // Red
            } else if (in_array($sVerboseLevel, ['notice', 'info'])) {
                $sTermTag = "\033[01;36m"; // cyan
            }
            echo "   " . Carbon::now() . ' ' . $sTermTag . $sMensaje . $sTermResetTag . "\n";
        }
        // Escribe log en log configurado en laravel
        if (in_array($sVerboseLevel, ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'])) {
            Log::$sVerboseLevel($sMensaje);
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
        $this->aClientesData = [];
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
        $this->aClientesConectados [$conn->resourceId] = $conn;
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
        }
        // Revisa que tipo de operación va a realizar
        $this->aStats['transacciones'] += 1;
        $this->loguea("    Acción {$from->resourceId}: {$jMensaje->accion}", 'debug');
        if ($jMensaje->accion == 'send') {
            // Revisa si el mensaje a enviar es un ISO Adecuado
            if (substr($jMensaje->mensaje, 2, 12) == 'ISO023400070') {
                // Envía mensaje y espera respuesta
                $sRespuestaEglobal = $this->enviaEglobal($jMensaje->mensaje);
                // Regresa respuesta
                $this->sendMessage($from, ['conexion' => 'success', 'encoding' => 'base64', 'respuesta' => base64_encode($sRespuestaEglobal)]);
            } else {
                $this->loguea("Mensaje BBVA ISO inválido.", 'error');
            }
        } else {
            $this->loguea("Acción desconocida de {$conn->resourceId}:" . $jMensaje->accion, 'debug');
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // Quita conección de storage
        $this->oConexiones->detach($conn);
        // Quita conección de clientes conectados
        unset($this->aClientesConectados[$conn->resourceId]);
//        unset($this->aClientesData[$conn->resourceId]);
        // Envía a terminal mensaje de cierre de conexión
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
     * @return string Respuesta del mensaje.
     */
    public function keepalive(): string
    {
        $iUltimaTrx = $this->aStats['eglobal']['ultima_transaccion']->diffInSeconds();
        $this->loguea("Ultima transacción hace: {$iUltimaTrx} seg.", 'debug');
        if ($iUltimaTrx >= ($this->aConfig['keepalive'])) {
            $this->loguea("Enviando keepalive a eglobal.", 'debug');
            $oInterred = new BBVAInterred();
            return $this->enviaEglobal($oInterred->mensajeEcho());
        }
        return '';
    }

    // }}}
}