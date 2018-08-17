<?php

namespace App\Classes\Pagos\Procesadores\Ingenico\Sub1;

use Jenssegers\Model\Model;
use Exception;
use Validator;
use Carbon\Carbon;
use App\Classes\Pagos\Base\Pedido;
use App\Classes\Pagos\Base\Contacto;
use App\Classes\Pagos\Base\PlanPago;
use App\Classes\Pagos\Medios\TarjetaCredito;
use NpsSDK\Constants;

/**
 * Clase para parámetros de entrada para cargos
 *
 */
class IngenicoConfig extends Model
{
    // {{{ properties

    /*
     * @var array $fillable Atributos asignables
     */
    protected $fillable = [
        'version', // It shows the messaging version to be used. The current version is 2.2. Default: '2.2'
        'merchant_id', // The Merchant unique identifier assigned to the merchant by the Ingenico administrator. String Alphabetic characters, any number and underscores, Min length: 1 / Max length: 14
        'tx_source', // Channel where the transaction is originated. Default: 'WEB'
        'environment', // Enum: DEVELOPMENT_ENV, STAGING_ENV, PRODUCTION_ENV
        'secret_key', // String
    ];

    /*
     * Atributos no asignables en masa
     */
    protected $guarded = [
    ];

    /*
     * Deshabilita timestamps
     */
    public $timestamps = false;

    /*
     * Atributos escondidos
     */
    protected $hidden = [
    ];

    /*
     * Atributos mutables
     */
    protected $casts = [
    ];

    /*
     * @var array $dates Atributos mutables a fechas
     */
    protected $dates = [
    ];

    /*
     * @var array $rules Reglas de validación
     */
    protected $rules = [
        'version' => 'required|string',
        'merchant_id' => 'required|string|min:1|max:14',
        'tx_source' => 'required|in:WEB',
        'environment' => 'required|in:DEVELOPMENT_ENV,STAGING_ENV,PRODUCTION_ENV',
        'secret_key' => 'required|string',
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
    public function __construct($aAttributes = [])
    {
        // Define fecha de creación
        $this->attributes['version'] = '2.2';
        $this->attributes['tx_source'] = 'WEB';
        $this->attributes['environment'] = 'DEVELOPMENT_ENV';
        // Valida entradas con los valores default incorporados
        $this->valida(array_merge($this->attributes, $aAttributes));
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
    // }}}
}