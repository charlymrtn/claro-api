<?php

namespace app\Http\Controllers\API\v1;

use Log;
use Auth;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Medios\Tarjeta;
use App\Classes\Pagos\Medios\TarjetaCredito;

class TarjetaController extends Controller
{

    /**
     * Comercio instance.
     *
     * @var \App\Models\Comercio
     */
    protected $mTarjeta;

    public function __construct(Tarjeta $tarjeta)
    {
        $this->mTarjeta = $tarjeta;
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $oRequest)
    {
        // Formatea y encapsula datos
        try {
            // Crea tarjeta y valida
            $oTarjeta = new TarjetaCredito($oRequest->all());
            // Guarda resultado en base de datos
            $mTarjeta = $this->mTarjeta->create(array_merge([
                    'comercio_uuid' => $oRequest->user()->comercio_uuid,
                    'default' => $oRequest->input('default', false),
                    'cargo_unico' => $oRequest->input('cargo_unico', true),
                ], $oTarjeta->toArray()));
            // Envía tarjeta a bóveda
            // Guarda resultado en base de datos
            // Formatea respuesta
            $aRespuesta = [
                'token_tarjeta' => $mTarjeta->uuid,
                'pan' => $mTarjeta->pan,
                'iin' => $mTarjeta->iin,
                'terminacion' => $mTarjeta->terminacion,
                'marca' => $mTarjeta->marca,
                'nombre' => $mTarjeta->nombre,
                'expiracion_mes' => $mTarjeta->expiracion_mes,
                'expiracion_anio' => $mTarjeta->expiracion_anio,
                'direccion' => $mTarjeta->direccion,
                'cliente_id' => $mTarjeta->cliente_uuid,
                'default' => $mTarjeta->default,
                'cargo_unico' => $mTarjeta->cargo_unico,
            ];
            // Regresa resultado
            return ejsend_success(['tarjeta' => $aRespuesta]);
        } catch (\Exception $e) {
            if (empty($e->getCode())) {
                $sCode = 400;
            } else {
                $sCode = (int) $e->getCode();
            }
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_fail(['code' => $sCode, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], $sCode, ['errors' => $e->getMessage()]);
        }
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
