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


require public_path('b.php');
use app\Prueba\Bbva\BbvaTest;


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
        $sPrueba = $oRequest->input('prueba', '1');
        $sTipo = $oRequest->input('tipo', 'envio_online');
        $sAccion = $oRequest->input('accion', 'prueba');
        $sTrxReq = base64_decode(urldecode($oRequest->input('trx_request', null)));
        $sTrxResp = base64_decode(urldecode($oRequest->input('trx_response', null)));
        // Prepara prueba
        $oBbvaTest = new BbvaTest();
        try {
            $oResultado = $oBbvaTest->pruebas($sPrueba, $sTipo, $sAccion, $sTrxReq, $sTrxResp);
        } catch (\Exception $e) {
            $iCode = $e->getCode();
            if (empty($iCode)) {
                $iCode = 520;
            }
            return ejsend_error(['code' => $iCode, 'type' => 'Sistema', 'message' => $e->getMessage() . ' ' . $e->getLine()], $iCode);
        }

        if (in_array($sTipo, ['datos_json', 'envio_json'])) {
            // Obtiene los datos a enviar de la prueba
            return ejsend_success($oResultado);
        } else if ($sTipo == 'envio_online') {
            echo "\n<br>Ejecutando prueba {$sPrueba}...";
            return $oResultado;
        }
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
