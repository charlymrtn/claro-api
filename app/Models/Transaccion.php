<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaccion extends Model
{
    //Nombre de la tabla
    protected $table = 'transaccions';

    //Atributos
    protected $fillable = [
        'uuid', 'comercio', 'pais_id', 'prueba', 'operacion', 'estatus', 'moneda', 'monto', 'forma_pago', 'datos_pago',
        'datos_antifraude', 'comercio_orden_id', 'datos_comercio', 'datos_claropagos', 'datos_procesador', 'datos_destino'
    ];

    /**
     * Se quita el autoincrementable
     * @var bool
     *
     */
    public $incrementing = 'false';
}
