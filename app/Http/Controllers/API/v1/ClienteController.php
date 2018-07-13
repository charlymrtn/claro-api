<?php

namespace App\Http\Controllers\API\v1;

use App\Classes\Pagos\Base\Direccion;
use App\Classes\Pagos\Base\Telefono;
use App\Models\Cliente;
use Log;
use Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClienteController extends Controller
{
    /**
     * Cliente instance.
     *
     * @var \App\Models\Cliente
     */
    protected $mCliente;

    /**
     * ClienteController constructor.
     *
     * @param \App\Models\Cliente $cliente
     */
    public function __construct(Cliente $cliente)
    {
        $this->mCliente = $cliente;
    }

    /**
     * Obtiene una lista de recursos
     *
     * @param \Illuminate\Http\Request $oRequest
     * @return \Illuminate\Http\Response
     */
    public function index(Request $oRequest)
    {
        try {
            // Verifica las variables para despliegue de datos
            $oValidator = Validator::make($oRequest->all(), [
                'per_page' => 'numeric|between:5,100',
                'order' => 'max:30|in:uuid,comercio_uuid,nombre,apellido_paterno,apellido_materno,sexo,email,nacimiento,estado',
                'search' => 'max:100',
                'sort' => 'in:asc,desc',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail([
                    'code' => 400,
                    'type' => 'Parámetros',
                    'message' => 'Error en parámetros de entrada.',
                ], 400, ['errors' => $oValidator->errors()]);
            }
            // Filtro
            $sFiltro = $oRequest->input('search', false);
            $oCliente = $this->mCliente
                ->where(
                    function ($q) use ($sFiltro) {
                        if ($sFiltro !== false) {
                            return $q
                                ->orWhere('uuid', 'like', "%$sFiltro%")
                                ->orWhere('comercio_uuid', 'like', "%$sFiltro%")
                                ->orWhere('nombre', 'like', "%$sFiltro%")
                                ->orWhere('apellido_paterno', 'like', "%$sFiltro%")
                                ->orWhere('apellido_materno', 'like', "%$sFiltro%")
                                ->orWhere('sexo', 'like', "%$sFiltro%")
                                ->orWhere('email', 'like', "%$sFiltro%")
                                ->orWhere('nacimiento', 'like', "%$sFiltro%")
                                ->orWhere('estado', 'like', "%$sFiltro%");
                        }
                    }
                )
                ->orderBy($oRequest->input('order', 'uuid'), $oRequest->input('sort', 'desc'))
                ->paginate((int) $oRequest->input('per_page', 25));
            // Envía datos paginados
            return ejsend_success(['cliente' => $oCliente]);
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return ejsend_error([
                'code' => 500,
                'type' => 'Sistema',
                'message' => 'Error al obtener el recurso: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return ejsend_success([]);
    }

    /**
     * Guarda un recurso
     *
     * @param  \Illuminate\Http\Request $oRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $oRequest): JsonResponse
    {
        try {
            // Valida campos
            $oValidator = Validator::make($oRequest->all(), [
                'comercio_uuid' => 'required|uuid|size:36|unique:cliente',
                'id_externo' => 'max:30',
                'creacion_externa' => 'date',
                'nombre' => 'min:2|max:255',
                'apellido_paterno' => 'min:2|max:255',
                'apellido_materno' => 'min:2|max:255',
                'sexo' => 'in:masculino,femenino',
                'email' => 'required|email',
                'nacimiento' => 'date',
                'estado' => 'required|in:activo,suspendido,inactivo',
                'telefono' => 'array',
                'direccion' => 'array',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail([
                    'code' => 400,
                    'type' => 'Parámetros',
                    'message' => 'Error en parámetros de entrada.',
                ], 400, ['errors' => $oValidator->errors()]);
            }
            // Modifica valores
            $aCambios = [];
            $aCambios['telefono'] = new Telefono([
                'tipo' => $oRequest->input('telefono.tipo', 'desconocido'),
                'codigo_pais' => $oRequest->input('telefono.codigo_pais'),
                'prefijo' => $oRequest->input('telefono.prefijo'),
                'codigo_area' => $oRequest->input('telefono.codigo_area'),
                'numero' => $oRequest->input('telefono.numero'),
                'extension' => $oRequest->input('telefono.extension'),
            ]);
            $aCambios['direccion'] = new Direccion([
                'pais' => $oRequest->input('direccion.pais'),
                'estado' => $oRequest->input('direccion.estado'),
                'ciudad' => $oRequest->input('direccion.ciudad', null),
                'municipio' => $oRequest->input('direccion.municipio'),
                'linea1' => $oRequest->input('direccion.linea1'),
                'linea2' => $oRequest->input('direccion.linea2'),
                'linea3' => $oRequest->input('direccion.linea3'),
                'cp' => $oRequest->input('direccion.cp'),
                'longitud' => $oRequest->input('direccion.longitud'),
                'latitud' => $oRequest->input('direccion.latitud'),
                'referencia_1' => $oRequest->input('direccion.referencia_1'),
                'referencia_2' => $oRequest->input('direccion.referencia_2'),
            ]);
            $oRequest->merge($aCambios);
            // Crea objeto
            $oCliente = $this->mCliente->create($oRequest->all());
            // Regresa resultados
            return ejsend_success(['cliente' => $oCliente]);
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return ejsend_error([
                'code' => 500,
                'type' => 'Sistema',
                'message' => 'Error al crear el recurso: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Obtiene un recurso
     *
     * @param string  $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($uuid): JsonResponse
    {
        // Muestra el recurso solicitado
        try {
            $oValidator = Validator::make(['uuid' => $uuid], [
                'uuid' => 'required|uuid|size:36',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail([
                    'code' => 400,
                    'type' => 'Parámetros',
                    'message' => 'Error en parámetros de entrada.',
                ], 400, ['errors' => $oValidator->errors()]);
            }
            // Busca cliente
            $oCliente = $this->mCliente->where('uuid', '=', $uuid)->first();
            if ($oCliente == null) {
                Log::error('Error on '.__METHOD__.' line '.__LINE__.': Cliente no encontrado:'.$uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            }
            // Regresa cliente
            return ejsend_success(['cliente' => $oCliente]);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return ejsend_error([
                'code' => 500,
                'type' => 'Sistema',
                'message' => 'Error al obtener el recurso: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return ejsend_success([]);
    }

    /**
     * Actualiza un recurso
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @param  string  $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $oRequest, $uuid): JsonResponse
    {
        try {
            $oValidator = Validator::make(array_merge(['uuid' => $uuid], $oRequest->all()),
                [
                    'uuid' => 'required|uuid|size:36',
                    'comercio_uuid' => 'sometimes|required|uuid|size:36|unique:cliente,comercio_uuid',
                    'id_externo' => 'max:30',
                    'creacion_externa' => 'date',
                    'nombre' => 'min:2|max:255',
                    'apellido_paterno' => 'min:2|max:255',
                    'apellido_materno' => 'min:2|max:255',
                    'sexo' => 'in:masculino,femenino',
                    'email' => 'required|email',
                    'nacimiento' => 'date',
                    'estado' => 'required|in:activo,suspendido,inactivo',
                    'telefono' => 'array',
                    'direccion' => 'array',
                ]);
            if ($oValidator->fails()) {
                return ejsend_fail([
                    'code' => 400,
                    'type' => 'Parámetros',
                    'message' => 'Error en parámetros de entrada.',
                ], 400, ['errors' => $oValidator->errors()]);
            }
            // Busca cliente
            $oCliente = $this->mCliente->where('uuid', '=', $uuid)->first();
            if ($oCliente == null) {
                Log::error('Error on '.__METHOD__.' line '.__LINE__.': Cliente no encontrado:'.$uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            }
            // Modifica valores
            $aCambios = [];
            $aCambios['telefono'] = new Telefono([
                'tipo' => $oRequest->input('telefono.tipo', 'desconocido'),
                'codigo_pais' => $oRequest->input('telefono.codigo_pais'),
                'prefijo' => $oRequest->input('telefono.prefijo'),
                'codigo_area' => $oRequest->input('telefono.codigo_area'),
                'numero' => $oRequest->input('telefono.numero'),
                'extension' => $oRequest->input('telefono.extension'),
            ]);
            $aCambios['direccion'] = new Direccion([
                'pais' => $oRequest->input('direccion.pais'),
                'estado' => $oRequest->input('direccion.estado'),
                'ciudad' => $oRequest->input('direccion.ciudad', null),
                'municipio' => $oRequest->input('direccion.municipio'),
                'linea1' => $oRequest->input('direccion.linea1'),
                'linea2' => $oRequest->input('direccion.linea2'),
                'linea3' => $oRequest->input('direccion.linea3'),
                'cp' => $oRequest->input('direccion.cp'),
                'longitud' => $oRequest->input('direccion.longitud'),
                'latitud' => $oRequest->input('direccion.latitud'),
                'referencia_1' => $oRequest->input('direccion.referencia_1'),
                'referencia_2' => $oRequest->input('direccion.referencia_2'),
            ]);
            $oRequest->merge($aCambios);
            // Actualiza cliente
            $oCliente->update($oRequest->all());
            return ejsend_success(['cliente' => $oCliente]);
        } catch (\Exception $e) {
            Log::error('Error on '.__METHOD__.' line '.$e->getLine().':'.$e->getMessage());
            return ejsend_error([
                'code' => 500,
                'type' => 'Sistema',
                'message' => 'Ehe rror al actualizar el recurso: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Elimina un recurso.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($uuid): JsonResponse
    {
        try {
            // Valida uuid
            $oValidator = Validator::make(['uuid' => $uuid], [
                'uuid' => 'required|uuid|size:36',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail([
                    'code' => 400,
                    'type' => 'Parámetros',
                    'message' => 'Error en parámetros de entrada.',
                ], 400, ['errors' => $oValidator->errors()]);
            }
            // Busca cliente
            $oCliente = $this->mCliente->where('uuid', '=', $uuid)->first();
            if ($oCliente == null) {
                Log::error('Error on '.__METHOD__.' line '.__LINE__.': Cliente no encontrado:'.$uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            }
            // Borra Cliente
            $oCliente->forceDelete();
            return ejsend_success([], 204);
        } catch (\Exception $e) {
            Log::error('Error on '.__METHOD__.' line '.$e->getLine().':'.$e->getMessage());
            return ejsend_error([
                'code' => 500,
                'type' => 'Sistema',
                'message' => 'Error al borrar el recurso: '.$e->getMessage(),
            ]);
        }
    }
}