<?php

namespace App\Classes\Pagos\Parametros;

use Jenssegers\Model\Model;
use Exception;
use Carbon\Carbon;
use App\Classes\Pagos\Parametros\Contacto;
use App\Classes\Pagos\Parametros\Direccion;

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
    //protected $casts = [];

    /*
     * Atributos mutables a fechas
     */
    protected $dates = [
        'created_at', 'updated_at', 'deleted_at',
    ];

    /*
     * Atributos de clases
     */
}