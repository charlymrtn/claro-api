<?php

namespace App\Http\Controllers\API\v1;

use Log;
use Auth;
use Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webpatser\Uuid\Uuid;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Classes\Pagos\Base\Direccion;
use App\Classes\Pagos\Base\Telefono;

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
            $cClientes = $this->mCliente
                //->where('comercio_uuid', $oUser->comercio_uuid)
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
                ->orderBy($oRequest->input('order', 'created_at'), $oRequest->input('sort', 'desc'))
                ->paginate((int) $oRequest->input('per_page', 25));
            // Envía datos paginados
            return ejsend_success(['clientes' => $cClientes]);
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
     * Guarda un recurso
     *
     * @param  \Illuminate\Http\Request $oRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $oRequest): JsonResponse
    {
        try {
            // Obtiene comercio_uuid del token del usuario de la petición
            $sComercioUuid = $oRequest->user()->comercio_uuid;
            // Define valores por default antes de validación
            $sRequestEmail = $oRequest->input('email');
            $oRequest->merge([
                'uuid' => Uuid::generate(4)->string,
                'comercio_uuid' => $sComercioUuid,
                'estado' => $oRequest->input('estado', 'activo'),
                'id_externo' => $oRequest->input('id_externo', $sRequestEmail),
            ]);
            // Valida campos
            $oValidator = Validator::make($oRequest->all(), $this->mCliente->rules);
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
                'codigo_pais' => $oRequest->input('telefono.codigo_pais', null),
                'prefijo' => $oRequest->input('telefono.prefijo'),
                'codigo_area' => $oRequest->input('telefono.codigo_area'),
                'numero' => $oRequest->input('telefono.numero'),
                'extension' => $oRequest->input('telefono.extension'),
            ]);
            $aCambios['direccion'] = new Direccion([
                'pais' => $oRequest->input('direccion.pais', 'MEX'),
                'estado' => $oRequest->input('direccion.estado', 'CMX'),
                'ciudad' => $oRequest->input('direccion.ciudad', 'CDMX'),
                'municipio' => $oRequest->input('direccion.municipio', ''),
                'linea1' => $oRequest->input('direccion.linea1', ''),
                'linea2' => $oRequest->input('direccion.linea2', ''),
                'linea3' => $oRequest->input('direccion.linea3', ''),
                'cp' => $oRequest->input('direccion.cp', ''),
                'longitud' => $oRequest->input('direccion.longitud', 0),
                'latitud' => $oRequest->input('direccion.latitud', 0),
                'referencia_1' => $oRequest->input('direccion.referencia_1', ''),
                'referencia_2' => $oRequest->input('direccion.referencia_2', ''),
            ]);
            $oRequest->merge($aCambios);
            // Valida cliente creado anteriormente con mismo email
            $oCliente = $this->mCliente
                ->where('comercio_uuid', '=', $sComercioUuid)
                ->where('email', '=', $sRequestEmail)
                ->first();
            if (!empty($oCliente)) {
                return ejsend_fail([
                    'code' => 409,
                    'type' => 'Parámetros',
                    'message' => 'Error al crear el recurso: Cliente existente con el mismo email (' . $oCliente->uuid . ')',
                ]);
            }
            // Valida cliente creado anteriormente con mismo id_externo
            $oCliente = $this->mCliente
                ->where('comercio_uuid', '=', $sComercioUuid)
                ->where('id_externo', '=', $oRequest->input('id_externo'))
                ->first();
            if (!empty($oClienteExistente)) {
                return ejsend_fail([
                    'code' => 409,
                    'type' => 'Parámetros',
                    'message' => 'Error al crear el recurso: Cliente existente con el mismo id_externo (' . $oCliente->uuid . ')',
                ]);
            }
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
            // Obtiene comercio_uuid del usuario de la petición
            $sComercioUuid = Auth::user()->comercio_uuid;
            // Valida request
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
            $oCliente = $this->mCliente->where('comercio_uuid', '=', $sComercioUuid)->find($uuid);
            if ($oCliente == null) {
                Log::error('Error on '.__METHOD__.' line '.__LINE__.': Cliente no encontrado:'.$uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Cliente no encontrado.'], 404);
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
     * Actualiza un recurso
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @param  string  $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $oRequest, $uuid): JsonResponse
    {
        try {
            // Obtiene comercio_uuid del token del usuario de la petición
            $sComercioUuid = $oRequest->user()->comercio_uuid;
            // Define valores por default antes de validación
            $oRequest->merge([
                'uuid'=> $uuid,
                'comercio_uuid'=> $sComercioUuid,
            ]);
            // Valida datos
            $oValidator = Validator::make($oRequest->all(), array_merge($this->mCliente->rules, [
                'uuid' => 'required|uuid|size:36',
                'comercio_uuid' => 'required|uuid|size:36',
                'email' => 'email',
            ]));
            if ($oValidator->fails()) {
                return ejsend_fail([
                    'code' => 400,
                    'type' => 'Parámetros',
                    'message' => 'Error en parámetros de entrada.',
                ], 400, ['errors' => $oValidator->errors()]);
            }
            // Busca cliente
            $oCliente = $this->mCliente->where('comercio_uuid', '=', $sComercioUuid)->find($uuid);
            if ($oCliente == null) {
                Log::error('Error on '.__METHOD__.' line '.__LINE__.': Cliente no encontrado:'.$uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            }
            // Modifica valores
            $aCambios = [];
            $aCambios['telefono'] = new Telefono([
                'tipo' => $oRequest->input('telefono.tipo', 'desconocido'),
                'codigo_pais' => $oRequest->input('telefono.codigo_pais', null),
                'prefijo' => $oRequest->input('telefono.prefijo'),
                'codigo_area' => $oRequest->input('telefono.codigo_area'),
                'numero' => $oRequest->input('telefono.numero'),
                'extension' => $oRequest->input('telefono.extension'),
            ]);
            $aCambios['direccion'] = new Direccion([
                'pais' => $oRequest->input('direccion.pais', 'MEX'),
                'estado' => $oRequest->input('direccion.estado', 'CMX'),
                'ciudad' => $oRequest->input('direccion.ciudad', 'CDMX'),
                'municipio' => $oRequest->input('direccion.municipio', ''),
                'linea1' => $oRequest->input('direccion.linea1', ''),
                'linea2' => $oRequest->input('direccion.linea2', ''),
                'linea3' => $oRequest->input('direccion.linea3', ''),
                'cp' => $oRequest->input('direccion.cp', ''),
                'longitud' => $oRequest->input('direccion.longitud', 0),
                'latitud' => $oRequest->input('direccion.latitud', 0),
                'referencia_1' => $oRequest->input('direccion.referencia_1', ''),
                'referencia_2' => $oRequest->input('direccion.referencia_2', ''),
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