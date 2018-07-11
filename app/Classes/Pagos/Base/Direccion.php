<?php

namespace App\Classes\Pagos\Base;

use Jenssegers\Model\Model;
use Exception;
use Validator;
use Carbon\Carbon;
use App\Classes\Pagos\Base\Telefono;

/**
 * Clase para direcciones
 *
 */
class Direccion extends Model
{

    // {{{ properties

    /*
     * @var array $fillable Atributos asignables
     */
    protected $fillable = [
        'pais', // Código de país en ISO alfabético de 3 caracteres
        'estado', // Código de estado en ISO alfabético de 3 caracteres
        'ciudad', // Ciudad
        'municipio', // Municipio | Delegación
        'linea1', // Dirección 1: Calle y número
        'linea2', // Dirección 2: Colonia, municipio
        'linea3', // Dirección 3: Otros
        'cp', // Código postal
        'telefono', // Teléfono de/en la dirección
        'longitud', // Localización: Longitud
        'latitud', // Localización: Latitud
        'referencia_1', // Referencia, entre calles, etc
        'referencia_2', // Referencia, entre calles, etc
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
        'longitud' => 'float',
        'latitud' => 'float',
    ];

    /*
     * @var array $dates Atributos mutables afechas
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

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
            'pais' => 'string|size:3',
            'estado' => 'string|size:3',
            'ciudad' => 'string|max:60',
            'municipio' => 'string|max:60',
            'linea1' => 'string|max:120',
            'linea2' => 'string|max:120',
            'linea3' => 'string|max:120',
            'cp' => 'string|min:3|max:10', // Pueden ser alfanuméricos https://en.wikipedia.org/wiki/Postal_code
            'longitud' => 'numeric|min:-180|max:180',
            'latitud' => 'numeric|min:-90|max:90',
        ]);
        if ($oValidator->fails()) {
            throw new Exception($oValidator->errors(), 400);
        }
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
        // Ejecuta constructor padre
        parent::__construct($aAttributes);
        // Valida datos
        $this->validaDatos();
    }

    /**
     * Formatea datos del pais ISO alfabético de 3 caracteres
     *
     * @param  string  $sPais
     * @return void
     */
    public function setPaisAttribute($sPais)
    {
        $this->attributes['pais'] = strtoupper($sPais);
    }

    /**
     * Formatea datos del estado ISO alfabético de 3 caracteres
     *
     * @param  string  $sEstado
     * @return void
     */
    public function setEstadoAttribute($sEstado)
    {
        $this->attributes['estado'] = strtoupper($sEstado);
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

    // }}}

}