<?php

namespace App\Classes\Pagos\Base;

use Jenssegers\Model\Model;
use Exception;
use Carbon\Carbon;
use App\Classes\Pagos\Base\Direccion;
use App\Classes\Pagos\Base\Telefono;

/**
 * Clase para direcciones
 *
 */
class Contacto extends Model
{

    // {{{ properties

    /*
     * @var array $fillable Atributos asignables
     */
    protected $fillable = [
        'id', // Id del contacto en el sistema externo
        'nombre', // Nombre/s del contacto
        'apellido_paterno', // Apellido paterno del contacto
        'apellido_materno', // Apellido materno del contacto
        'genero', // Género del cliente
        'email', // Email del cliente
        'telefono', // Teléfono del cliente
        'nacimiento', // Fecha de nacimiento
        'creacion', // Fecha de creación del usuario en el sistema externo
        'direccion', // Dirección en donde reside el contacto (objeto Direccion)
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
        'creacion', 'nacimiento',
        'created_at', 'updated_at', 'deleted_at',
    ];

    // }}}

    /*
     * Accessor & Mutators
     */

    /*
     * Mutator direccion
     *
     * @param Direccion $oDireccion Objeto Direccion
     * @return void
     */
    public function setDireccionAttribute(Direccion $oDireccion): void
    {
        $this->attributes['direccion'] = $oDireccion;
    }

    /*
     * Mutator telefono
     *
     * @param Direccion $oDireccion Objeto Direccion
     * @return void
     */
    public function setTelefonoAttribute(Telefono $oTelefono): void
    {
        $this->attributes['telefono'] = $oTelefono;
    }

}