<?php

namespace App\Classes\Pagos\Base;

use Jenssegers\Model\Model;
use Exception;
use Carbon\Carbon;

/**
 * Clase para direcciones
 *
 */
class Error extends Model
{

    // {{{ properties

    /*
     * @var array $fillable Atributos asignables
     */
    protected $fillable = [
        'codigo', // Código de error Claro Pagos
        'tipo', // Categoría del error
        'descripcion', // Identificador de banco
        'http_code', // Código HTTP
    ];

    /*
     * Atributos no asignables en masa
     */
    protected $guarded = [
        'created_at', 'updated_at', 'deleted_at'
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
     * @var array $dates Atributos mutables a fechas
     */
    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    /*
     * @var array $rules Reglas de validación
     */
    protected $rules = [
    ];

    // }}}

    /*
     * Accessor & Mutators
     */


}