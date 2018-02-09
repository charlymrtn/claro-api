<?php

namespace App\Classes\Sistema;

use App;
use Config;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

/**
 * Validaciones de integridad del sistema
 */
class HealthCheck
{
    // {{{ properties

    /**
     * @var array $sPruebas disponibles
     */
    protected $aPruebas = [
        ['id' => 'database', 'titulo' => 'Base de datos', 'descripcion' => 'Validación de conexión y funcionamiento de base de datos.', 'metodo' => 'checkDatabase'],
        ['id' => 'model', 'titulo' => 'Modelos eloquent', 'descripcion' => 'Validación de funcionamiento de modelos eloquent.', 'metodo' => 'checkModel'],
        ['id' => 'filesystem', 'titulo' => 'Filesystem', 'descripcion' => 'Validación de filesystem.', 'metodo' => 'checkFilesystem'],
        ['id' => 'cache', 'titulo' => 'Cache', 'descripcion' => 'Validación de cache.', 'metodo' => 'checkCache'],
    ];

    /**
     * @var DB $oTestDB Base de datos para prueba de base de datos
     */
    protected $oTestDB;

    /**
     * @var object TestModel Modelo para prueba de modelos
     */
    protected $oTestModel;

    /**
     * @var object TestStorage Objeto para prueba de storage
     */
    protected $oTestStorage;

    /**
     * @var object TestCache Objeto para prueba de cache
     */
    protected $oTestCache;

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
     * Valida conexión y funcionamiento de la base de datos
     */
    public function checkDatabase(): array
    {
        // Inicializa variables
        $aResultado = ['resultado' => false, 'mensaje' => 'Prueba no terminada.'];
        // Revisa conexion
        try {
            $this->oTestDB::connection()->getPdo();
            $aResultado['resultado'] = true;
            $aResultado['mensaje'] = '';
        } catch (\Exception $e) {
            $aResultado['resultado'] = false;
            $aResultado['mensaje'] = 'Error al conectarse a la base de datos: ' . $e->getMessage();
        }
        // Resultado
        return $aResultado;
    }

    /**
     * Valida modelos eloquent
     */
    public function checkModel(): array
    {
        // Inicializa variables
        $aResultado = ['resultado' => false, 'mensaje' => 'Prueba no terminada.'];
        // Revisa
        try {
            $oRecord = $this->oTestModel->firstOrFail();
            $aResultado['resultado'] = true;
            $aResultado['mensaje'] = '';
        } catch (\Exception $e) {
            $aResultado['resultado'] = false;
            $aResultado['mensaje'] = 'Error al obtener el modelo: ' . $e->getMessage();
        }
        // Resultado
        return $aResultado;
    }

    /**
     * Valida filesystem
     */
    public function checkFilesystem(): array
    {
        // Inicializa variables
        $aResultado = ['resultado' => false, 'mensaje' => 'Prueba no terminada.'];
        // Revisa local disk
        try {
            // Crea archivo
            Storage::disk('local')->put('healthcheck.txt', 'Healthcheck Filesystem Check');
            // Verifica existencia del archivo
            if (Storage::disk('local')->exists('healthcheck.txt')) {
                // Borra el archivo
                if (Storage::disk('local')->delete('healthcheck.txt')) {
                    $aResultado['resultado'] = true;
                    $aResultado['mensaje'] = '';
                } else {
                    $aResultado['resultado'] = false;
                    $aResultado['mensaje'] = 'Error al borrar archivos en la aplicación.';
                }
            } else {
                $aResultado['resultado'] = false;
                $aResultado['mensaje'] = 'Error al crear archivos en la aplicación.';
            }
        } catch (\Exception $e) {
            $aResultado['resultado'] = false;
            $aResultado['mensaje'] = 'Error al validar el filesystem: ' . $e->getMessage();
        }
        // Revisa public disk
        try {
            // Crea archivo
            Storage::disk('public')->put('healthcheck.txt', 'Healthcheck Filesystem Check');
            // Verifica existencia del archivo
            if (Storage::disk('public')->exists('healthcheck.txt')) {
                // Borra el archivo
                if (Storage::disk('public')->delete('healthcheck.txt')) {
                    $aResultado['resultado'] = true;
                    $aResultado['mensaje'] = '';
                } else {
                    $aResultado['resultado'] = false;
                    $aResultado['mensaje'] = 'Error al borrar archivos en la aplicación.';
                }
            } else {
                $aResultado['resultado'] = false;
                $aResultado['mensaje'] = 'Error al crear archivos en la aplicación.';
            }
        } catch (\Exception $e) {
            $aResultado['resultado'] = false;
            $aResultado['mensaje'] = 'Error al validar el filesystem: ' . $e->getMessage();
        }
        // Resultado
        return $aResultado;
    }

    /**
     * Valida cache
     */
    public function checkCache(): array
    {
        // Inicializa variables
        $aResultado = ['resultado' => false, 'mensaje' => 'Prueba no terminada.'];
        // Revisa
        try {
            // Inicializa cache
            $sKeyCache = 'HealthcheckCacheTest';
            $sValorCache = 'Healthcheck Cache Check';
            // Limpia cache
            $this->oTestCache::forget($sKeyCache);
            // Crea valor en cache
            if ($this->oTestCache::add($sKeyCache, $sValorCache, 1)) {
                // Obtiene y borra valor en cache
                $sValor = $this->oTestCache::pull($sKeyCache);
                if ($sValor == $sValorCache) {
                    $aResultado['resultado'] = true;
                    $aResultado['mensaje'] = '';
                } else {
                    $aResultado['resultado'] = false;
                    $aResultado['mensaje'] = 'Error al obtener valor del cache.';
                }
            } else {
                $aResultado['resultado'] = false;
                $aResultado['mensaje'] = 'Error al crear valores en el cache.';
            }
        } catch (\Exception $e) {
            $aResultado['resultado'] = false;
            $aResultado['mensaje'] = 'Error al validar el cache: ' . $e->getMessage();
        }
        // Resultado
        return $aResultado;
    }

    // }}}

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos públicos
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ public functions

    /**
     * Constructor
     */
    public function __construct(DB $oTestDB, User $oTestModel, Storage $oTestStorage, Cache $oTestCache)
    {
        // Asigna objetos
        $this->oTestDB = $oTestDB;
        $this->oTestModel = $oTestModel;
        $this->oTestStorage = $oTestStorage;
        $this->oTestCache = $oTestCache;
    }

    /**
     * Regresa las pruebas disponibles
     *
     * @return array Arreglo de las pruebas disponibles con datos principales
     */
    public function getPruebasDisponibles(): array
    {
        return $this->aPruebas;
    }

    // }}}
}