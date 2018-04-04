<?php

namespace App\Classes\Pagos\Parametros;

use Jenssegers\Model\Model;
use Exception;
use Validator;
use Carbon\Carbon;
use Webpatser\Uuid\Uuid;
use App\Classes\Pagos\Base\Pedido;
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
        'prueba', // Booleano que indica si es una transacción de prueba o no
        'tarjeta', // Objeto tipo TarjetaCredito
        'monto', // Monto total de la transacción
        'puntos', // Cantidad del monto total pagado en puntos (Si el procesador lo soporta)
        'descripcion',
        'pedido', // Objeto tipo Pedido
        'cliente', // Objeto tipo Contacto
        'parcialidades',
        'diferido', // Número de meses de diferimiento del pago
        'plan', // Tipo de plan de pagos
        'direccion_cargo', // Objeto tipo Direccion
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
     * @var array $dates Atributos mutables a fechas
     */
    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    /*
     * @var array $rules Reglas de validación
     */
    protected $rules = [
            'prueba' => 'boolean',
            'tarjeta' => 'required',
            'monto' => 'required',
            'puntos' => 'numeric',
            'descripcion' => 'max:250',
            'parcialidades' => 'numeric|min:0|max:60',
            'diferido' => 'numeric|min:0|max:12',
            'comercio_uuid' => 'required|string',
    ];

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
        $this->attributes['id'] = Uuid::generate(4)->string;
        $this->attributes['created_at'] = Carbon::now();
        $this->attributes['prueba'] = true;
        // Valida entradas
        $this->valida($aAttributes);
        // Ejecuta constructor padre
        parent::__construct($aAttributes);
    }

    /**
     * Valida input con las reglas de validación del modelo
     *
     * @param array $aAttributes Arreglo con valores de los campos
     *
     * @return void
     */
    public function valida($aAttributes)
    {
        $oValidator = Validator::make($aAttributes, $this->rules);
        if ($oValidator->fails()) {
            throw new Exception($oValidator->errors(), 400);
        }
    }

    // --------------------------------------------------------------------------------------------------------

    /**
     * Mutator a objeto TarjetaCredito
     *
     * @param TarjetaCredito $oTarjeta
     * @return void
     */
    public function setTarjetaAttribute(TarjetaCredito $oTarjeta)
    {
        $this->attributes['tarjeta'] = $oTarjeta;
    }

    /**
     * Mutator a objeto Contacto
     *
     * @param Contacto $oCliente
     * @return void
     */
    public function setClienteAttribute(Contacto $oCliente)
    {
        $this->attributes['cliente'] = $oCliente;
    }

    /**
     * Mutator a objeto Pedido
     *
     * @param Pedido $oPedido
     * @return void
     */
    public function setPedidoAttribute(Pedido $oPedido)
    {
        $this->attributes['pedido'] = $oPedido;
    }

    // }}}
}