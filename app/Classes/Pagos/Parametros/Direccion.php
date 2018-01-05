<?php

namespace App\Classes\Pagos;

use Jenssegers\Model\Model;
use Exception;
use Carbon\Carbon;

/**
 * Clase para direcciones
 *
 */
class Direccion extends Model
{

    /*
     * @var array $fillable Atributos asignables
     */
    protected $fillable = [
        'pais_iso_a3', // Código de país en ISO alfabético de 3 caracteres
        'estado_iso_a3', // Código de estado en ISO alfabético de 3 caracteres
        'ciudad', // Ciudad
        'linea1', // Dirección 1: Calle y número
        'linea2', // Dirección 2: Colonia, municipio
        'linea3', // Dirección 3: Otros
        'cp', // Código postal
        'telefono', // Teléfono de/en la dirección
        'longitud', // Localización: Longitud
        'latitud', // Localización: Latitud
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


}