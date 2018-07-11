<?php

namespace App\Models;

use Webpatser\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;

class Transaccion extends Model
{
    //Nombre de la tabla
    protected $table = 'transaccion';

    //Atributos
    protected $fillable = [
        'uuid', // Id de la transacción en Claro Pagos
        'comercio_uuid', // Id del comercio
        'prueba',  // Booleano. Es prueba la transacción
        'operacion',
        'monto',
        'forma_pago',
        // Catálogos (pseudo por velocidad)
        'estatus', // ['completada', 'reembolsada', 'reembolso-parcial', 'pendiente', 'autorizada', 'cancelada', 'rechazada-banco', 'rechazada-antifraude', 'contracargo-pendiente', 'contracargo-rechazado', 'contracargada', 'fallida', 'declinada']
        'pais',
        'moneda',
        // Objetos JSON
        'datos_pago',
        'datos_comercio',
        'datos_destino',
        // Eventos JSON
        'datos_antifraude',
        'datos_procesador',
        'datos_claropagos',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id'
    ];

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
        'datos_pago' => 'array',
        'datos_comercio' => 'array',
        'datos_destino' => 'array',
        'datos_antifraude' => 'array',
        'datos_procesador' => 'array',
        'datos_claropagos' => 'array',
    ];

    /**
     * Se quita el autoincrementable
     * @var $incrementing string
     */
    public $incrementing = false;
    protected $primaryKey = 'uuid';
    protected $keyType = 'uuid';

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
}
