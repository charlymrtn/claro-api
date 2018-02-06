<?php

namespace App\Http\Controllers\API\Admin;

use Log;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\User;

class UsuarioController extends Controller
{
    protected $mUsuario;

    public function __construct(User $usuario)
    {
        $this->mUsuario = $usuario;
    }

    /**
     * Regresa lista de usuarios.
     *
     * @param \Illuminate\Http\Request $oRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $oRequest): JsonResponse
    {
        // Regresa todos los usuarios paginados
        try {
            // Verifica las variables para despliegue de datos
            $oValidator = Validator::make($oRequest->all(), [
                'comercio_uuid' => 'uuid|size:36',
                // Datos de la paginación y filtros
                'per_page' => 'numeric|between:5,100',
                'order' => 'max:30|in:id,name,email,activo,comercio_uuid,comercio_nombre,created_at,updated_at,deleted_at',
                'search' => 'max:100',
                'deleted' => 'in:no,yes,only',
                'sort' => 'in:asc,desc',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Filtros
            $sFiltro = $oRequest->input('search', false);
            $sDeleted = $oRequest->input('deleted', 'no');
            $sComercio = $oRequest->input('comercio_uuid', false);
            $cUsuarios = $this->mUsuario
                ->withTrashed()
                ->where(
                    function ($q) use ($sComercio) {
                        if (!empty($sComercio)) {
                            return $q
                                ->orWhere('comercio_uuid', '=', $sComercio);
                        }
                    }
                )
                ->where(
                    function ($q) use ($sFiltro) {
                        if ($sFiltro !== false) {
                            return $q
                                ->orWhere('name', 'like', "%$sFiltro%")
                                ->orWhere('email', 'like', "%$sFiltro%")
                                ->orWhere('comercio_uuid', 'like', "%$sFiltro%")
                                ->orWhere('comercio_nombre', 'like', "%$sFiltro%");
                        }
                    }
                )
                ->where(
                    function ($q) use ($sDeleted) {
                        if ($sDeleted == 'no') {
                            return $q->whereNull('deleted_at');
                        } elseif ($sDeleted == 'yes') {
                            return $q;
                        } elseif ($sDeleted == 'only') {
                            return $q->whereNotNull('deleted_at');
                        }
                    }
                )
                ->orderBy($oRequest->input('order', 'id'), $oRequest->input('sort', 'asc'))
                ->paginate((int) $oRequest->input('per_page', 25));

            // Envía datos paginados
            return ejsend_success(['usuarios' => $cUsuarios]);
        } catch (\Exception $e) {
            Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ':' . $e->getMessage());
            return ejsend_error(['code' => 500, 'type' => 'Sistema', 'message' => 'Error al obtener el recurso: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // echo '{"method":"' . __METHOD__ . '"}';
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $oRequest): JsonResponse
    {
        // Valida datos
        try {
            // Valida campos
            $oValidator = Validator::make($oRequest->all(), [
                'activo' => 'boolean',
                'name' => 'required|min:2|max:255',
                'email' => 'required|unique:users,email',
                'descripcion' => 'max:255',
                'comercio_uuid' => 'required|uuid|size:36',
                'comercio_nombre' => 'max:255',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Agrega valores
            $oRequest->merge(['password' => Hash::make(str_random(24))]);
            // Crea usuario
            $oUsuario = User::create($oRequest->all());
            return ejsend_success(['usuario' => $oUsuario]);
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . __LINE__ . ':' . $e->getMessage());
            return ejsend_error(['code' => 500, 'type' => 'Sistema', 'message' => 'Error al crear el recurso: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        // Muestra el recurso solicitado
        try {
            $oValidator = Validator::make(['id' => $id], [
                'id' => 'required|numeric',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Busca usuario (borrados y no borrados)
            $oUsuario = $this->mUsuario->withTrashed()->find($id);
            if ($oUsuario == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Usuario no encontrado');
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            } else {
                // Carga tokens del usuario
                $oUsuario->load('tokens');
                // Regresa usuario con tokens
                return ejsend_success(['usuario' => $oUsuario]);
            }
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en ' . __METHOD__ . ' línea ' . __LINE__ . ':' . $e->getMessage());
            return ejsend_error(['code' => 500, 'type' => 'Sistema', 'message' => 'Error al obtener el recurso: ' . $e->getMessage()]);
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
        // echo '{"method":"' . __METHOD__ . '"}';
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $oRequest, $id): JsonResponse
    {
        // Busca usuario
        $oUsuario = $this->mUsuario->find($id);
        if ($oUsuario == null) {
            Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Usuario no encontrado');
            return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
        }
        // Actualiza usuario
        try {
            $oValidator = Validator::make(array_merge(['id' => $id], $oRequest->all()), [
                'id' => 'required|numeric',
                'name' => 'max:255',
                'email' =>  'unique:users,email,' . $id,
                'activo' => 'boolean',
                'descripcion' => 'max:255',
                'comercio_uuid' => 'uuid|size:36',
                'comercio_nombre' => 'max:255',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Actualiza usuario
            $oUsuario->update($oRequest->all());
            return ejsend_success(['usuario' => $oUsuario]);
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . __LINE__ . ':' . $e->getMessage());
            return ejsend_error(['code' => 500, 'type' => 'Sistema', 'message' => 'Error al actualizar el recurso: ' . $e->getMessage()]);
        }
    }

    /**
     * Borra el modelo.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id): JsonResponse
    {
        // Busca usuario
        $oUsuario = $this->oUsuario->find($id);
        if ($oUsuario == null) {
            Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Usuario no encontrado');
            return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
        }
        // Borra usuario
        try {
            // Primero se actualiza en campo activo a 0
            $oUsuario->update(['activo' => 0]);
            $oUsuario->delete();
            // @todo: Inhabilita tokens del usuario
            // Regresa resultado
            return ejsend_success(['usuario' => $oUsuario]);
        } catch (\Exception $e) {
            Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ':' . $e->getMessage());
            return ejsend_error(['code' => 500, 'type' => 'Sistema', 'message' => 'Error al borrar el recurso: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        // Busca usuario
        $oUsuario = $this->oUsuario->find($id);
        if ($oUsuario == null) {
            Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Usuario no encontrado');
            return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
        }
        // Borra usuario
        try {
            // Primero se actualiza en campo activo a 0
            $oUsuario->forceDelete();
            // @todo: Elimina tokens del usuario
            // Regresa resultado
            return ejsend_success([], 204);
        } catch (\Exception $e) {
            Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ':' . $e->getMessage());
            return ejsend_error(['code' => 500, 'type' => 'Sistema', 'message' => 'Error al destruir el recurso: ' . $e->getMessage()]);
        }
    }
}
