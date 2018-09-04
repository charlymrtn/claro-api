<?php

namespace app\Http\Controllers\API\v1;

use Log;
use Exception;
use Validator;
use Carbon\Carbon;
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
                'pagina' => 'numeric',
                'ordenar_por' => 'max:30|in:uuid,nombre,monto,estado,puede_suscribir,creacion',
                'orden' => 'in:asc,desc',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Obtiene usuario autenticado
            $oUser = $this->getApiUser();
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
            return ejsend_exception($e);
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
            // Obtiene usuario autenticado
            $oUser = $this->getApiUser();
            // Define valores por default antes de validación
            $oRequest->merge([
                'uuid' => Uuid::generate(4)->string,
                'comercio_uuid' => $oUser->comercio_uuid,
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
            // Valida cliente creado anteriormente con mismo id_externo
            $oPlanExistenteId = $this->mPlan
                ->where('comercio_uuid', '=', $oUser->comercio_uuid)
                ->where('id_externo', '=', $oRequest->input('id_externo'))
                ->first();
            if (!empty($oPlanExistenteId)) {
                return ejsend_fail(['code' => 409, 'type' => 'Parámetros', 'message' => 'Error al crear el recurso: Plan existente con el mismo id_externo (' . $oPlanExistenteId->uuid . ')'], 409);
            }
            // Crea objeto
            $oPlan = new PlanResource($this->mPlan->create($oRequest->all()));
            // Regresa resultados
            return ejsend_success(['plan' => $oPlan]);
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea ' . $e->getLine().':' . $e->getMessage());
            return ejsend_exception($e);
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
            // Obtiene usuario autenticado
            $oUser = $this->getApiUser();
            // Obtiene plan
            $oPlan = $this->getPlan($uuid, $oUser->comercio_uuid);
            // Regresa plan
            return ejsend_success(['plan' => new PlanResource($oPlan)]);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en '.__METHOD__.' línea ' . $e->getLine().':' . $e->getMessage());
            return ejsend_exception($e);
        }
    }

    /**
     * Consultar un Plan por el id externo
     *
     * @param string  $id_externo
     * @return \Illuminate\Http\JsonResponse
     */
    public function showExterno(string $id_externo): JsonResponse
    {
        // Muestra el recurso solicitado
        try {
            // Obtiene usuario autenticado
            $oUser = $this->getApiUser();
            // Valida request
            $oValidator = Validator::make(['id_externo' => $id_externo], [
                'id_externo' => 'required',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Busca plan
            $oPlan = $this->mPlan->where('comercio_uuid', '=', $oUser->comercio_uuid)->where('id_externo', '=', $id_externo)->first();
            if ($oPlan == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Plan id_externo no encontrado:' . $id_externo);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'El id_externo proporcionado no fue encontrado en el sistema.'], 404);
            }
            // Regresa cliente con el mismo formato que el método show()
            return $this->show($oPlan->uuid);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_exception($e);
        }
    }

    /**
     * Actualizar Plan.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @param  string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $oRequest, string $uuid): JsonResponse
    {
        try {
            // Valida estructura del request
            $this->validateJson($oRequest->getContent());
            // Obtiene usuario autenticado
            $oUser = $this->getApiUser();
            // Obtiene plan
            $oPlan = $this->getPlan($uuid, $oUser->comercio_uuid);
            // Filtra campos aceptados para actualización
            $aCambios = array_only($oRequest->all(), $this->mPlan->updatable);
            // Valida campos
            $oValidator = Validator::make($aCambios, array_only($this->mPlan->rules, array_keys($aCambios)));
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Actualiza suscripción
            $oPlan->update($aCambios);
            return ejsend_success(['plan' => new PlanResource($oPlan)]);
        } catch (\Exception $e) {
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_exception($e);
        }
    }

    /**
     * Cancelar Plan.
     *
     * @param  string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            // Obtiene usuario autenticado
            $oUser = $this->getApiUser();
            // Obtiene plan
            $oPlan = $this->getPlan($uuid, $oUser->comercio_uuid);
            if ($oPlan == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Objeto no encontrado');
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            }
            // Valida precondiciones
            // - El plan debe estar inactivo
            if ($oPlan->estado == 'inactivo' && $oPlan->puede_suscribir == false) {
                $oPlan->delete();
                // Regresa usuario con clientes y tokens
                return ejsend_success(['plan' => ['id' => $uuid, 'eliminacion' => Carbon::now()->toIso8601String()]]);
            } else {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': El plan proporcionado no puede ser cancelado porque sigue estando activo.');
                return ejsend_fail(['code' => 412, 'type' => 'Plan', 'message' => 'El plan proporcionado no puede ser cancelado porque sigue estando activo.'], 412);
            }
        } catch (\Exception $e) {
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_exception($e);
        }
        return ejsend_fail(['code' => 405, 'type' => 'Sistema', 'message' => 'Método no implementado o no permitido para este recurso.'], 405);
    }

    /**
     * Obtiene las suscripciones del plan
     *
     * @param string  $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function suscripciones(string $uuid): JsonResponse
    {
        // Muestra el recurso solicitado
        try {
            // Obtiene usuario autenticado
            $oUser = $this->getApiUser();
            // Obtiene plan
            $oPlan = $this->getPlan($uuid, $oUser->comercio_uuid);
            // Regresa suscripciones del cliente
            return ejsend_success(['suscripciones' => SuscripcionResource::collection($oPlan->suscripciones)]);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine().':' . $e->getMessage());
            return ejsend_exception($e);
        }
    }

    /**
     * Cancela las suscripciones al plan
     *
     * @param string  $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelarSuscripciones(string $uuid): JsonResponse
    {
        try {
            // Obtiene usuario autenticado
            $oUser = $this->getApiUser();
            // Obtiene plan
            $oPlan = $this->getPlan($uuid, $oUser->comercio_uuid);
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
            Log::error('Error en '.__METHOD__.' línea ' . $e->getLine().':' . $e->getMessage());
            return ejsend_exception($e);
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
            // Obtiene usuario autenticado
            $oUser = $this->getApiUser();
            // Obtiene plan
            $oPlan = $this->getPlan($uuid, $oUser->comercio_uuid);
            // Valida si existen suscripciones sin cancelar
            $oSuscripcionesActivas = $this->mSuscripcion->where([
                ['comercio_uuid', '=', $oUser->comercio_uuid],
                ['plan_uuid', '=', $uuid],
            ])->whereIn('estado', ['prueba', 'activa', 'pendiente'])->get();
            if ($oSuscripcionesActivas->isNotEmpty()) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': No se puede cancelar el plan ya que tiene suscripciones activas, en prueba o pendientes: ' . $uuid);
                return ejsend_fail(['code' => 412, 'type' => 'Plan', 'message' => 'No se puede cancelar el plan ya que tiene suscripciones activas, en prueba o pendientes.'], 412);
            }
            // Si ya está cancelada envía error
            if ($oPlan->estado == 'inactivo') {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Plan previamente cancelado.');
                return ejsend_fail(['code' => 412, 'type' => 'Suscripción', 'message' => 'Plan previamente cancelado.'], 412);
            }
            $oPlan->cancela();
            // Regresa suscripciones del cliente
            return ejsend_success(['plan' => new PlanResource($oPlan)]);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_exception($e);
        }
    }

    /* --------------------------------------------------------------
     * Métodos privados
     */

    /**
     * Obtiene plan validando el uuid
     *
     * @param string $uuid
     * @param string $comercio_uuid
     * @return \App\Models\Suscripciones\Plan
     */
    private function getPlan(string $uuid, string $comercio_uuid): Plan
    {
        // Valida request
        $oIdValidator = Validator::make(['plan_id' => $uuid], [
            'plan_id' => 'required|uuid|size:36',
        ]);
        if ($oIdValidator->fails()) {
            throw new Exception('El id_plan proporcionado no es válido.', 400);
        }
        // Busca plan
        $oPlan = $this->mPlan->where('comercio_uuid', '=', $comercio_uuid)->find($uuid);
        if ($oPlan == null) {
            throw new Exception('El id_plan proporcionado no fue encontrado en el sistema.', 404);
        }
        // Regresa Plan
        return $oPlan;
    }
}
