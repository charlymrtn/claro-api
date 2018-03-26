<?php

namespace App\Classes\Pagos\Procesadores\Eglobal;

use App;
use Log;
use Carbon\Carbon;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\StreamSelectLoop;

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
        echo $this->TS['reset'] . "Nuevo cliente conectado: {$conn->resourceId}" . $this->TS['reset'] . "\n";
        $this->showConnectedClients();
        $conn->send("Conectado a fake eglobal socket server\n");
        echo $this->TS['cyan'] . "    Respuesta enviada a {$conn->resourceId}." . $this->TS['reset'] . "\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo $this->TS['cyan'] . "    Mensaje de cliente {$from->resourceId}: {$msg}" . $this->TS['reset'] . "\n";
        echo $this->TS['cyan'] . "    Mensaje en hex     {$from->resourceId}: " . $this->TS['reset'] . $this->ascii2hex($msg) . "\n";
        $from->send("Mensaje recibido: {$msg}");
        echo $this->TS['cyan'] . "    Respuesta enviada a {$from->resourceId}." . $this->TS['reset'] . "\n";
//        $this->sendMessage("{$from->resourceId} envió un mensaje.", $from->resourceId);
//        // Do we have a username for this user yet?
//        if (isset($this->connectedUsersNames[$from->resourceId])) {
//            // If we do, append to the chat logs their message
//            $this->logs[] = array(
//                "user" => $this->connectedUsersNames[$from->resourceId],
//                "msg" => $msg,
//                "timestamp" => time()
//            );
//            $this->sendMessage(end($this->logs));
//        } else {
//            // If we don't this message will be their username
//            $this->connectedUsersNames[$from->resourceId] = $msg;
//        }
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

    private function sendMessage($message, $except) {
        foreach ($this->connectedUsers as $user) {
            if ($user->resourceId != $except) {
                $user->send(json_encode($message));
            }
        }
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
}
