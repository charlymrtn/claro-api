<?php

namespace App\Classes\Pagos\Procesadores\Eglobal;

use App;
use Log;
use Carbon\Carbon;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\StreamSelectLoop;
use App\Classes\Pagos\Procesadores\Bbva\Interred as BBVAInterred;
use App\Classes\Pagos\Procesadores\Bbva\Mensaje;

/**
 * Implementación de servidor mock de eglobal.
 */

class SocketServerMock implements MessageComponentInterface {
    public $clients;
    private $logs;
    private $connectedUsers;
    private $connectedUsersNames;
    // Term Styles
    private $TS = [
        'bold' => "\033[01;1m",
        'red' => "\033[01;31m",
        'yellow' => "\033[01;93m",
        'green' => "\033[01;30m",
        'cyan' => "\033[01;36m",
        'reset' => "\033[0m",
    ];


    /**
     * Constructor
     *
     * @param StreamSelectLoop $oLoop Loop de React para manipulación posterior.
     */
    public function __construct(StreamSelectLoop $oLoop) {
        $this->clients = new \SplObjectStorage;
        $this->logs = [];
        $this->connectedUsers = [];
        $this->connectedUsersNames = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $this->connectedUsers [$conn->resourceId] = $conn;
        echo $this->TS['reset'] . Carbon::now() . " Nuevo cliente conectado: {$conn->resourceId}" . $this->TS['reset'] . "\n";
        $this->showConnectedClients();
        // Comentados porque eglobal no envía nada al conectarse
        ////$conn->send("Conectado a fake eglobal socket server\n");
        //echo $this->TS['cyan'] . "    " . Carbon::now() . " Respuesta enviada a {$conn->resourceId}." . $this->TS['reset'] . "\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo $this->TS['cyan'] . "    " . Carbon::now() . " Recibiendo mensaje de cliente {$from->resourceId} (str): " . $this->TS['reset'] . $msg . "\n";
        echo $this->TS['cyan'] . "    " . Carbon::now() . " Recibiendo mensaje de cliente {$from->resourceId} (hex): " . $this->TS['reset'] . $this->ascii2hex($msg) . "\n";
        // Procesa mensaje ISO recibido
        try {
            $oInterred = new BBVAInterred();
            $aMensajeISO = $oInterred->procesaMensaje($msg);
            echo $this->TS['cyan'] . "    " . Carbon::now() . " Recibiendo mensaje de cliente {$from->resourceId} (iso): " . $this->TS['reset'] . json_encode($aMensajeISO['iso_parsed']) . "\n";
        } catch (Exception $e) {
            echo $this->TS['red'] . "    Mensaje incorrecto: " . $this->TS['red'] . $e->getMessage() . "\n";
            $aMensajeISO['iso_mti'] = '0';
        }
        // Responde acorde al tio pde mensaje
        if ($aMensajeISO['iso_mti'] == '0800') {
            if ($aMensajeISO['iso_parsed']['70'] == '001') {
                echo $this->TS['cyan'] . "    " . Carbon::now() . " Enviando respuesta de Signon\n";
                $this->sendMessage($from, $oInterred->respuestaSignOn(['stan' => $aMensajeISO['iso_parsed']['11']]));
            } else if ($aMensajeISO['iso_parsed']['70'] == '301') {
                echo $this->TS['cyan'] . "    " . Carbon::now() . " Enviando respuesta de Echo\n";
                $this->sendMessage($from, $oInterred->respuestaEcho(['stan' => $aMensajeISO['iso_parsed']['11']]));
            }
        } else if ($aMensajeISO['iso_mti'] == '0200') {
            echo $this->TS['cyan'] . "    " . Carbon::now() . " Enviando respuesta de Cargo\n";
            $sRespuesta = $this->isoRespuestaCompra($aMensajeISO['iso']->getDataArray());
            dump($sRespuesta);
            #$from->send($sRespuesta);
            $this->sendMessage($from, $sRespuesta);
            #$this->sendMessage($from, $msg);
        } else {
            echo $this->TS['cyan'] . "    " . Carbon::now() . " Enviando mismo mensaje como respuesta\n";
            $this->sendMessage($from, $msg);
        }
        echo $this->TS['cyan'] . "    " . Carbon::now() . $this->TS['cyan'] . " Respuesta enviada a {$from->resourceId}." . $this->TS['reset'] . "\n";
    }

    public function onClose(ConnectionInterface $conn) {
        // Detatch everything from everywhere
        $this->clients->detach($conn);
//        unset($this->connectedUsersNames[$conn->resourceId]);
        unset($this->connectedUsers[$conn->resourceId]);
        echo $this->TS['red'] . "Cliente desconectado: {$conn->resourceId}" . $this->TS['reset'] . "\n";
        $this->showConnectedClients();
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }

    /**
     * Envía mensae al usuario conectado al proxy
     *
     * @param ConnectionInterface $user Usuario conectado al cual enviar el mensaje.
     * @param array $aMensaje Mensaje a enviar en arreglo para ser transformado en json.
     *
     * @return bool Resultado del envío del mensajes.
     */
    private function sendMessage(ConnectionInterface $user, string $sMensaje) {
        echo $this->TS['cyan'] . "    " . Carbon::now() . " Enviando mensaje a cliente    {$user->resourceId} (str): " . $this->TS['reset'] . $this->TS['yellow'] . $sMensaje . "\n";
        return $user->send($sMensaje);
    }

    private function showConnectedClients() {
        echo $this->TS['yellow'] . "    Clientes conectados: " . count($this->connectedUsers) . $this->TS['reset'];
        if (count($this->connectedUsers)) {
            $usuarios = [];
            foreach($this->connectedUsers as $user) {
                $usuarios[] = $user->resourceId;
            }
            echo " [" . implode(", ", $usuarios) . "]";
        }
        echo "\n";
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





	private function isoRespuestaCompra(array $aIsoCompra)
	{
echo $this->TS['cyan'] . "    " . Carbon::now() . " Generando respuesta de Cargo\n";
        // Define campos
		$oMensaje = new Mensaje();
		$oMensaje->setMTI('0210');
		$oMensaje->setData(3, $aIsoCompra[3]); // Processing Code
		$oMensaje->setData(4, $aIsoCompra[4]); // Transaction Amount - Monto de la transacción con centavos
		$oMensaje->setData(7, $aIsoCompra[7]); // Date & time
		$oMensaje->setData(11, $aIsoCompra[11]); // Systems Trace Audit Number
		$oMensaje->setData(12, $aIsoCompra[12]); // Hora local de la transacción
		$oMensaje->setData(13, $aIsoCompra[13]); // Date & time - Día local de la transacción
		$oMensaje->setData(17, $aIsoCompra[17]); // Date & time - Día en el cual la transacción es registrada por el Adquirente
		$oMensaje->setData(22, $aIsoCompra[22]); // PoS Entry Mode
		$oMensaje->setData(25, $aIsoCompra[25]); // Point of Service Condition Code - 59 = Comercio Electrónico
		$oMensaje->setData(35, $aIsoCompra[35]); // Track 2 Data
		$oMensaje->setData(37, $aIsoCompra[37]); // Retrieval Reference Number
		$oMensaje->setData(38, 0); // Retrieval Reference Number
		$oMensaje->setData(39, '00'); // Retrieval Reference Number
		$oMensaje->setData(41, $aIsoCompra[41]); // Card Acceptor Terminal Identification
//		$oMensaje->setData(48, $aIsoCompra[48]); // Additional DataRetailer Data - Define la afiliación del Establecimiento
		$oMensaje->setData(49, $aIsoCompra[49]); // Transaction Currency Code.
		$oMensaje->setData(60, $aIsoCompra[60]); // POS Terminal Data
		$oMensaje->setData(63, $aIsoCompra[63]); // POS Additional Data
//		echo "<pre>" . print_r($oMensaje->getDataArray(), true) . "</pre>";
        dump($oMensaje->getDataArray());
echo $this->TS['cyan'] . "    " . Carbon::now() . " Respuesta de Cargo generada\n";
        $oInterred = new BBVAInterred();
        return $oInterred->preparaMensaje($oMensaje->getISO(false));
	}


}
