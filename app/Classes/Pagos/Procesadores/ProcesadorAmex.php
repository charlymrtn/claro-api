<?php

namespace App\Classes\Pagos\Procesadores;

use App;
use Config;
use Exception;
use Carbon\Carbon;

use App\Classes\Pagos\Parametros\PeticionCargo;
use App\Classes\Pagos\Procesadores\AbstractProcesador;
use App\Classes\Pagos\Procesadores\Amex\InternetDirect;

/**
 * Procesador de pagos para American Express
 */
class ProcesadorAmex extends AbstractProcesador
{
    // {{{ properties

    /**
     * Dependency injection
     *
     * @var InternetDirect Objeto InternetDirects
     */
    protected $oInternetDirect;


    /**
     * @var PeticionCargo Objeto PeticionCargo
     */
    protected $oPeticionCargo;

    /**
     * @var array Parámetros
     */
    protected $aParametros = [
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

    /**
     * Inicializa procesador de pagos.
     */
    private function inicializa(): void
    {
        // Define parámetros default de la configuración Claro Pagos
        $aParametros = [
            'api_url' => Config::get('claropagos.' . App::environment() . '.procesadores_pago.amex.api_url'),
            'origin' => Config::get('claropagos.' . App::environment() . '.procesadores_pago.amex.origin'),
        ];
        // Reemplaza y agrega parámetros del procesador de pagos
        $this->setParametros($aParametros);
        // Inicializa configuración del procesador de pagos

    }

    // }}}

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos públicos
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ public functions

    /**
     * Obtiene el nombre comercial del procesador de pagos.
     *
     * @return string Nombre comercial del procesador de pagos.
     */
    public function getNombre(): string
    {
        return 'American Express';
    }

    /**
     * Obtiene el identificador del procesador de pagos.
     *
     * @return string Identificador del procesador de pagos.
     */
    public function getId(): string
    {
        return 'amex';
    }

    /**
     * Obtiene parámetros del procesador de pagos.
     *
     * @return array Arreglo con parámetros definidos.
     */
    public function getParametros(): array
    {
        return [
            'api_url' => [
                'id' => 'api_url',
                'label' => 'API URL',
                'description' => 'URL del API proporcionado por AMEX para la afiliación bancaria.',
                'placeholder' => 'http://americanexpress.com/abc.do/',
                'datatype' => 'string',
                'type' => 'url',
                'validator' => 'required|url',
                'minlength' => 5,
                'maxlength' => 255,
                'order' => 1,
            ],
            'origin' => [
                'id' => 'origin',
                'label' => 'Amex Origin',
                'description' => 'Campo Origin proporcionado por Amex.',
                'placeholder' => 'AMERICAMOVIL-XXXXX',
                'datatype' => 'string',
                'type' => 'alpha_dash',
                'validator' => 'required|alpha_dash',
                'minlength' => 5,
                'maxlength' => 25,
            ],
            'country' => [
                'id' => 'country',
                'label' => 'Código de país',
                'description' => 'Código de país ISO 3166-1.',
                'placeholder' => '000',
                'datatype' => 'integer',
                'type' => 'numeric',
                'validator' => 'required|numeric',
                'minlength' => 3,
                'maxlength' => 3,
                'default' => 484,
            ],
            'region' => [
                'id' => 'region',
                'label' => 'Región',
                'description' => 'Región interna Amex',
                'placeholder' => 'AAA',
                'datatype' => 'integer',
                'type' => 'alpha',
                'validator' => 'required|alpha',
                'minlength' => 3,
                'maxlength' => 3,
                'default' => 'LAC',
            ],
            'rtind' => [
                'id' => 'rtind',
                'label' => 'Return Indicator',
                'description' => 'Código de retorno interno Amex',
                'placeholder' => '000',
                'datatype' => 'integer',
                'type' => 'numeric',
                'validator' => 'required|numeric',
                'minlength' => 3,
                'maxlength' => 3,
            ],
        ];
    }

    /**
     * Envía petición de cargo
     *
     * @param  string  $sPais
     * @return void
     */
    public function carga(PeticionCargo $oPeticionCargo)
    {
        //
        dump($oPeticionCargo->toArray());
    }

    // }}}
}