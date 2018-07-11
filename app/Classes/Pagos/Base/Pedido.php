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
        'fecha_creacion', // Fecha de creación del pedido del comercio
        'total_monto', // Monto total del pedido
        'total_peso', // Peso total del pedido
        'total_articulos', // Total de artículos del pedido
        'monto_articulos', // Monto total de artículs del pedido
        'articulos', // Artículos del pedido tipo objeto PedidoArticulo
        'direccion_envio', // Dirección de envío del pedido
        'envio_empresa', //
        'envio_numero_guia', //
        'envio_monto', //
        'es_regalo', // El pedido es un regalo. Booleano
        'fecha_entrega', // Fecha de entrega estimada
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

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos públicos
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ public functions

    /**
     * Constructor
     */
    public function __construct($aAttributes)
    {
        // Define fecha de creación
        $this->attributes['creacion'] = Carbon::now();
        $this->attributes['created_at'] = Carbon::now();
        // Valida entradas
        //$this->valida($aAttributes);
        // Ejecuta constructor padre
        parent::__construct($aAttributes);
    }


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