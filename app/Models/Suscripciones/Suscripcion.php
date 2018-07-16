<?php

namespace App\Models\Suscripciones;

use Webpatser\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    protected $hidden = [];

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
    protected $rules = [
        'comercio_uuid' => 'required|string',
        'plan_uuid' => 'required|string',
        'cliente_uuid' => 'required|string',
        'metodo_pago' => 'in:Tarjeta',
        'metodo_pago_uuid' => 'required|string',
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

}