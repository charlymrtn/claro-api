<?php

namespace App\Classes\Pagos\Base;

use Jenssegers\Model\Model;
use Exception;
use Carbon\Carbon;

/**
 * Clase para direcciones
 *
 */
class Telefono extends Model
{

    // {{{ properties

    /*
     * @var array $fillable Atributos asignables
     */
    protected $fillable = [
        'tipo', // Tipo de teléfono. [$enum_telefono_tipos]
        'codigo_pais', //  ITU-T E.123 y E.164 (ISD)
        'codigo_area', // ITU
        'numero', // Teléfono del cliente
        'extension', // Extensión
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
        'nacimiento',
        'created_at', 'updated_at', 'deleted_at',
    ];

    /*
     * Otros atributos
     */

    /*
     * Tipos de teléfono
     *
     * @param array $enum_telefono_tipos
     */
    private $enum_telefono_tipos = [
        'No definido' => ['desconocido', 'no definido'],
        'Móvil' => ['movil'],
        'Casa' => ['casa', 'hogar'],
        'Oficina' => ['oficina', 'trabajo'],
        'Facturación' => ['facturacion', 'pago'],
        'Mensajes' => ['mensajes', 'whats'],
        'Temporal' => ['temporal', 'tmp'],
    ];

    /*
     * Accessor & Mutators
     */

    /*
     * Mutator tipo Tipo de teléfono
     *
     * @param string $tipo Tipo de teléfono
     * @return void
     */
    public function setTipoAttribute(string $tipo): void
    {
        // Si está vacío
        if (empty($tipo)) {
            $this->attributes['tipo'] = 'No definido';
        }
        // Limpia texto para validación
        $sTipoTelefono = strtr(strtolower($tipo), "áéíóúü", "aeiouu");
        // Verifica si existe en el arreglo
        foreach($this->enum_telefono_tipos as $sKey => $aVal) {
            if (in_array($sTipoTelefono, $aVal)) {
                $this->attributes['tipo'] = $sKey;
                return;
            }
        }
        // Si no está, utiliza levenshtein
        $iLev[10] = 'No definido';
        foreach($this->enum_telefono_tipos as $sKey => $aVal) {
            foreach($aVal as $sTipo) {
                $iLev[levenshtein($sTipo, $sTipoTelefono)] = $sKey;
            }
        }
        ksort($iLev);
        $this->attributes['tipo'] = array_shift($iLev);
    }

    /*
     * Accessor tipos Arreglo de tipos de teléfono
     *
     * @return array
     */
    public function getTiposAttribute(): array
    {
        return array_keys($this->enum_telefono_tipos);
    }
}