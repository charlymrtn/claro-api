<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Webpatser\Uuid\Uuid;

class Transaccion extends Model
{
    //Nombre de la tabla
    protected $table = 'transaccion';

    //Atributos
    protected $fillable = [
        'uuid', 'comercio', 'pais', 'prueba', 'operacion', 'estatus', 'moneda', 'monto', 'forma_pago', 'datos_pago',
        'datos_antifraude', 'comercio_orden_id', 'datos_comercio', 'datos_claropagos', 'datos_procesador', 'datos_destino'
    ];

    /**
     * Se quita el autoincrementable
     * @var bool
     *
     */
    public $incrementing = 'false';

    /**
     *  Setup model event hooks
     */
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Uuid::generate(4);
            }
        });
    }
}
