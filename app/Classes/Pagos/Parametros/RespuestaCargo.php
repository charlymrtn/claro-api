<?php

namespace App\Classes\Pagos\Parametros;

use Jenssegers\Model\Model;
use Exception;
use Validator;
use Carbon\Carbon;
use App\Classes\Pagos\Parametros\PeticionCargo;

/**
 * Clase para parámetros de entrada para cargos
 *
 */
class RespuestaCargo extends Model
{
    // {{{ properties

    /*
     * @var array $fillable Atributos asignables
     */
    protected $fillable = [
        'id', // ID de la transacción en Claro Pagos
        'prueba', // Booleano que indica si es una transacción de prueba o no
        'monto', // Numérico (mayor a cero con dos decimales). Monto de la transacción
        'autorizacion_id', // String. Identificador de autorización del procesador de pago
        'tipo', // String. Tipo de transacción (“cargo”)
        'orden_id', // String. Identificador del orden del comercio
        'cliente_id', // String. Identificador de cliente del comercio
        'estatus', // String. Estatus de la transacción
        'error', // Objeto. Objeto de tipo Error
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

    // --------------------------------------------------------------------------------------------------------

}