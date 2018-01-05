<?php

namespace App\Classes\Pagos\Procesadores;

use App;
use Config;
use Exception;
use App\Classes\Pagos\Procesadores\AbstractProcesador;
use Carbon\Carbon;

use App\Classes\Pagos\Procesadores\Amex\InternetDirect;

/**
 * Procesador de pagos para American Express
 */
class ProcesadorAmex extends AbstractProcesador
{
    // {{{ properties

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
     * Constructor
     */
    public function __construct()
    {
        // Ejecuta constructor padre
        parent::__construct();
    }

    // }}}
}