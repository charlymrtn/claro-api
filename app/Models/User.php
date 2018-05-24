<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Classes\Pagos\Base\Afiliacion;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'activo', 'name', 'email', 'password', 'descripcion', 'comercio_uuid', 'comercio_nombre',
        'config', // Configuración de la lógica del comercio
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'config'
    ];

    /*
     * Atributos mutables
     */
    protected $casts = [
        'activo' => 'boolean',
        'config' => 'array',
    ];


    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos públicos
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ public functions

    /**
     * Regresa configuración de afiliaciones
     *
     * @return void
     */
    public function getConfig(string $sConfig = null)
    {
        $aConfig = json_decode($this->attributes['config'], true);
        if (!empty($sConfig)) {
            if (array_key_exists($sConfig, $aConfig)) {
                return $aConfig[$sConfig];
            }
        }
        return [];
    }

    /**
     * Regresa configuración de afiliaciones
     *
     * @return void
     */
    public function getAfiliaciones(string $sProcesador = null)
    {
        $aAfiliaciones = $this->getConfig('afiliaciones');
        $aoAfiliaciones = [];
        foreach($aAfiliaciones as $aAfiliacion) {
            $oAfiliacion = new Afiliacion($aAfiliacion);
            if (empty($sProcesador) || $oAfiliacion->procesador ==  $sProcesador) {
                $aoAfiliaciones[] = $oAfiliacion;
            }
        }
        return $aoAfiliaciones;
    }

    // }}}

}
