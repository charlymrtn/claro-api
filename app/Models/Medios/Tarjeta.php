<?php

namespace App\Models\Medios;

use Webpatser\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Classes\Pagos\Base\Direccion;
use App\Classes\Pagos\Base\Telefono;

class Tarjeta extends Model
{
    // Traits
    use Notifiable, SoftDeletes;

    // Nombre de la tabla
    protected $table = 'medio_tarjeta';

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
        'uuid', // UUID de la tarjeta en Claro Pagos
        'comercio_uuid', // UUID del comercio
        'cliente_uuid', // UUID del cliente
        // Datos de la tarjeta
        'iin', // Issuer Identification Number (bin)
        'marca', // Marca de la tarjeta: visa, mastercard, carnet, amex (american express).
        'pan', // Número de tarjeta sanitizado
        'terminacion', // Terminación o últimos cuatro dígitos
        'nombre', // Nombre como aparece en la tarjeta
        'expiracion_mes', // Mes de expiración
        'expiracion_anio', // Año de expiracion
        'inicio_mes', // Mes de inicio de la tarjeta
        'inicio_anio', // Anio de inicio
        // Otros datos
        'pan_hash', // Hash de la tarjeta
        'token', // Token dado por la bóveda
        'default', // Booleano si es el método de pago por default
        'cargo_unico', // Booleano que indica si la tarjeta sólo se puede usar una vez
        // Objetos JSON
        'direccion', // Objeto de tipo Dirección
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['comercio_uuid', 'pan_hash', 'token'];

    /**
     * Atributos mutables a fechas.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    /**
     * Atributos mutables automáticamente.
     *
     * @var array
     */
    protected $casts = [
        'default' => 'boolean',
        'cargo_unico' => 'boolean',
    ];

    /* --------------------------------------------------------------
     * Reglas de validación
     * @var array $rules Reglas de validación
     */
    public $rules = [
        'pan' => 'required',
        'nombre' => 'required_without:nombres|min:3|max:60',
        'expiracion_mes' => 'required|numeric',
        'expiracion_anio' => 'required|numeric',
        'inicio_mes' => 'numeric',
        'inicio_anio' => 'numeric',
        'default' => 'boolean',
        'cliente_uuid' => 'sometimes|uuid|size:36|exists:cliente,uuid',
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
     * Dueño de la tarjeta
     */
    public function cliente()
    {
        return $this->belongsTo('App\Models\Cliente', 'cliente_uuid', 'uuid');
    }


    /* --------------------------------------------------------------
     * Accessor & Mutators
     */

    /*
     * Mutator direccion set
     *
     * @param Direccion $oDireccion Objeto Direccion
     * @return void
     */
    public function setDireccionAttribute(Direccion $oDireccion): void
    {
        $this->attributes['direccion'] = $oDireccion;
    }

    /*
     * Mutator direccion get
     *
     * @param string $sValue Arreglo
     * @return Direccion
     */
    public function getDireccionAttribute(?string $jDireccion): ?Direccion
    {
        // Valida null
        if ($jDireccion == null) {
            return null;
        }
        // Convierte dato en arreglo
        $aDireccion = json_decode($jDireccion, true);
        // Convierte objeto Telefono
        if(!empty($aDireccion['telefono'])) {
            $oTelefono = new Telefono($aDireccion['telefono']);
            unset($aDireccion['telefono']);
        }
        // Convierte datos en objeto
        $oDireccion = new Direccion($aDireccion);
        // Agrega objetos
        if (isset($oTelefono)) {
            $oDireccion->telefono = $oTelefono;
        }
        // Regresa objeto completo
        return $oDireccion;
    }
}