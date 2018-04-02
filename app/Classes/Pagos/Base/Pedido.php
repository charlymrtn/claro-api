<?php

namespace App\Classes\Pagos\Base;

use Jenssegers\Model\Model;
use Exception;
use Carbon\Carbon;
use App\Classes\Pagos\Base\Direccion;

/**
 * Clase para direcciones
 *
 */
class Pedido extends Model
{

    // {{{ properties

    /*
     * @var array $fillable Atributos asignables
     */
    protected $fillable = [
        'id', // Id del pedido
        'creacion', // Fecha de creación del pedido
        'envio', // Fecha de envío del pedido
        'total', // Monto total del pedido
        'direccion_envio', // Dirección de envío del pedido
        'peso', // Peso total del pedido
        'articulos', // Artículos del pedido tipo objeto PedidoArticulo
    ];

    /*
     * Atributos no asignables en masa
     */
    protected $guarded = [
        'created_at', // Fecha de creación del objeto tipo Carbon
        'updated_at', // Fecha de actualización del objeto tipo Carbon
    ];

    /*
     * Atributos escondidos
     */
    //protected $hidden = [];

    /*
     * Atributos mutables
     */
    //protected $casts = [];

    /*
     * Atributos mutables a fechas
     */
    protected $dates = [
        'creacion', 'envio',
        'created_at', 'updated_at', 'deleted_at',
    ];

    // }}}

    /*
     * Accessor & Mutators
     */

    /*
     * Mutator direccion
     *
     * @param Direccion $oDireccion Objeto Direccion
     * @return void
     */
    public function setDireccionEnvioAttribute(Direccion $oDireccion): void
    {
        $this->attributes['direccion'] = $oDireccion;
    }

}