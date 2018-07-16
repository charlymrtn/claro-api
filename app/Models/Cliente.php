<?php

namespace App\Models;

use App\Classes\Pagos\Base\Direccion;
use App\Classes\Pagos\Base\Telefono;
use Webpatser\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    // Traits
    use Notifiable, SoftDeletes;

    // Nombre de la tabla
    protected $table = 'cliente';

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
        'uuid', // UUID del cliente en Claro Pagos
        // Datos del comercio
        'comercio_uuid', // UUID del comercio
        'id_externo', // ID del cliente en el comercio
        'creacion_externa', // Fecha y hora de creación del usuario en el comercio
        // Datos del cliente
        'nombre', // Nombre o nombres del cliente
        'apellido_paterno', // Apellido paterno del cliente
        'apellido_materno', // Apellido materno del cliente
        'sexo', // ['masculino', 'femenino']
        'email', // Email del cliente
        'nacimiento', // Fecha de nacimiento
        'estado', // ['activo', 'suspendido', 'inactivo']
        // Objetos JSON
        'telefono', // Objeto tipo Teléfono
        'direccion', // Objeto de tipo Dirección
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
    protected $dates = ['deleted_at', 'created_at', 'updated_at', 'creacion_externa'];

    /**
     * Atributos mutables.
     *
     * @var array
     */
    protected $casts = [
        'nacimiento' => 'date',
        'telefono' => 'array',
        'direccion' => 'array',
    ];

    /**
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
     * Tarjetas de pago
     */
    public function tarjetas()
    {
        return $this->hasMany('App\Models\Medios\Tarjeta', 'cliente_uuid', 'uuid');
    }

    /**
     * Suscripciones
     */
    public function suscripciones()
    {
        return $this->hasMany('App\Models\Suscripciones\Suscripcion', 'cliente_uuid', 'uuid');
    }

    /* --------------------------------------------------------------
     * Accessor & Mutators
     */

    /*
     * Mutator direccion
     *
     * @param Direccion $oDireccion Objeto Direccion
     * @return void
     */
    public function setDireccionAttribute(Direccion $oDireccion): void
    {
        $this->attributes['direccion'] = $oDireccion;
    }

    /*
     * Mutator telefono
     *
     * @param Direccion $oDireccion Objeto Direccion
     * @return void
     */
    public function setTelefonoAttribute(Telefono $oTelefono): void
    {
        $this->attributes['telefono'] = $oTelefono;
    }


}