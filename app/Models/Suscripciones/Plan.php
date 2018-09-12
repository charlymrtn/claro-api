<?php

namespace App\Models\Suscripciones;

use Webpatser\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    // Traits
    use Notifiable, SoftDeletes;

    // Nombre de la tabla
    protected $table = 'suscripcion_plan';

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
        // Datos del comercio
        'comercio_uuid', // UUID del comercio
        'id_externo', // ID del cliente en el comercio
        // Datos del plan
        'nombre', // Nombre del plan
        'monto', // Monto que será cobrado en la suscripción
        'frecuencia', // Determina cuantas veces debe repetirse el tipo de periodo
        'tipo_periodo', // ['dia', 'semana', 'mes', 'anio']
        'max_reintentos', // 1 a 10
        'prueba_frecuencia', // Determina el periodo de prueba
        'prueba_tipo_periodo', // ['dia', 'semana', 'mes', 'anio']
        'estado', // ['inactivo', 'activo']
        'puede_suscribir', // Booleano
        // Objetos JSON
        // Catálogos
        'moneda_iso_a3', // Identificador de moneda de tres caracteres ISO 4217
    ];

    /**
     * Atributos escondidos.
     *
     * @var array
     */
    protected $hidden = ['comercio_uuid'];

    /**
     * Atributos mutables a fechas.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    /**
     * Atributos mutables.
     *
     * @var array
     */
    protected $casts = [
        'frecuencia' => 'integer',
        'prueba_frecuencia' => 'integer',
        'max_reintentos' => 'integer',
        'puede_suscribir' => 'boolean',
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
     * Suscripciones
     */
    public function suscripciones()
    {
        return $this->hasMany('App\Models\Suscripciones\Suscripcion', 'plan_uuid', 'uuid');
    }

    /* --------------------------------------------------------------
     * Accessor & Mutators
     */


    /* --------------------------------------------------------------
     * Reglas de validación y sanitizacion
     */

    /**
     * Reglas de validación
     *
     * @var array $rules Reglas de validación
     */
    public $rules = [
        'comercio_uuid' => 'required|string',
        'id_externo' => 'required|min:1|max:64', // unique:suscripcion_plan,id_externo|
        'nombre' => 'string|min:1|max:60',
        'monto' => 'required|numeric',
        'frecuencia' => 'required|integer',
        'tipo_periodo' => 'required|in:dia,semana,mes,anio',
        'max_reintentos' => 'integer|min:0|max:10',
        'prueba_frecuencia' => 'integer',
        'prueba_tipo_periodo' => 'in:dia,semana,mes,anio',
        'estado' => 'in:inactivo,activo',
        'puede_suscribir' => 'boolean',
        'moneda_iso_a3' => 'required|string|size:3',
    ];

    /**
     * Mensajes de error específicos
     *
     * @var array $validation_messages Reglas de validación
     */
    public $validation_messages = [
        //'required' => 'El campo :attribute es obligatorio.',
    ];

    /**
     * Atributos actualizables
     *
     * @var array
     */
    public $updatable = [
        'nombre', 'monto', 'frecuencia', 'tipo_periodo', 'max_reintentos', 'estado', 'puede_suscribir', 'prueba_frecuencia', 'prueba_tipo_periodo'
    ];

    /* --------------------------------------------------------------
     * Otros
     */

    /**
     * Cancela plan
     *
     * @return void
     */
    public function cancela()
    {
        $this->attributes['estado'] = 'inactivo';
        $this->attributes['puede_suscribir'] = false;
        $this->save();
    }

    /**
     * Regresa campos de fechas definidos en el modelo
     *
     * @return array
     */
    public function dates()
    {
        return $this->dates;
    }


}