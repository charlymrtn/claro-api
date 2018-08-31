<?php

namespace app\Http\Controllers\API\v1;

use Log;
use Auth;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Webpatser\Uuid\Uuid;
use App\Http\Controllers\ApiController;
use App\Models\Suscripciones\Plan;
use App\Models\Suscripciones\Suscripcion;
use App\Http\Resources\v1\SuscripcionResource;
use App\Http\Resources\v1\SuscripcionCollectionResource;
use App\Models\Medios\Tarjeta;

class SuscripcionController extends ApiController
{

    /**
     * Suscripcion instance.
     *
     * @var \App\Models\Suscripciones\Suscripcion
     */
    protected $mSuscripcion;

    /**
     * Plan instance.
     *
     * @var \App\Models\Suscripciones\Plan
     */
    protected $mPlan;

    /**
     * Tarjeta instance.
     *
     * @var \App\Models\Medios\Tarjeta
     */
    protected $mTarjeta;

    /**
     * SuscripcionController constructor.
     *
     * @param \App\Models\Suscripciones\Suscripcion $suscripcion
     */
    public function __construct(Suscripcion $suscripcion, Plan $plan, Tarjeta $tarjeta)
    {
        $this->mSuscripcion = $suscripcion;
        $this->mPlan = $plan;
        $this->mTarjeta = $tarjeta;
    }

    /**
     * Consultar listado de Suscripciones.
     *
     *  @param \Illuminate\Http\Request $oRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $oRequest): JsonResponse
    {
        // Regresa todas las suscripciones paginados
        try {
            // Verifica las variables para despliegue de datos
            $oValidator = Validator::make($oRequest->all(), [
                // Datos de filtros
                'filtro' => 'max:100',
                // Datos de la paginación
                'registros_por_pagina' => 'numeric|between:5,100',
                'pagina' => 'numeric|min:1',
                'ordenar_por' => 'max:30|in:uuid,comercio_uuid,nombre,apellido_paterno,apellido_materno,sexo,email,nacimiento,estado',
                'orden' => 'in:asc,desc',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Obtiene usuario autenticado
            $oUser = $this->getApiUser();
            // Filtro
            $sFiltro = $oRequest->input('filtro', false);
            // Busca suscripcion
            $cSuscripcion = new SuscripcionCollectionResource($this->mSuscripcion
                ->where('comercio_uuid', $oUser->comercio_uuid)
                ->where(
                    function ($q) use ($sFiltro) {
                        if ($sFiltro !== false) {
                            return $q
                                ->orWhere('uuid', 'like', "%$sFiltro%")
                                ->orWhere('plan_uuid', 'like', "%$sFiltro%")
                                ->orWhere('cliente_uuid', 'like', "%$sFiltro%")
                                ->orWhere('metodo_pago', 'like', "%$sFiltro%")
                                ->orWhere('estado', 'like', "%$sFiltro%");
                        }
                    }
                )
                ->orderBy($oRequest->input('ordenar_por', 'created_at'), $oRequest->input('orden', 'desc'))
                ->paginate((int) $oRequest->input('registros_por_pagina', 25), ['*'], 'pagina', (int) $oRequest->input('pagina', 1)));
            // Envía datos paginados
            return ejsend_success(['suscripciones' => $cSuscripcion]);
        } catch (\Exception $e) {
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_exception($e, 'Error al mostrar los recursos: ' . $e->getMessage());
        }
    }

    /**
     * Crear Suscripcion.
     *
     * @param  \Illuminate\Http\Request $oRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $oRequest): JsonResponse
    {
        // Guarda suscripcion
        try {
            // Valida estructura del request
            $this->validateJson($oRequest->getContent());
            // Obtiene usuario autenticado
            $oUser = $this->getApiUser();
            // Define valores por default antes de validación
            $oRequest->merge([
                'uuid' => Uuid::generate(4)->string,
                'comercio_uuid' => $oUser->comercio_uuid,
                'plan_uuid' => $oRequest->input('plan_id'),
                'cliente_uuid' => $oRequest->input('cliente_id'),
                'metodo_pago' => $oRequest->input('metodo_pago'),
                'metodo_pago_uuid' => $oRequest->input('metodo_pago_token'),
                'inicio' => $oRequest->input('inicio', 'now'),
            ]);
            // Parsea fechas
            $oRequest->merge($this->parseArrayDates($oRequest->all(), ['inicio', 'fin', 'prueba_inicio', 'prueba_fin', 'periodo_fecha_inicio', 'periodo_fecha_fin', 'fecha_proximo_cargo']));
            // Valida campos
            $oValidator = Validator::make($oRequest->all(), $this->mSuscripcion->rules, [
                'cliente_uuid.exists' => 'El cliente proporcionado no existe',
                'plan_uuid.exists' => 'El plan proporcionado no existe',
                'metodo_pago_uuid.required_with' => 'El token de metodo de pago es requerido',
                'cliente_uuid.uuid' => 'cliente_id',
                'cliente_uuid.size' => 'El campo cliente_id no es un identificador correcto',
                'cliente_uuid.required' => 'El campo cliente_id es requerido',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Valida si ya existe la suscripción
            $oPreviaSuscripcion = $this->mSuscripcion->where([
                ['comercio_uuid', '=', $oUser->comercio_uuid],
                ['plan_uuid', '=', $oRequest->input('plan_uuid')],
                ['cliente_uuid', '=', $oRequest->input('cliente_uuid')],
            ])->first();
            if (!empty($oPreviaSuscripcion)) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': El cliente ya tiene una suscripción al plan proporcionado.' . $oPreviaSuscripcion->uuid);
                return ejsend_fail(['code' => 409, 'type' => 'Suscripción', 'message' => 'El cliente ya tiene una suscripción al plan proporcionado: ' . $oPreviaSuscripcion->uuid], 409);
            }
            // Valida plan seleccionado
            $oPlan = $this->mPlan->find($oRequest->input('plan_uuid'));
            if (!$oPlan->puede_suscribir) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': El plan proporcionado no permite suscripciones.');
                return ejsend_fail(['code' => 412, 'type' => 'Suscripción', 'message' => 'El plan proporcionado no permite suscripciones.'], 412);
            }
            if ($oPlan->estado == 'inactivo') {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': El plan proporcionado se encuentra inactivo.');
                return ejsend_fail(['code' => 412, 'type' => 'Suscripción', 'message' => 'El plan proporcionado se encuentra inactivo.'], 412);
            }
            // Valida método de pago existente si es proporcionado
            if ($oRequest->input('metodo_pago') == 'tarjeta' && !empty($oRequest->input('token'))) {
                $oTarjeta = $this->mTarjeta->find($oRequest->input('token'));
                if ($oTarjeta == null) {
                    return ejsend_fail(['code' => 409, 'type' => 'Parámetros', 'message' => 'La tarjeta proporcionada no existe.'], 409);
                } elseif ($oTarjeta->cliente_uuid != $oRequest->input('cliente_uuid')) {
                    return ejsend_fail(['code' => 409, 'type' => 'Parámetros', 'message' => 'La tarjeta proporcionada no corresponde al cliente proporcionado.'], 409);
                }
            }
            // Crea objeto
            $oSuscripcion = new SuscripcionResource($this->mSuscripcion->create($oRequest->all()));
            // Regresa resultados
            return ejsend_success(['suscripcion' => $oSuscripcion]);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_exception($e, 'Error al crear el recurso: ' . $e->getMessage());
        }
    }

    /**
     * Consultar una Suscripción.
     *
     * @param  string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        // Muestra el recurso solicitado
        try {
            // Obtiene usuario autenticado
            $oUser = $this->getApiUser();
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
            // Busca suscripcion
            $oSuscripcion = $this->mSuscripcion->where('comercio_uuid', '=', $oUser->comercio_uuid)->find($uuid);
            if ($oSuscripcion == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Suscripción no encontrada:' . $uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Suscripción no encontrada.'], 404);
            }
            // Regresa suscripcion
            return ejsend_success(['suscripcion' => new SuscripcionResource($oSuscripcion)]);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return ejsend_exception($e, 'Error al mostrar el recurso: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar objeto Suscripción.
     *
     * @param  \Illuminate\Http\Request $oRequest
     * @param  string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $oRequest, string $uuid): JsonResponse
    {
        try {
            // Obtiene usuario autenticado
            $oUser = $this->getApiUser();
            // Valida uuid
            $oIdValidator = Validator::make(['uuid' => $uuid], [
                'uuid' => 'required|uuid|size:36',
            ]);
            if ($oIdValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oIdValidator->errors()]);
            }
            // Valida estructura del request
            $this->validateJson($oRequest->getContent());
            // Busca suscripción
            $oSuscripcion = $this->mSuscripcion->where('comercio_uuid', '=', $oUser->comercio_uuid)->find($uuid);
            if ($oSuscripcion == null) {
                Log::error('Error on '.__METHOD__.' line '.__LINE__.': Suscripción no encontrada:'.$uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            }
            // Define valores antes de validación
            $oRequest->merge([
                'metodo_pago_uuid' => $oRequest->input('token'),
            ]);
            // Parsea fechas
            $oRequest->merge($this->parseArrayDates($oRequest->all(), ['inicio', 'fin', 'prueba_inicio', 'prueba_fin', 'periodo_fecha_inicio', 'periodo_fecha_fin', 'fecha_proximo_cargo']));
            // Filtra campos aceptados para actualización
            $aCambios = array_only($oRequest->all(), ['metodo_pago', 'metodo_pago_uuid', 'estado']);
            // Valida campos
            $oValidator = Validator::make($aCambios, [
                'metodo_pago' => $this->mSuscripcion->rules['metodo_pago'],
                'metodo_pago_uuid' => $this->mSuscripcion->rules['metodo_pago_uuid'],
                'estado' => $this->mSuscripcion->rules['estado'],
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Actualiza suscripción
            $oSuscripcion->update($aCambios);
            return ejsend_success(['suscripcion' => new SuscripcionResource($oSuscripcion)]);
        } catch (\Exception $e) {
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_exception($e, 'Error al actualizar el recurso: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar suscripción.
     *
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $uuid): JsonResponse
    {
        return ejsend_fail(['code' => 405, 'type' => 'Sistema', 'message' => 'Método no implementado o no permitido para este recurso.'], 405);
    }

    /**
     * Cancelar suscripción.
     *
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelar(string $uuid): JsonResponse
    {
        try {
            // Obtiene usuario autenticado
            $oUser = $this->getApiUser();
            // Valida request
            $oIdValidator = Validator::make(['uuid' => $uuid], [
                'uuid' => 'required|uuid|size:36',
            ]);
            if ($oIdValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oIdValidator->errors()]);
            }
            // Busca suscripcion
            $oSuscripcion = $this->mSuscripcion->where('comercio_uuid', '=', $oUser->comercio_uuid)->find($uuid);
            if ($oSuscripcion == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Suscripción no encontrada:' . $uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Suscripción no encontrada.'], 404);
            }
            // Si ya está cancelada envía error
            if ($oSuscripcion->estado == 'cancelada') {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Suscripción ya cancelada.');
                return ejsend_fail(['code' => 412, 'type' => 'Suscripción', 'message' => 'Suscripción ya cancelada.'], 412);
            }
            // Cancela suscripción
            $oSuscripcion->cancela();
            // Regresa suscripcion
            return ejsend_success(['suscripcion' => new SuscripcionResource($oSuscripcion)]);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_exception($e, 'Error al cancelar el recurso: ' . $e->getMessage());
        }
    }
}
