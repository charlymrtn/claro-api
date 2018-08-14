<?php

namespace app\Http\Controllers\API\v1;

use Log;
use Auth;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Webpatser\Uuid\Uuid;
use App\Http\Controllers\ApiController;
use App\Models\Suscripciones\Plan;
use App\Models\Suscripciones\Suscripcion;
use App\Http\Resources\v1\PlanResource;
use App\Http\Resources\v1\PlanCollectionResource;
use App\Http\Resources\v1\SuscripcionResource;
use App\Http\Resources\v1\SuscripcionCollectionResource;

class PlanController extends ApiController
{

    /**
     * Plan instance.
     *
     * @var \App\Models\Suscripciones\Plan
     */
    protected $mPlan;

    /**
     * Suscripcion instance.
     *
     * @var \App\Models\Suscripciones\Suscripcion
     */
    protected $mSuscripcion;

    /**
     * SuscripcionController constructor.
     *
     * @param \App\Models\Suscripciones\Suscripcion $suscripcion
     */
    public function __construct(Plan $plan, Suscripcion $suscripcion)
    {
        $this->mPlan = $plan;
        $this->mSuscripcion = $suscripcion;
    }

    /**
     * Consultar listado de Planes.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $oRequest): JsonResponse
    {
        // Regresa los registros de planes paginados
        try {
            // Verifica las variables para despliegue de datos
            $oValidator = Validator::make($oRequest->all(), [
                // Datos de filtros
                'filtro' => 'max:100',
                // Datos de la paginación
                'registros_por_pagina' => 'numeric|between:5,100',
                'pagina' => 'numeric|between:1,3',
                'ordenar_por' => 'max:30|in:uuid,nombre,monto,estado,puede_suscribir,creacion',
                'orden' => 'in:asc,desc',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Obtiene usuario del request
            $oUser = Auth::user();
            // Filtro
            $sFiltro = $oRequest->input('filtro', false);
            $aOrderByLabels = [
                'id' => 'uuid',
                'plan_id' => 'uuid',
                'creacion' => 'created_at',
                'cliente' => 'cliente_uuid',
                'estado' => 'estado',
                'inicio' => 'inicio',
                'fin' => 'fin',
                'fecha_proximo_cargo' => 'fecha_proximo_cargo',
            ];
            $sOrderBy = $aOrderByLabels[$oRequest->input('ordenar_por', 'creacion')] ?? 'created_at';
            // Busca cliente
            $cPlanes = new PlanCollectionResource($this->mPlan
                ->where('comercio_uuid', $oUser->comercio_uuid)
                ->where(
                    function ($q) use ($sFiltro) {
                        if ($sFiltro !== false) {
                            return $q
                                ->orWhere('uuid', 'like', "%$sFiltro%")
                                ->orWhere('nombre', 'like', "%$sFiltro%")
                                ->orWhere('monto', 'like', "%$sFiltro%")
                                ->orWhere('estado', 'like', "%$sFiltro%");
                        }
                    }
                )
                ->orderBy($sOrderBy, $oRequest->input('orden', 'desc'))
                ->paginate((int) $oRequest->input('registros_por_pagina', 25), ['*'], 'pagina', (int) $oRequest->input('pagina', 1)));
            // Envía datos paginados
            return ejsend_success(['planes' => $cPlanes]);
        } catch (\Exception $e) {
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_exception($e, 'Error al mostrar los recursos: ' . $e->getMessage());
        }
    }

    /**
     * Crear Plan.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $oRequest): JsonResponse
    {
        // Guarda Plan
        try {
            // Valida estructura del request
            $this->validateJson($oRequest->getContent());
            // Obtiene comercio_uuid del token del usuario de la petición
            $sComercioUuid = $oRequest->user()->comercio_uuid;
            // Define valores por default antes de validación
            $oRequest->merge([
                'uuid' => Uuid::generate(4)->string,
                'comercio_uuid' => $sComercioUuid,
                'estado' => 'activo',
                'puede_suscribir' => true,
                'moneda_iso_a3' => $oRequest->input('moneda', 'MXN'),
                'nombre' => $oRequest->input('nombre', 'Plan ' . str_random(5)),
                'max_reintentos' => $oRequest->input('max_reintentos', 3),
                'prueba_frecuencia' => $oRequest->input('prueba_frecuencia', 1),
                'prueba_tipo_periodo' => $oRequest->input('prueba_tipo_periodo', 'mes'),
            ]);
            // Valida campos
            $oValidator = Validator::make($oRequest->all(), $this->mPlan->rules);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Crea objeto
            $oPlan = new PlanResource($this->mPlan->create($oRequest->all()));
            // Regresa resultados
            return ejsend_success(['plan' => $oPlan]);
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return ejsend_exception($e, 'Error al crear el recurso: ' . $e->getMessage());
        }
    }

    /**
     * Consultar un Plan.
     *
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        // Muestra el recurso solicitado
        try {
            // Obtiene comercio_uuid del usuario de la petición
            $sComercioUuid = Auth::user()->comercio_uuid;
            // Valida request
            $oValidator = Validator::make(['uuid' => $uuid], [
                'uuid' => 'required|uuid|size:36',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail([
                    'code' => 400,
                    'type' => 'Parámetros',
                    'message' => 'Error en parámetros de entrada.',
                ], 400, ['errors' => $oValidator->errors()]);
            }
            // Busca plan
            $oPlan = $this->mPlan->where('comercio_uuid', '=', $sComercioUuid)->find($uuid);
            if ($oPlan == null) {
                Log::error('Error on '.__METHOD__.' line '.__LINE__.': Plan no encontrado:'.$uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Plan no encontrado.'], 404);
            }
            // Regresa plan
            return ejsend_success(['plan' => new PlanResource($oPlan)]);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return ejsend_exception($e, 'Error al mostrar el recurso: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar Plan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $oRequest, string $uuid)
    {
        try {
            // Obtiene comercio_uuid del token del usuario de la petición
            $sComercioUuid = $oRequest->user()->comercio_uuid;
            // Valida uuid
            $oIdValidator = Validator::make(['uuid' => $uuid], [
                'uuid' => 'required|uuid|size:36',
            ]);
            if ($oIdValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oIdValidator->errors()]);
            }
            // Valida estructura del request
            $this->validateJson($oRequest->getContent());
            // Busca plan
            $oPlan = $this->mPlan->where('comercio_uuid', '=', $sComercioUuid)->find($uuid);
            if ($oPlan == null) {
                Log::error('Error on '.__METHOD__.' line '.__LINE__.': Suscripción no encontrada:'.$uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            }
            // Define valores antes de validación
            $oRequest->merge([
                'nombre' => $oRequest->input('nombre', 'Plan ' . str_random(5)),
            ]);
            // Filtra campos aceptados para actualización
            $aCambios = array_only(array_merge($oPlan->toArray(), $oRequest->all()), ['nombre', 'monto', 'frecuencia', 'tipo_periodo', 'max_reintentos', 'estado', 'puede_suscribir', 'prueba_frecuencia', 'prueba_tipo_periodo']);
            // Valida campos
            $oValidator = Validator::make($aCambios, [
                'nombre' => $this->mPlan->rules['nombre'],
                'monto' => $this->mPlan->rules['monto'],
                'frecuencia' => $this->mPlan->rules['frecuencia'],
                'tipo_periodo' => $this->mPlan->rules['tipo_periodo'],
                'max_reintentos' => $this->mPlan->rules['max_reintentos'],
                'estado' => $this->mPlan->rules['estado'],
                'puede_suscribir' => $this->mPlan->rules['puede_suscribir'],
                'prueba_frecuencia' => $this->mPlan->rules['prueba_frecuencia'],
                'prueba_tipo_periodo' => $this->mPlan->rules['prueba_tipo_periodo'],
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Actualiza suscripción
            $oPlan->update($aCambios);
            return ejsend_success(['plan' => new PlanResource($oPlan)]);
        } catch (\Exception $e) {
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_exception($e, 'Error al actualizar el recurso: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar Plan.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        return ejsend_fail(['code' => 405, 'type' => 'Sistema', 'message' => 'Método no implementado o no permitido para este recurso.'], 405);
    }

    /**
     * Obtiene las suscripciones del plan
     *
     * @param string  $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function suscripciones($uuid): JsonResponse
    {
        // Muestra el recurso solicitado
        try {
            // Obtiene comercio_uuid del usuario de la petición
            $sComercioUuid = Auth::user()->comercio_uuid;
            // Valida request
            $oIdValidator = Validator::make(['uuid' => $uuid], [
                'uuid' => 'required|uuid|size:36',
            ]);
            if ($oIdValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oIdValidator->errors()]);
            }
            // Busca plan
            $oPlan = $this->mPlan->where('comercio_uuid', '=', $sComercioUuid)->find($uuid);
            if ($oPlan == null) {
                Log::error('Error on '.__METHOD__.' line '.__LINE__.': Plan no encontrado:'.$uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Plan no encontrado.'], 404);
            }
            // Regresa suscripciones del cliente
            return ejsend_success(['suscripciones' => SuscripcionResource::collection($oPlan->suscripciones)]);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return ejsend_exception($e, 'Error al obtener los recursos: ' . $e->getMessage());
        }
    }

    /**
     * Cancela las suscripciones al plan
     *
     * @param string  $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelarSuscripciones($uuid): JsonResponse
    {
        try {
            // Obtiene comercio_uuid del usuario de la petición
            $sComercioUuid = Auth::user()->comercio_uuid;
            // Valida request
            $oIdValidator = Validator::make(['uuid' => $uuid], [
                'uuid' => 'required|uuid|size:36',
            ]);
            if ($oIdValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oIdValidator->errors()]);
            }
            // Busca plan
            $oPlan = $this->mPlan->where('comercio_uuid', '=', $sComercioUuid)->find($uuid);
            if ($oPlan == null) {
                Log::error('Error on '.__METHOD__.' line '.__LINE__.': Plan no encontrado:'.$uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Plan no encontrado.'], 404);
            }
            // Cancela suscripciones
            $aResultados = [];
            foreach($oPlan->suscripciones as $oSuscripcion) {
                if (in_array($oSuscripcion->estado, ['prueba', 'activa', 'pendiente'])) {
                    $oSuscripcion->cancela();
                    $aResultados[] = [
                        'suscripcion_id' => $oSuscripcion->uuid,
                        'cliente_id' => $oSuscripcion->cliente_uuid,
                        'estado' => $oSuscripcion->estado,
                        'actualizado' => $oSuscripcion->updated_at->toRfc3339String(),
                    ];
                }
            }
            // Regresa suscripciones del cliente
            return ejsend_success(['suscripciones' => $aResultados]);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return ejsend_exception($e, 'Error al cancelar los recursos: ' . $e->getMessage());
        }
    }

    /**
     * Cancela el plan
     *
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelar(string $uuid): JsonResponse
    {
        try {
            // Obtiene comercio_uuid del usuario de la petición
            $sComercioUuid = Auth::user()->comercio_uuid;
            // Valida request
            $oIdValidator = Validator::make(['uuid' => $uuid], [
                'uuid' => 'required|uuid|size:36',
            ]);
            if ($oIdValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oIdValidator->errors()]);
            }
            // Busca plan
            $oPlan = $this->mPlan->where('comercio_uuid', '=', $sComercioUuid)->find($uuid);
            if ($oPlan == null) {
                Log::error('Error on '.__METHOD__.' line '.__LINE__.': Plan no encontrado:'.$uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Plan no encontrado.'], 404);
            }
            // Valida si existen suscripciones sin cancelar
            $oSuscripcionesActivas = $this->mSuscripcion->where([
                ['comercio_uuid', '=', $sComercioUuid],
                ['plan_uuid', '=', $uuid],
            ])->whereIn('estado', ['prueba', 'activa', 'pendiente'])->get();
            if ($oSuscripcionesActivas->isNotEmpty()) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': No se puede cancelar el plan ya que tiene suscripciones activas, en prueba o pendientes: ' . $uuid);
                return ejsend_fail(['code' => 412, 'type' => 'Plan', 'message' => 'No se puede cancelar el plan ya que tiene suscripciones activas, en prueba o pendientes.'], 412);
            } else {
                $oPlan->cancela();
            }
            // Regresa suscripciones del cliente
            return ejsend_success(['plan' => new PlanResource($oPlan)]);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return ejsend_exception($e, 'Error al cancelar el recurso: ' . $e->getMessage());
        }
    }
}
