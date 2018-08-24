<?php

namespace app\Http\Controllers\API\v1;

use Log;
use Auth;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Medios\Tarjeta;
use App\Classes\Pagos\Medios\TarjetaCredito;
use App\Classes\Pagos\Base\Direccion;
use App\Classes\Pagos\Base\Telefono;
use App\Http\Resources\v1\TarjetaResource;
use App\Http\Resources\v1\TarjetaCollectionResource;

class TarjetaController extends Controller
{

    /**
     * Tarjeta instance.
     *
     * @var \App\Models\Medios\Tarjeta
     */
    protected $mTarjeta;

    /**
     * TarjetaController constructor.
     *
     * @param Tarjeta $tarjeta
     */
    public function __construct(Tarjeta $tarjeta)
    {
        $this->mTarjeta = $tarjeta;
    }

    /**
     * Obtiene la lista de tarjetas creadas por el comercio
     *
     * @param \Illuminate\Http\Request $oRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $oRequest): JsonResponse
    {
        // Regresa los registros de tarjeta paginados
        try {
            // Verifica las variables para despliegue de datos
            $oValidator = Validator::make($oRequest->all(), [
                // Datos de filtros
                'filtro' => 'max:100',
                // Datos de la paginación
                'registros_por_pagina' => 'numeric|between:5,100',
                'pagina' => 'numeric',
                'ordenar_por' => 'max:30|in:uuid,nombre,marca,comercio_uuid,cliente_uuid,iin,pan,terminacion,created_at,updated_at,deleted_at',
                'orden' => 'in:asc,desc',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Obtiene usuario del request
            $oUser = Auth::user();
            // Filtro
            $sFiltro = $oRequest->input('filtro', false);
            // Busca tarjeta
            $aTarjeta = new TarjetaCollectionResource($this->mTarjeta
                ->where('comercio_uuid', $oUser->comercio_uuid)
                ->where(
                    function ($q) use ($sFiltro) {
                        if ($sFiltro !== false) {
                            return $q
                                ->orWhere('uuid', 'like', "%$sFiltro%")
                                ->orWhere('nombre', 'like', "%$sFiltro%")
                                ->orWhere('marca', 'like', "%$sFiltro%")
                                ->orWhere('comercio_uuid', 'like', "%$sFiltro%")
                                ->orWhere('cliente_uuid', 'like', "%$sFiltro%")
                                ->orWhere('pan', 'like', "%$sFiltro%");
                        }
                    }
                )
                ->orderBy($oRequest->input('ordenar_por', 'created_at'), $oRequest->input('orden', 'desc'))
                ->paginate((int) $oRequest->input('registros_por_pagina', 25)));
            // Envía datos paginados
            return ejsend_success(["tarjetas" => $aTarjeta]);
        } catch (\Exception $e) {
            // Define error
            if (empty($e->getCode())) {
                $sCode = 400; $sErrorType = 'Parámetros';
            } else {
                $sCode = (int) $e->getCode(); $sErrorType = 'Sistema';
            }
            // Registra error
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_error(['code' => $sCode, 'type' => $sErrorType, 'message' => 'Error al obtener el recurso: ' . $e->getMessage()]);
        }
    }

    /**
     * Guarda una tarjeta
     *
     * @param  \Illuminate\Http\Request $oRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $oRequest): JsonResponse
    {
        // Guarda tarjeta
        try {
            // Obtiene comercio_uuid del token del usuario de la petición
            $sComercioUuid = $oRequest->user()->comercio_uuid;
            // Crea tarjeta y valida
            $oTarjetaCredito = $this->tarjetaRequest($oRequest);
            // Define campos
            $aTarjeta = array_filter_null(array_merge([
                    'comercio_uuid' => $oRequest->user()->comercio_uuid,
                    'cliente_uuid' => $oRequest->input('cliente_id', null),
                    'default' => $oRequest->input('default', false),
                    'cargo_unico' => $oRequest->input('cargo_unico', true),
                ], $oTarjetaCredito->toArray()));
            // Valida campos
            $oValidator = Validator::make($aTarjeta, $this->mTarjeta->rules, ['cliente_uuid.exists' => 'El cliente proporcionado no existe']);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Verifica que la tarjeta no exista con el cliente proporcionado
            if (!empty($oRequest->input('cliente_id'))) {
                // Busca tarjeta
                $oTarjeta = $this->mTarjeta->where('comercio_uuid', $sComercioUuid)->where('cliente_uuid', $oRequest->input('cliente_id'))->where('pan_hash', $oTarjetaCredito->pan_hash)->first();
                if ($oTarjeta != null) {
                    // Regresa error de tarjeta existente
                    return ejsend_fail(['code' => 409, 'type' => 'Tarjeta', 'message' => 'La tarjeta ya existe para el cliente proporcionado'], 409);
                }
            }
            // Guarda resultado en base de datos
            $oTarjeta = $this->mTarjeta->create($aTarjeta);
            // Envía tarjeta a bóveda
            // Guarda resultado en base de datos
            // Formatea respuestay regresa resultado
            return ejsend_success(['tarjeta' => new TarjetaResource($oTarjeta)]);
        } catch (\Exception $e) {
            // Define error
            if (empty($e->getCode())) {
                $sCode = 400; $sErrorType = 'Parámetros';
            } else {
                $sCode = (int) $e->getCode(); $sErrorType = 'Sistema';
            }
            // Registra error
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_error(['code' => $sCode, 'type' => $sErrorType, 'message' => 'Error en parámetros de entrada: ' . $e->getMessage()], $sCode);
        }
    }

    /**
     * Muestra tarjeta solicitada.
     *
     * @param  string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        // Busca tarjeta
        try {
            $oValidator = Validator::make(['uuid' => $uuid], [
                'uuid' => 'required|uuid|size:36',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Obtiene usuario del request
            $oUser = Auth::user();
            // Busca tarjeta
            $oTarjeta = $this->mTarjeta->where('comercio_uuid', $oUser->comercio_uuid)->find($uuid);
            if ($oTarjeta == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Objeto no encontrado');
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            } else {
                // Regresa tarjeta
                return ejsend_success(['tarjeta' => new TarjetaResource($oTarjeta)]);
            }
        } catch (\Exception $e) {
            // Define error
            if (empty($e->getCode())) {
                $sCode = 400; $sErrorType = 'Parámetros';
            } else {
                $sCode = (int) $e->getCode(); $sErrorType = 'Sistema';
            }
            // Registra error
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_error(['code' => $sCode, 'type' => $sErrorType, 'message' => 'Error al obtener el recurso: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualiza datos de tarjeta.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @param  string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $oRequest, string $uuid): JsonResponse
    {
        // Formatea y encapsula datos
        try {
            // Obtiene usuario del request
            $oUser = Auth::user();
            // Busca tarjeta
            $oTarjeta = $this->mTarjeta->where('comercio_uuid', $oUser->comercio_uuid)->find($uuid);
            if ($oTarjeta == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Tarjeta no encontrada:' . $uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Tarjeta no encontrada.'], 404);
            }
            // Define cambios
            $aCambios = [];
            $aCambios['direccion'] = new Direccion([
                'pais' => $oRequest->input('direccion.pais', $oTarjeta->direccion->pais),
                'estado' => $oRequest->input('direccion.estado', $oTarjeta->direccion->estado),
                'ciudad' => $oRequest->input('direccion.ciudad', $oTarjeta->direccion->ciudad),
                'municipio' => $oRequest->input('direccion.municipio', $oTarjeta->direccion->municipio ?? ''),
                'linea1' => $oRequest->input('direccion.linea1', $oTarjeta->direccion->linea1 ?? ''),
                'linea2' => $oRequest->input('direccion.linea2', $oTarjeta->direccion->linea2 ?? ''),
                'linea3' => $oRequest->input('direccion.linea3', $oTarjeta->direccion->linea3 ?? ''),
                'cp' => $oRequest->input('direccion.cp', $oTarjeta->direccion->cp ?? '0000'),
                'longitud' => $oRequest->input('direccion.longitud', $oTarjeta->direccion->longitud ?? 0),
                'latitud' => $oRequest->input('direccion.latitud', $oTarjeta->direccion->latitud ?? 0),
                'referencia_1' => $oRequest->input('direccion.referencia_1', $oTarjeta->direccion->referencia_1 ?? null),
                'referencia_2' => $oRequest->input('direccion.referencia_2', $oTarjeta->direccion->referencia_2 ?? null),
                'telefono' => new Telefono([
                    'tipo' => $oRequest->input('telefono.tipo', $oTarjeta->direccion->telefono->tipo ?? 'desconocido'),
                    'codigo_pais' => $oRequest->input('telefono.codigo_pais', $oTarjeta->direccion->telefono->codigo_pais ?? 52),
                    'prefijo' => $oRequest->input('telefono.prefijo', $oTarjeta->direccion->telefono->prefijo ?? null),
                    'codigo_area' => $oRequest->input('telefono.codigo_area', $oTarjeta->direccion->telefono->codigo_area ?? 55),
                    'numero' => $oRequest->input('telefono.numero', $oTarjeta->direccion->telefono->numero ?? '0000000000'),
                    'extension' => $oRequest->input('telefono.extension', $oTarjeta->direccion->telefono->extension ?? null),
                ]),
            ]);
            $oRequest->merge($aCambios);
            // Actualiza en base de datos
            $oTarjeta->update($oRequest->all());
            // Envía tarjeta a bóveda
            // Guarda resultado en base de datos
            // Formatea respuestay regresa resultado
            return ejsend_success(['tarjeta' => new TarjetaResource($oTarjeta)]);
        } catch (\Exception $e) {
            // Define error
            if (empty($e->getCode())) {
                $sCode = 400; $sErrorType = 'Parámetros';
            } else {
                $sCode = (int) $e->getCode(); $sErrorType = 'Sistema';
            }
            // Registra error
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_error(['code' => $sCode, 'type' => $sErrorType, 'message' => 'Error al obtener el recurso: ' . $e->getMessage()]);
        }
    }

    /**
     * Elimina tarjeta.
     *
     * @param  string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $uuid): JsonResponse
    {
        // Busca tarjeta
        try {
            // Valida datos de entrada
            $oValidator = Validator::make(['uuid' => $uuid], [
                'uuid' => 'required|uuid',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Obtiene usuario del request
            $oUser = Auth::user();
            // Busca tarjeta
            $oTarjeta = $this->mTarjeta->where('comercio_uuid', $oUser->comercio_uuid)->find($uuid);
            if ($oTarjeta == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Objeto no encontrado');
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            } else {
                $oTarjeta->forceDelete();
                // Regresa usuario con clientes y tokens
                return ejsend_success(['tarjeta' => ['token' => $uuid, 'eliminacion' => Carbon::now()->toIso8601String()]]);
            }
        } catch (\Exception $e) {
            // Define error
            if (empty($e->getCode())) {
                $sCode = 400; $sErrorType = 'Parámetros';
            } else {
                $sCode = (int) $e->getCode(); $sErrorType = 'Sistema';
            }
            // Registra error
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_error(['code' => $sCode, 'type' => $sErrorType, 'message' => 'Error al obtener el recurso: ' . $e->getMessage()]);
        }
    }

    /**
     * Formato de request de tarjeta
     *
     * @param  Request $oRequest Request con datos de tarjeta
     * @return TarjetaCredito
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
                'municipio' => $oRequest->input('direccion.municipio', ''),
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
}