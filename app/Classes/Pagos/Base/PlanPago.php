<?php

namespace App\Classes\Pagos\Base;

use Validator;

use Jenssegers\Model\Model;

/**
 * Clase para direcciones
 *
 */
class PlanPago extends Model
{

    // {{{ properties

    /*
     * @var array $fillable Atributos asignables
     */
    protected $fillable = [
        'plan', // Clave del plan a aplicar
        'puntos', // Cantidad del monto total pagado en puntos (Si el procesador lo soporta)
        'parcialidades', // Número de parcialidades
        'diferido', // Número de meses de diferimiento para iniciar el/los pago/s
    ];

    /**
     * Atributos de timestamp: created_at, updated_at y deleted_at
     *
     * @var bool
     */
    public $timestamps = false;

    /*
     * @var array $rules Reglas de validación
     */
    protected $rules = [
            'plan' => 'string   ',
            'puntos' => 'numeric',
            'parcialidades' => 'numeric|min:0|max:60',
            'diferido' => 'numeric|min:0|max:12',
    ];

    /*
     * Atributos no asignables en masa
     *
     * @var array
     */
    protected $guarded = [
    ];

    /*
     * Atributos escondidos
     *
     * @var array
     */
    protected $hidden = [
    ];

    /*
     * Atributos mutables
     *
     * @var array
     */
    protected $casts = [
        'parcialidades' => 'integer',
        'diferido' => 'integer',
    ];

    /*
     * Atributos mutables a fechas
     *
     * @var array
     */
    protected $dates = [
    ];

    // }}}

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos públicos
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ public functions

    /**
     * Constructor
     *
     * @param array $aAttributes
     */
    public function __construct($aAttributes)
    {
        // Valida entradas
        #$this->valida($aAttributes);
        // Ejecuta constructor padre
        parent::__construct($aAttributes);
    }

    /**
     * Valida input con las reglas de validación del modelo
     *
     * @param array $aAttributes Arreglo con valores de los campos
     *
     * @return void
     */
    public function valida($aAttributes)
    {
        $oValidator = Validator::make($aAttributes, $this->rules);
        if ($oValidator->fails()) {
            throw new Exception($oValidator->errors(), 400);
        }
    }

    /*
     * Accessor & Mutators
     */

    /*
     * Mutator Parcialidades
     *
     * @param int $iParcialidades
     * @return void
    public function setParcialidadesAttribute(int $iParcialidades): void
    {
        $this->attributes['parcialidades'] = $iParcialidades;
    }
     */

}