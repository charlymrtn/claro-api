<?php

namespace app\Http\Controllers\API\v1;

use Log;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Suscripciones\Plan;

class PlanController extends Controller
{

    public function __construct()
    {
        // @todo: Obtiene usuario y comercio
    }
    /**
     * Consultar listado de Planes.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $oRequest)
    {
        // Regresa todos los planes paginados
        try {
            // Verifica las variables para despliegue de datos
            $oValidator = Validator::make($oRequest->all(), [
                // Datos de la paginación y filtros
                'registros_por_pagina' => 'numeric|between:5,100',
                'pagina' => 'numeric|between:1,3',
                'ordenar_por' => 'max:30|in:uuid,comercio_uuid,nombre,estado,puede_suscribir,creacion',
                'orden' => 'in:asc,desc',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // @todo: Filtros
            $iRegistrosPorPagina = (int) $oRequest->input('registros_por_pagina', 10);
            $sOrdenarPor = $oRequest->input('ordenar_por', 'creacion');
            $aOrderBy = [
                'creacion' => 'created_at',
                'nombre' => 'nombre',
                'estado' => 'estado',
                'puede_suscribir' => 'puede_suscribir',
            ];
            $cPlanes = factory(Plan::class, $iRegistrosPorPagina)->create()->sortBy($aOrderBy[$sOrdenarPor]);
            // Formatea objeto resultado
            $aPlanes = [];
            foreach($cPlanes as $item) {
                $aPlanes[] = [
                    'id' => $item->uuid,
                    'nombre' => $item->nombre,
                    'monto' => $item->monto,
                    'moneda' => $item->moneda_iso_a3,
                    'frecuencia' => $item->frecuencia,
                    'tipo_periodo' => $item->tipo_periodo,
                    'max_reintentos' => $item->max_reintentos,
                    'estado' => $item->estado,
                    'puede_suscribir' => $item->puede_suscribir,
                    'prueba_frecuencia' => $item->prueba_frecuencia,
                    'prueba_tipo_periodo' => $item->prueba_tipo_periodo,
                    'creacion' => $item->created_at->toRfc3339String(),
                    'actualizacion' => $item->updated_at->toRfc3339String(),
                ];
            }
            $aResultado = [
                'total' => 30,
                'per_page' => $iRegistrosPorPagina,
                'current_page' => 1,
                'last_page' => 3,
                'from' => 1,
                'to' => $iRegistrosPorPagina,
                'data' => $aPlanes,
            ];
            // Envía datos paginados
            return ejsend_success(['planes' => $aResultado]);
        } catch (\Exception $e) {
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_error(['code' => 500, 'type' => 'Sistema', 'message' => 'Error al obtener el recurso: ' . $e->getMessage()]);
        }
    }

    /**
     * Crear Plan de Suscripcion.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $oRequest)
    {
        //
        $oPlan = factory(Plan::class)->create([]);
        $aPlan = [
            'id' => $uuid,
            'nombre' => $oRequest->input('nombre', $oPlan->nombre),
            'monto' => $oRequest->input('monto', $oPlan->monto),
            'moneda' => $oRequest->input('moneda', $oPlan->moneda_iso_a3),
            'frecuencia' => $oRequest->input('frecuencia', $oPlan->frecuencia),
            'tipo_periodo' => $oRequest->input('tipo_periodo', $oPlan->tipo_periodo),
            'max_reintentos' => $oRequest->input('max_reintentos', $oPlan->max_reintentos),
            'estado' => $oRequest->input('estado', $oPlan->estado),
            'puede_suscribir' => $oRequest->input('puede_suscribir', $oPlan->puede_suscribir),
            'prueba_frecuencia' => $oRequest->input('prueba_frecuencia', $oPlan->prueba_frecuencia),
            'prueba_tipo_periodo' => $oRequest->input('prueba_tipo_periodo', $oPlan->prueba_tipo_periodo),
            'creacion' => $oPlan->created_at->toRfc3339String(),
            'actualizacion' => $oPlan->updated_at->toRfc3339String(),
        ];
        // Envía datos paginados
        return ejsend_success(['plan' => $aPlan]);
    }

    /**
     * Consultar un Plan.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $uuid)
    {
        //
        $oPlan = factory(Plan::class)->create([]);
        $aPlan = [
            'id' => $uuid,
            'nombre' => $oPlan->nombre,
            'monto' => $oPlan->monto,
            'moneda' => $oPlan->moneda_iso_a3,
            'frecuencia' => $oPlan->frecuencia,
            'tipo_periodo' => $oPlan->tipo_periodo,
            'max_reintentos' => $oPlan->max_reintentos,
            'estado' => $oPlan->estado,
            'puede_suscribir' => $oPlan->puede_suscribir,
            'prueba_frecuencia' => $oPlan->prueba_frecuencia,
            'prueba_tipo_periodo' => $oPlan->prueba_tipo_periodo,
            'creacion' => $oPlan->created_at->toRfc3339String(),
            'actualizacion' => $oPlan->updated_at->toRfc3339String(),
        ];
        // Envía datos paginados
        return ejsend_success(['plan' => $aPlan]);
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
        //
        $oPlan = factory(Plan::class)->create([]);
        $aPlan = [
            'id' => $uuid,
            'nombre' => $oRequest->input('nombre', $oPlan->nombre),
            'monto' => $oRequest->input('monto', $oPlan->monto),
            'moneda' => $oRequest->input('moneda', $oPlan->moneda_iso_a3),
            'frecuencia' => $oRequest->input('frecuencia', $oPlan->frecuencia),
            'tipo_periodo' => $oRequest->input('tipo_periodo', $oPlan->tipo_periodo),
            'max_reintentos' => $oRequest->input('max_reintentos', $oPlan->max_reintentos),
            'estado' => $oRequest->input('estado', $oPlan->estado),
            'puede_suscribir' => $oRequest->input('puede_suscribir', $oPlan->puede_suscribir),
            'prueba_frecuencia' => $oRequest->input('prueba_frecuencia', $oPlan->prueba_frecuencia),
            'prueba_tipo_periodo' => $oRequest->input('prueba_tipo_periodo', $oPlan->prueba_tipo_periodo),
            'creacion' => $oPlan->created_at->toRfc3339String(),
            'actualizacion' => $oPlan->updated_at->toRfc3339String(),
        ];
        // Envía datos paginados
        return ejsend_success(['plan' => $aPlan]);
    }

    /**
     * Cancelar Plan.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        //
    }
}
