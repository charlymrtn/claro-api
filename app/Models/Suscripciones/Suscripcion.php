<?php

namespace App\Models\Suscripciones;

use Carbon\Carbon;
use Webpatser\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Suscripciones\Plan;

class Suscripcion extends Model
{
    // Traits
    use Notifiable, SoftDeletes;

    // Nombre de la tabla
    protected $table = 'suscripcion_suscripcion';

    /**
     * Se elimina autoincrementable
     * @var $incrementing string
     */
    public $incrementing = false;
    protected $primaryKey = 'uuid';
    protected $keyType = 'uuid';

    /**
     * Atributos
     * @var $fillable array
     */
    protected $fillable = [
        'uuid', // UUID del plan de suscripción en Claro Pagos
        // Relaciones
        'comercio_uuid', // UUID del comercio
        'plan_uuid', // UUID del plan
        'cliente_uuid', // UUID del cliente
        'metodo_pago', // Medio de pago utilizada para pagar la suscripción App\Models\Medios ['Tarjeta']
        'metodo_pago_uuid', // UUID del medio de pago
        // Datos de la suscripción
        'estado', // ['prueba', 'activa', 'pendiente', 'suspendida', 'cancelada']
        'inicio', // Fecha de inicio de la suscripción
        'fin', // Fecha de término de la suscripción
        'prueba_inicio', // Fecha de inicio de periodo de prueba
        'prueba_fin', // Fecha de término de periodo de prueba
        'periodo_fecha_inicio', // Fecha de inicio del periodo actual
        'periodo_fecha_fin', // Fecha del último día de periodo actual
        'fecha_proximo_cargo', // Fecha de próximo cargo del plan
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['comercio_uuid'];

    /**
     * Atributos mutables a fechas.
     *
     * @var array
     */
    protected $dates = [
        'inicio', 'fin', 'prueba_inicio', 'prueba_fin',
        'periodo_fecha_inicio', 'periodo_fecha_fin', 'fecha_proximo_cargo',
        'deleted_at', 'created_at', 'updated_at'
    ];

    /**
     * Atributos mutables.
     *
     * @var array
     */
    protected $casts = [
    ];

    /* --------------------------------------------------------------
     * Reglas de validación
     * @var array $rules Reglas de validación
     */
    public $rules = [
        'comercio_uuid' => 'required|uuid|size:36',
        'plan_uuid' => 'required|uuid|size:36|exists:suscripcion_plan,uuid',
        'cliente_uuid' => 'required|uuid|size:36|exists:cliente,uuid',
        'metodo_pago' => 'in:tarjeta',
        'metodo_pago_uuid' => 'required|uuid|size:36|string',
        'estado' => 'in:prueba,activa,pendiente,suspendida,cancelada',
        'inicio' => 'date',
        'fin' => 'date',
        'prueba_inicio' => 'date',
        'prueba_fin' => 'date',
        'periodo_fecha_inicio' => 'date',
        'periodo_fecha_fin' => 'date',
        'fecha_proximo_cargo' => 'date',
    ];

    /** --------------------------------------------------------------
     *  Setup model event hooks
     */
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Uuid::generate(4)->string;
            }
        });
    }

    /** --------------------------------------------------------------
     * Relaciones
     */

    /**
     * Plan
     */
    public function plan()
    {
        return $this->belongsTo('App\Models\Suscripciones\Plan', 'plan_uuid', 'uuid');
    }

    /**
     * Cliente
     */
    public function cliente()
    {
        return $this->belongsTo('App\Models\Cliente', 'cliente_uuid', 'uuid');
    }

    /* --------------------------------------------------------------
     * Accessor & Mutators
     */

    /**
     * Define el inicio de la suscripcion
     *
     * @param Carbon $cFechaInicio
     *
     * @return void
     */
    public function setInicioAttribute(Carbon $cFechaInicio)
    {
        // Define datetime
        $this->attributes['inicio'] = $cFechaInicio;
        // Calcula fechas de periodos y estado de la suscripción
        $this->calculaFechas();
    }

    /**
     * Define el inicio de la suscripcion
     *
     * @param Carbon $cFechaInicio
     *
     * @return void
     */
    public function setPlanUuidAttribute(string $uUuid)
    {
        // Define plan_uuid
        $this->attributes['plan_uuid'] = $uUuid;
        // Calcula fechas de periodos y estado de la suscripción
        $this->calculaFechas();
    }

    /* --------------------------------------------------------------
     * Otros
     */

    /**
     * Valida input con las reglas de validación del modelo
     *
     * @param array $aAttributes Arreglo con valores de los campos
     *
     * @return void
     */
    public function valida($aAttributes)
    {
        $oValidator = Validator::make($aAttributes, $this->rules);
        if ($oValidator->fails()) {
            throw new Exception($oValidator->errors(), 400);
        }
    }

    /**
     * Calcula fechas y estado de la suscripción
     *
     * @return void
     */
    public function calculaFechas()
    {
        // Verifica ya exista la información completa
        if (isset($this->attributes['inicio']) && isset($this->attributes['plan_uuid'])) {
            // Variables
            $cNow = Carbon::now();
            // Obtiene plan
            $oPlan = Plan::find($this->attributes['plan_uuid']);
            if (!empty($oPlan)) {
                // Calcula fechas de prueba
                $this->attributes['prueba_inicio'] = $this->attributes['inicio']->copy();
                $this->attributes['prueba_fin'] = $this->attributes['prueba_inicio']->copy();
                if ($oPlan->prueba_tipo_periodo == 'dia') {
                    $this->attributes['prueba_fin']->addDays($oPlan->prueba_frecuencia);
                } elseif ($oPlan->prueba_tipo_periodo == 'semana') {
                    $this->attributes['prueba_fin']->addWeeks($oPlan->prueba_frecuencia);
                } elseif ($oPlan->prueba_tipo_periodo == 'mes') {
                    $this->attributes['prueba_fin']->addMonths($oPlan->prueba_frecuencia);
                } elseif ($oPlan->prueba_tipo_periodo == 'anio') {
                    $this->attributes['prueba_fin']->addYears($oPlan->prueba_frecuencia);
                }
                // Calcula fechas periodo
                $this->attributes['periodo_fecha_inicio'] = $this->attributes['prueba_fin']->copy()->addDay();
                $this->attributes['periodo_fecha_fin'] = $this->attributes['periodo_fecha_inicio']->copy();
                if ($oPlan->tipo_periodo == 'dia') {
                    $this->attributes['periodo_fecha_fin']->addDays($oPlan->prueba_frecuencia);
                } elseif ($oPlan->tipo_periodo == 'semana') {
                    $this->attributes['periodo_fecha_fin']->addWeeks($oPlan->prueba_frecuencia);
                } elseif ($oPlan->tipo_periodo == 'mes') {
                    $this->attributes['periodo_fecha_fin']->addMonths($oPlan->prueba_frecuencia);
                } elseif ($oPlan->tipo_periodo == 'anio') {
                    $this->attributes['periodo_fecha_fin']->addYears($oPlan->prueba_frecuencia);
                }
               // Define estado
                if ($cNow->lt($this->attributes['prueba_fin'])) {
                    $this->attributes['estado'] = 'prueba';
                } else {
                    $this->attributes['estado'] = 'activa';
                }
                // Calcula fecha de próximo cargo
                if ($this->attributes['estado'] == 'prueba') {
                    $this->attributes['fecha_proximo_cargo'] = $this->attributes['periodo_fecha_inicio']->copy();
                } elseif ($this->attributes['estado'] == 'activa') {
                    $this->attributes['fecha_proximo_cargo'] = $this->attributes['periodo_fecha_fin']->copy()->addDay();
                }
            } else {
                $this->attributes['prueba_inicio'] = $cNow;
                $this->attributes['prueba_fin'] = $cNow;
                $this->attributes['estado'] = 'prueba';
            }
        }
    }

    /**
     * Cancela suscripción
     *
     * @return void
     */
    public function cancela()
    {
        $this->attributes['estado'] = 'cancelada';
        $this->save();
    }
}