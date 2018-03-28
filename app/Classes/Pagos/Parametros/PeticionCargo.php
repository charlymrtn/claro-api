<?php

namespace App\Classes\Pagos\Parametros;

use Jenssegers\Model\Model;
use Exception;
use Validator;
use Carbon\Carbon;
use App\Classes\Pagos\Base\Contacto;
use App\Classes\Pagos\Base\Direccion;
use App\Classes\Pagos\Base\Telefono;
use App\Classes\Pagos\Medios\TarjetaCredito;

/**
 * Clase para parámetros de entrada para cargos
 *
 */
class PeticionCargo extends Model
{

    // {{{ properties

    /*
     * @var array $fillable Atributos asignables
     */
    protected $fillable = [
        'id', // Id de la transacción en Claro Pagos
        'prueba',
        'tarjeta',
        'monto',
        'descripcion',
        'pedido',
        'cliente',
        'parcialidades',
        'comercio_uuid',
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
    protected $casts = [
        'parcialidades' => 'integer',
        'prueba' => 'boolean',
    ];

    /*
     * Atributos mutables a fechas
     */
    protected $dates = [
        'created_at', 'updated_at', 'deleted_at',
    ];

    /*
     * Atributos de clases
     */
    // }}}}

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos protegidos
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ protected functions

    // }}}

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos privados
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ private functions

    private function validaDatos()
    {
         // Valida datos de entrada
        $oValidator = Validator::make($this->toArray(), [
            'prueba' => 'boolean',
            'tarjeta' => 'required',
            'monto' => 'required',
            'descripcion' => 'max:250',
            'pedido' => 'required|array',
                'pedido.id' => 'max:48',
                'pedido.direccion_envio' => 'array',
                'pedido.direccion_cargo' => 'required|array',
                'pedido.articulos' => 'numeric',
            'cliente' => 'required|array',
                'cliente.id' => 'required|string',
                'cliente.nombre' => 'required|min:3|max:30',
                'cliente.apellido_paterno' => 'required|min:3|max:30',
                'cliente.apellido_materno' => 'min:3|max:30',
                'cliente.email' => 'required|email',
                'cliente.telefono' => 'string',
                'cliente.direccion' => 'array',
                'cliente.creacion' => 'date',
            'parcialidades' => 'numeric|min:0|max:48',
            'comercio_uuid' => 'required|string',
        ]);
        if ($oValidator->fails()) {
            throw new Exception($oValidator->errors(), 400);
        }
    }

    /**
     * Formatea datos de entrada
     *
     * @return void
     */
    private function formateaDatos()
    {
        $this->attributes['pedido']['direccion_envio'] = new Direccion($this->attributes['pedido']['direccion_envio']);
        $this->attributes['pedido']['direccion_cargo'] = new Direccion($this->attributes['pedido']['direccion_cargo']);
        $this->attributes['cliente']['direccion'] = new Direccion($this->attributes['cliente']['direccion']);
        $this->attributes['cliente']['telefono'] = new Telefono(['numero' => $this->attributes['cliente']['telefono']]);
        $this->attributes['cliente'] = new Contacto($this->attributes['cliente']);
    }

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
        $this->attributes['created_at'] = Carbon::now();
        $this->attributes['prueba'] = true;
        // Ejecuta constructor padre
        parent::__construct($aAttributes);
        // Valida datos
        $this->validaDatos();
        // Formatea datos
        $this->formateaDatos();
    }

    /**
     * Convierte datos de tarjeta a objeto TarjetaCredito
     * (Validaciones de datos en el objeto)
     *
     * @param  string  $aTarjeta
     * @return void
     */
    public function setTarjetaAttribute($aTarjeta)
    {
        $this->attributes['tarjeta'] = new TarjetaCredito($aTarjeta);
    }

    // }}}
}