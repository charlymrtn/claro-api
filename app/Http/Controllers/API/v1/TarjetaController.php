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
use App\Classes\Pagos\Base\Direccion;
use App\Classes\Pagos\Base\Telefono;

class TarjetaController extends Controller
{

    /**
     * Tarjeta instance.
     *
     * @var \App\Models\Medios\Tarjeta
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
            $oTarjetaCredito = $this->tarjetaRequest($oRequest);
            // Guarda resultado en base de datos
            $oTarjeta = $this->mTarjeta->create(array_merge([
                    'comercio_uuid' => $oRequest->user()->comercio_uuid,
                    'default' => $oRequest->input('default', false),
                    'cargo_unico' => $oRequest->input('cargo_unico', true),
                ], $oTarjetaCredito->toArray()));
            // Envía tarjeta a bóveda
            // Guarda resultado en base de datos
            // Formatea respuestay regresa resultado
            return ejsend_success(['tarjeta' => $this->tarjetaResponse($oTarjeta)]);
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
     * @param  string $uuid
     * @return \Illuminate\Http\Response
     */
    public function show(string $uuid)
    {
        // Busca tarjeta
        try {
            // Obtiene usuario del request
            $oUser = Auth::user();
            // Busca tarjeta
            $oTarjeta = $this->mTarjeta->where('comercio_uuid', $oUser->comercio_uuid)->find($uuid);
            if ($oTarjeta == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Objeto no encontrado');
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            } else {
                // Regresa usuario con clientes y tokens
                return ejsend_success(['tarjeta' => $this->tarjetaResponse($oTarjeta)]);
            }
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
     * Actualiza datos de tarjeta.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @param  string $uuid
     * @return \Illuminate\Http\Response
     */
    public function update(Request $oRequest, string $uuid)
    {
        // Formatea y encapsula datos
        try {
            // Obtiene usuario del request
            $oUser = Auth::user();
            // Busca tarjeta
            $oTarjeta = $this->mTarjeta->where('comercio_uuid', $oUser->comercio_uuid)->find($uuid);
            // Crea TarjetaCredito y valida
            $oTarjetaCredito = $this->tarjetaRequest($oRequest);
            // Actualiza en base de datos
            $oTarjeta->update(array_merge([
                    'comercio_uuid' => $oRequest->user()->comercio_uuid,
                    'default' => $oRequest->input('default', false),
                    'cargo_unico' => $oRequest->input('cargo_unico', true),
                ], $oTarjetaCredito->toArray()));
            // Envía tarjeta a bóveda
            // Guarda resultado en base de datos
            // Formatea respuestay regresa resultado
            return ejsend_success(['tarjeta' => $this->tarjetaResponse($oTarjeta)]);
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
     * Elimina tarjeta.
     *
     * @param  string $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $uuid)
    {
        // Busca tarjeta
        try {
            // Obtiene usuario del request
            $oUser = Auth::user();
            // Busca tarjeta
            $oTarjeta = $this->mTarjeta->where('comercio_uuid', $oUser->comercio_uuid)->find($uuid);
            //$oTarjeta = $this->mTarjeta->find($uuid);
            if ($oTarjeta == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Objeto no encontrado');
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            } else {
                $oTarjeta->forceDelete();
                // Regresa usuario con clientes y tokens
                return ejsend_success(['tarjeta' => $this->tarjetaResponse($oTarjeta)]);
            }
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
     * Formato de request de tarjeta
     *
     * @param  Request $oRequest Request con datos de tarjeta
     * @return array
     */
    private function tarjetaRequest(Request $oRequest): TarjetaCredito
    {
        return $oTarjetaCredito = new TarjetaCredito([
            'nombre' => $oRequest->input('nombre'),
            'pan' => $oRequest->input('pan'),
            'cvv2' => $oRequest->input('cvv2'),
            'expiracion_mes' => $oRequest->input('expiracion_mes'),
            'expiracion_anio' => $oRequest->input('expiracion_anio'),
            'direccion' => new Direccion([
                'linea1' => $oRequest->input('direccion.linea1', ''),
                'linea2' => $oRequest->input('direccion.linea2', ''),
                'linea3' => $oRequest->input('direccion.linea3', ''),
                'cp' => $oRequest->input('direccion.cp', '0000'),
                'pais' => $oRequest->input('direccion.pais', 'MEX'),
                'estado' => $oRequest->input('direccion.estado', 'CMX'),
                'ciudad' => $oRequest->input('direccion.ciudad', 'CDMX'),
                'municipio' => $oRequest->input('direccion.municipio', 'Delegación'),
                'telefono' => new Telefono([
                    'tipo' => $oRequest->input('direccion.telefono.tipo', 'desconocido'),
                    'codigo_pais' => $oRequest->input('direccion.telefono.codigo_pais', '52'),
                    'codigo_area' => $oRequest->input('direccion.telefono.codigo_area', '55'),
                    'numero' => $oRequest->input('direccion.telefono.numero', '0000000000'),
                    'extension' => $oRequest->input('direccion.telefono.extension', null),
                ]),
            ]),
        ]);
    }

    /**
     * Formato de respuesta de tarjeta
     *
     * @param  TarjetaCredito $oTarjeta Objeto de modelo de TarjetaCredito
     * @return array
     */
    private function tarjetaResponse(Tarjeta $oTarjeta): array
    {
        return [
            'token_tarjeta' => $oTarjeta->uuid,
            'pan' => $oTarjeta->pan,
            'iin' => $oTarjeta->iin,
            'terminacion' => $oTarjeta->terminacion,
            'marca' => $oTarjeta->marca,
            'nombre' => $oTarjeta->nombre,
            'expiracion_mes' => $oTarjeta->expiracion_mes,
            'expiracion_anio' => $oTarjeta->expiracion_anio,
            'direccion' => $oTarjeta->direccion,
            'cliente_id' => $oTarjeta->cliente_uuid,
            'default' => $oTarjeta->default,
            'cargo_unico' => $oTarjeta->cargo_unico,
        ];
    }
}
