<?php

namespace App\Classes\Pagos\Procesadores;

use App;
use Config;
use Exception;
use Carbon\Carbon;

use App\Classes\Pagos\Parametros\PeticionCargo;
use App\Classes\Pagos\Procesadores\AbstractProcesador;

/**
 * Procesador de pagos para American Express
 */
class ProcesadorEjemplo extends AbstractProcesador
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
     * Envía petición de cargo
     *
     * @param  string  $sPais
     * @return void
     */
    public function carga(PeticionCargo $oPeticionCargo)
    {
        //
        dump($this->aParametros);
    }

    // }}}
}