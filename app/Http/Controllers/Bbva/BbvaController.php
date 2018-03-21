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

        // Crea socket cliente
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            # return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error al crear socket.'], 400, ['errors' => socket_strerror(socket_last_error())]);
            echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
        } else {
            echo "OK.\n";
        }
        flush();

        echo "Conectando a '" . $aConfig['ip'] . "' on port '" . $aConfig['port'] . "'...";
        $result = socket_connect($socket, $aConfig['ip'], $aConfig['port']);
        if ($result === false) {
            echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
        } else {
            echo "OK.\n";
        }

        // Prepara mensaje echo
        $oMensaje = new Mensaje();


        // Variables
        $sSystemsTraceAuditNumber = $this->oMensaje->generateSystemsTraceAuditNumber();
        // Define campos
        $oMensaje->setData(7, date('mdhis')); // Date & time
        $oMensaje->setData(11, $sSystemsTraceAuditNumber); // Systems Trace Audit Number
        $oMensaje->setData(15, date('md')); // Date & time
        $oMensaje->setData(70, 301); // Network Management Information Code

        $in = $this->oMensaje->getISO(true);
        $out = '';

        echo "Sending HTTP HEAD request...";
        socket_write($socket, $in, strlen($in));
        echo "OK.\n";

        echo "Reading response:\n\n";
        while ($out = socket_read($socket, 2048)) {
            echo $out;
        }

        echo "Closing socket...";
        socket_close($socket);
        echo "OK.\n\n";

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
