<?php

namespace App\Classes\Pagos\Procesadores;

use Jenssegers\Model\Model;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Description of ProcesadorAbstract
 *
 * @author ahfer
 */
abstract class AbstractProcesador implements InterfaceProcesador
{

    // {{{ properties

    /**
     * @var array Parámetros
     */
    protected $aParametros = [];

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
        $aParametros = [];
        // Reemplaza y agrega parámetros del procesador de pagos
        $this->setParametros($aParametros);
    }

    // }}}

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos públicos
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ public functions

    /**
     * Define parámetros del procesador de pagos.
     *
     * @param array $aParametros Arreglo de parámetros.
     *
     * @return array Arreglo con parámetros definidos.
     */
    public function setParametros(array $aParametros = []): array
    {
        // Reemplaza y agrega parámetros del procesador de pagos
        $this->aParametros = array_merge($this->aParametros, $aParametros);
        // Regresa los parámetros
        return $this->getParametros();
    }

    /**
     * Obtiene los parámetros del procesador de pagos.
     *
     * @return array Arreglo con parámetros definidos.
     */
    public function getParametros(): array
    {
        return $this->aParametros;
    }

    /**
     * Define parámetro solicitado del procesador de pagos.
     *
     * @param array $aParametros Arreglo de parámetros.
     *
     * @return mixed Regresa valor del parámetro.
     */
    public function setParametro(string $sNombre, $mValor)
    {
        // Define parámetro del procesador de pagos
        $this->aParametros[$sNombre] = $mValor;
        // Regresa los parámetros
        return $this->getParametro($sNombre);
    }

    /**
     * Obtiene parámetro solicitado del procesador de pagos.
     *
     * @param string $sNombre Nombre del parámetro a obtener.
     *
     * @return mixed Regresa valor del parámetro.
     */
    public function getParametro(string $sNombre)
    {
        return $this->aParametros[$sNombre] ?? null;
    }


    /**
     * Regresa si el procesador de pagos puede realizar cargos.
     *
     * @return bool Puede realizar cargos?
     */
    public function puedeCargar(): bool
    {
        return method_exists($this, 'carga');
    }

    /**
     * Regresa si el procesador de pagos puede realizar preautorizaciones sin cargo directo.
     *
     * @return bool Puede preautorizar cargos?
     */
    public function puedePreautorizar(): bool
    {
        return method_exists($this, 'preautoriza');
    }


    /**
     * Regresa si el procesador de pagos puede realizar cancelaciones.
     *
     * @return bool Puede cancelar cargos?
     */
    public function puedeCancelar(): bool
    {
        return method_exists($this, 'cancela');
    }

    /**
     * Regresa si el procesador de pagos puede realizar devoluciones.
     *
     * @return bool Puede devolver cargos?
     */
    public function puedeDevolver(): bool
    {
        return method_exists($this, 'devuelve');
    }

    // }}}
}