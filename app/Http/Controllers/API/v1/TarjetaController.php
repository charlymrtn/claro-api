<?php

namespace app\Http\Controllers\API\v1;

use Log;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Medios\Tarjeta;
use App\Classes\Pagos\Base\Direccion;
use App\Classes\Pagos\Base\Telefono;

class TarjetaController extends Controller
{
    protected $oTarjera;

    /**
     * TarjetaController constructor.
     * @param Tarjeta $tarjeta
     */
    public function __construct(Tarjeta $tarjeta)
    {
        $this->oTarjera = $tarjeta;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $oRequest)
    {
        try {
            // Verifica las variables para despliegue de datos
            $oValidator = Validator::make($oRequest->all(), [
                'per_page' => 'numeric|between:5,100',
                'order' => 'max:30|in:uuid,nombre,marca,comercio_uuid,cliente_uuid,iin,pan,terminacion,created_at,updated_at,deleted_at',
                'search' => 'max:100',
                'sort' => 'in:asc,desc',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => $sCode, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], $sCode, ['errors' => $oValidator->errors()]);
            }
            // Filtro
            $sFiltro = $oRequest->input('search', false);
            $sDeleted = $oRequest->input('deleted', 'yes');
            // Busca tarjeta
            $aTarjeta = $this->oTarjera
                ->withTrashed()
                ->where(
                    function ($q) use ($sFiltro) {
                        if ($sFiltro !== false) {
                            return $q
                                ->orWhere('uuid', 'like', "%$sFiltro%")
                                ->orWhere('nombre', 'like', "%$sFiltro%")
                                ->orWhere('marca', 'like', "%$sFiltro%")
                                ->orWhere('comercio_uuid', 'like', "%$sFiltro%")
                                ->orWhere('cliente_uuid', 'like', "%$sFiltro%")
                                ->orWhere('iin', 'like', "%$sFiltro%")
                                ->orWhere('pan', 'like', "%$sFiltro%")
                                ->orWhere('terminacion', 'like', "%$sFiltro%");
                        }
                    }
                )
                ->where(
                    function ($q) use ($sDeleted) {
                        if ($sDeleted == 'no') {
                            return $q->whereNull('deleted_at');
                        } else if ($sDeleted == 'yes') {
                            return $q;
                        } else if ($sDeleted == 'only') {
                            return $q->whereNotNull('deleted_at');
                        }
                    }
                )
                ->orderBy($oRequest->input('order', 'uuid'), $oRequest->input('sort', 'asc'))
                ->paginate((int) $oRequest->input('per_page', 25));
            // Envía datos paginados
            return ejsend_success(["status" => "success", "data" => ["tarjetas" => $aTarjeta]]);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return ejsend_error([
                'code' => 500,
                'type' => 'Tarjeta',
                'message' => 'Error al obtener el recurso: '.$e->getMessage(),
            ]);
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
        // Valida datos de entrada
        $oValidator = Validator::make($oRequest->toArray(), [
            'comercio_uuid' => 'required|string',
            'cliente_uuid' => 'required|string',
            'iin' => 'required|string',
            'marca' => 'required|string|min:3',
            'pan' => 'required|numeric',
            'terminacion' => 'required|numeric',
            'nombre' => 'required_without:nombres|min:3|max:60',
            'expiracion_mes' => 'required|numeric',
            'expiracion_anio' => 'required|numeric',
            'inicio_mes' => 'numeric|min:2',
            'inicio_anio' => 'numeric|min:2',
            'pan_hash' => 'required',
            'token' => 'required',
            'default' => 'boolean',
            'direccion' => 'array',
        ]);
        //retorna error si no pasa validacion
        if ($oValidator->fails()) {
            $sCode = '400';
            Log::error('Error de validación de parámetros: ' . json_encode($oValidator->errors()));
            return ejsend_fail(['code' => $sCode, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], $sCode, ['errors' => $oValidator->errors()]);
        }
        //nueva tarjeta
        try{
            $oRequest->merge([
                'iin' =>  $oRequest->input('iin'),
                'marca' => $oRequest->input('marca'),
                'pan' => $oRequest->input('pan'),
                'terminacion' => $oRequest->input('terminacion'),
                'nombre' => $oRequest->input('nombre'),
                'expiracion_mes' => $oRequest->input('expiracion_mes'),
                'expiracion_anio' => $oRequest->input('expiracion_anio'),
                'comercio_uuid' => $oRequest->input('comercio_uuid'),
                'cliente_uuid'  => $oRequest->input('cliente_uuid'),
                'inicio_mes' => $oRequest->input('inicio_mes'),
                'inicio_anio' => $oRequest->input('inicio_anio'),
                'pan_hash' => $oRequest->input('pan_hash'),
                'token' => $oRequest->input('token'),
                'default' => $oRequest->input('default'),
                'direccion' => new Direccion([
                    'pais' => $oRequest->input('direccion.pais','MEX'),
                    'estado' => $oRequest->input('direccion.estado','MEX'),
                    'ciudad' => $oRequest->input('direccion.ciudad' ,'ECA'),
                    'municipio' => $oRequest->input('direccion.municipio','ECATEPEC'),
                    'linea1' => $oRequest->input('direccion.linea1',''),
                    'linea2' => $oRequest->input('direccion.linea2',''),
                    'linea3' => $oRequest->input('direccion.linea3',''),
                    'cp' => $oRequest->input('direccion.cp','55024'),
                    'telefono' => new Telefono([
                        'tipo' => $oRequest->input('direccion.telefono.tipo','casa'),
                        'codigo_pais' => $oRequest->input('direccion.telefono.codigo_pais', '52'),
                        'codigo_area' => $oRequest->input('direccion.telefono.codigo_area','55'),
                        'numero' => $oRequest->input('direccion.telefono','0000000'),
                        'extension' => $oRequest->input('direccion.extension',null),
                    ]),
                ]),
            ]);
            // Crea objeto
            $oTarjeta = $this->oTarjera->create($oRequest->all());
            // Regresa resultados
            return ejsend_success(['tarjeta' => $oTarjeta]);
        }catch (\Exception $e) {
            if (empty($e->getCode())) {
                $sCode = '400';
            } else {
                $sCode = $e->getCode();
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
    public function show($uuid)
    {
        // Muestra el recurso solicitado
        try {
            $oValidator = Validator::make(['uuid' => $uuid], [
                'uuid' => 'required|uuid',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Busca registro
            $oTarjeta = $this->oTarjera->find($uuid);
            if ($oTarjeta == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Transacción no encontrada:' . $uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            }
            // Regresa transaccion con usuarios
            return ejsend_success(['transaccion' => $oTarjeta]);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_error(['code' => 500, 'type' => 'Sistema', 'message' => 'Error al obtener el recurso: ' . $e->getMessage()]);
        }
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
    public function update(Request $oRequest, $uuid): JsonResponse
    {
        try {
            // Valida id
            $oValidator = Validator::make(array_merge(['uuid' => $uuid], $oRequest->all()),  [
                'uuid' => 'required|uuid',
                'comercio_uuid' => 'required|string',
                'cliente_uuid' => 'required|string',
                'iin' => 'required|string',
                'marca' => 'required|string|min:3',
                'pan' => 'required|numeric',
                'terminacion' => 'required|numeric',
                'nombre' => 'required_without:nombres|min:3|max:60',
                'expiracion_mes' => 'required|numeric',
                'expiracion_anio' => 'required|numeric',
                'inicio_mes' => 'numeric|min:2',
                'inicio_anio' => 'numeric|min:2',
                'pan_hash' => 'required',
                'token' => 'required',
                'default' => 'boolean',
                'direccion' => 'array',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Busca transaccion
            $oTarjeta = $this->oTarjera->find($uuid);
            if ($oTarjeta == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Transacción no encontrada:' . $uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrada.'], 404);
            }
            $oRequest->merge([
                'iin' =>  $oRequest->input('iin','ccc-cc'),
                'marca' => $oRequest->input('marca'),
                'pan' => $oRequest->input('pan'),
                'terminacion' => $oRequest->input('terminacion'),
                'nombre' => $oRequest->input('nombre'),
                'expiracion_mes' => $oRequest->input('expiracion_mes'),
                'expiracion_anio' => $oRequest->input('expiracion_anio'),
                'comercio_uuid' => $oRequest->input('comercio_uuid'),
                'cliente_uuid'  => $oRequest->input('cliente_uuid'),
                'inicio_mes' => $oRequest->input('inicio_mes'),
                'inicio_anio' => $oRequest->input('inicio_anio'),
                'pan_hash' => $oRequest->input('pan_hash'),
                'token' => $oRequest->input('token'),
                'default' => $oRequest->input('default'),
                'direccion' => new Direccion([
                    'pais' => $oRequest->input('direccion.pais'),
                    'estado' => $oRequest->input('direccion.estado'),
                    'ciudad' => $oRequest->input('direccion.ciudad' ),
                    'municipio' => $oRequest->input('direccion.municipio'),
                    'linea1' => $oRequest->input('direccion.linea1'),
                    'linea2' => $oRequest->input('direccion.linea2'),
                    'linea3' => $oRequest->input('direccion.linea3'),
                    'cp' => $oRequest->input('direccion.cp'),
                    'telefono' => new Telefono([
                        'tipo' => $oRequest->input('direccion.telefono.tipo','casa'),
                        'codigo_pais' => $oRequest->input('direccion.telefono.codigo_pais', '52'),
                        'codigo_area' => $oRequest->input('direccion.telefono.codigo_area','55'),
                        'numero' => $oRequest->input('direccion.telefono','0000000'),
                        'extension' => $oRequest->input('direccion.extension',null),
                    ]),
                ]),
            ]);
            // Actualiza la transaccion
            $oTarjeta->update($oRequest->all());

            return ejsend_success(['tarjeta' => $oTarjeta]);
        } catch (\Exception $e) {
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_error(['code' => 500, 'type' => 'Sistema', 'message' => 'Error al actualizar el recurso: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        // Muestra el recurso solicitado
        try {
            $oValidator = Validator::make(['uuid' => $uuid], [
                'uuid' => 'required|uuid',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Busca registro
            $oTarjeta = $this->oTarjera->find($uuid);
            if ($oTarjeta == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Transacción no encontrada:' . $uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            }
            // Regresa transaccion con usuarios
            $oTarjeta->delete();
            return ejsend_success(['transaccion' => 'Eliminada']);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_error(['code' => 500, 'type' => 'Sistema', 'message' => 'Error al obtener el recurso: ' . $e->getMessage()]);
        }
    }
}