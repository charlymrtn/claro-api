<?php

namespace app\Http\Controllers\Bbva;

use Log;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaccion;
use App\Classes\Pagos\Parametros\PeticionCargo;
//use App\Classes\Sistema\Mensaje;
use Webpatser\Uuid\Uuid;
use App\Classes\Pagos\Procesadores\Bbva\Mensaje;

class BbvaController extends Controller
{

    /**
     * Crea nueva instancia.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $oRequest)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $oRequest)
    {

        $aConfig = [
            'port' => $oRequest->input('port', '8315'),
            'ip' => $oRequest->input('ip', '172.26.202.4'),
            'message' => $oRequest->input('message', 'echo'),
        ];

        // Prepara log
        $aLog = [];

        // Crea socket cliente
        $sEtapa = "Conectando socket: ";
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            # return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error al crear socket.'], 400, ['errors' => socket_strerror(socket_last_error())]);
            $sEtapa .= "socket_create() failed: reason: " . socket_strerror(socket_last_error());
        } else {
            $sEtapa .= "OK";
        }
        $aLog[] = $sEtapa;

        $sEtapa = "Conectando a '" . $aConfig['ip'] . "' on port '" . $aConfig['port'] . "': ";
        $result = socket_connect($socket, $aConfig['ip'], $aConfig['port']);
        if ($result === false) {
            $sEtapa .= "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket));
        } else {
            $sEtapa .= "OK";
        }
        $aLog[] = $sEtapa;

        // Prepara mensaje echo
        $oMensaje = new Mensaje();

        // Variables
        $sSystemsTraceAuditNumber = $oMensaje->generateSystemsTraceAuditNumber();
        // Define campos
        $oMensaje->setData(7, date('mdhis')); // Date & time
        $oMensaje->setData(11, $sSystemsTraceAuditNumber); // Systems Trace Audit Number
        $oMensaje->setData(15, date('md')); // Date & time
        $oMensaje->setData(70, 301); // Network Management Information Code

        // Prepara mensaje
        $sEtapa = "Preparando mensaje: ";
        $in = $oMensaje->getISO(true);
        $out = '';
        $sEtapa .= $in;
        $aLog[] = $sEtapa;

        // Envía mensaje
        $sEtapa = "Enviando mensaje: ";
        socket_write($socket, $in, strlen($in));
        $sEtapa .= "OK";
        $aLog[] = $sEtapa;

        // Recibe respuesta
        $sEtapa = "Reading response: ";
        $buf = 'This is my buffer.';
        if (false !== ($bytes = socket_recv($socket, $buf, 2048, MSG_DONTWAITs))) {
            $sEtapa .= "Read $bytes bytes from socket_recv(): " . $buf;
        } else {
            $sEtapa .= "socket_recv() failed; reason: " . socket_strerror(socket_last_error($socket));
        }
        $aLog[] = $sEtapa;

        // Cierra conexion
        $sEtapa = "Closing socket: ";
        socket_close($socket);
        $sEtapa .= "OK";
        $aLog[] = $sEtapa;

        return json_encode(['log' => $aLog]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        //
    }
}
