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
    ];

    /*
     * Atributos escondidos
     */
    protected $hidden = [
        'created_at', // Fecha de creación del objeto tipo Carbon
        'updated_at', // Fecha de actualización del objeto tipo Carbon
        'deleted_at', // Fecha de borrado del objeto tipo Carbon
    ];

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
            'pais' => 'sometimes|string|size:3',
            'estado' => 'sometimes|string|size:3',
            'ciudad' => 'sometimes|string|max:60',
            'municipio' => 'sometimes|string|max:60',
            'linea1' => 'sometimes|string|max:120',
            'linea2' => 'sometimes|string|max:120',
            'linea3' => 'sometimes|string|max:120',
            'cp' => 'sometimes|string|min:3|max:10', // Pueden ser alfanuméricos https://en.wikipedia.org/wiki/Postal_code
            'longitud' => 'sometimes|numeric|min:-180|max:180',
            'latitud' => 'sometimes|numeric|min:-90|max:90',
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
        $this->attributes['creacion'] = $this->attributes['created_at']->toIso8601String();
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
    public function setPaisAttribute(?string $sPais): void
    {
        if (!is_null($sPais)) {
            $this->attributes['pais'] = strtoupper($sPais);
        }
    }

    /**
     * Formatea datos del estado ISO alfabético de 3 caracteres
     *
     * @param  string  $sEstado
     * @return void
     */
    public function setEstadoAttribute(?string $sEstado): void
    {
        if (!is_null($sEstado)) {
            $this->attributes['estado'] = strtoupper($sEstado);
        }
    }

    /*
     * Mutator telefono
     *
     * @param Direccion $oDireccion Objeto Direccion
     * @return void
     */
    public function setTelefonoAttribute(?Telefono $oTelefono): void
    {
        if (!is_null($oTelefono)) {
            $this->attributes['telefono'] = $oTelefono;
        }
    }

    // }}}

}