<?php

namespace App\Classes\Pagos\Procesadores;

/**
 * Interfaz de procesador de pagos.
 * Definición de funciones mínimas que deben ser definidas en los procesadores de pago.
 */
interface InterfaceProcesador
{
    /**
     * Obtiene el nombre comercial del procesador de pagos.
     *
     * @return string Nombre comercial del procesador de pagos.
     */
    public function getNombre(): string;

    /**
     * Obtiene el identificador del procesador de pagos.
     *
     * @return string Identificador del procesador de pagos.
     */
    public function getId(): string;

    /**
     * Obtiene parámetros del procesador de pagos.
     *
     * @return array Arreglo con parámetros definidos.
     */
    public function getParametros(): array;

    /**
     * Define parámetros del procesador de pagos.
     *
     * @param array $aParametros Arreglo de parámetros.
     *
     * @return array Arreglo con parámetros definidos.
     */
    public function setParametros(array $aParametros = []): array;

    /**
     * Obtiene parámetro solicitado del procesador de pagos.
     *
     * @param string $sNombre Nombre del parámetro a obtener.
     *
     * @return mixed Regresa valor del parámetro.
     */
    public function getParametro(string $sNombre);

    /**
     * Define parámetro solicitado del procesador de pagos.
     *
     * @param array $aParametros Arreglo de parámetros.
     *
     * @return mixed Regresa valor del parámetro.
     */
    public function setParametro(string $sNombre, $mValor);


    /**
     * Regresa si el procesador de pagos puede realizar cargos.
     *
     * @return bool Puede realizar cargos?
     */
    public function puedeCargar(): bool;

    /**
     * Regresa si el procesador de pagos puede realizar autorizaciones sin cargo directo.
     *
     * @return bool Puede preautorizar cargos?
     */
    public function puedePreautorizar(): bool;

    /**
     * Regresa si el procesador de pagos puede realizar autorizaciones sin cargo directo.
     *
     * @return bool Puede confirmar un cargo preautorizado?
     */
    public function puedeConfirmar(): bool;

    /**
     * Regresa si el procesador de pagos puede realizar cancelaciones.
     *
     * @return bool Puede cancelar cargos?
     */
    public function puedeCancelar(): bool;

    /**
     * Regresa si el procesador de pagos puede realizar devoluciones.
     *
     * @return bool Puede devolver cargos?
     */
    public function puedeDevolver(): bool;

}