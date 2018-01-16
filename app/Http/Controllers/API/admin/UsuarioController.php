<?php

namespace App\Http\Controllers\API\Admin;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Log;

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
    public function index(Request $oRequest)
    {
        // Regresa todos los usuarios paginados
        try {
            // Verifica las variables para despliegue de datos
            $oValidator = Validator::make($oRequest->all(), [
                // Datos de la paginación y filtros
                'per_page' => 'numeric|between:5,100',
                'order' => 'max:30|in:id,name,email,activo,comercio_uuid,comercio_nombre,created_at,updated_at,deleted_at',
                'search' => 'max:100',
                'deleted' => 'in:no,yes,only',
                'sort' => 'in:asc,desc',
            ]);
            if ($oValidator->fails()) {
                return response()->json(["status" => "fail", "data" => ["errors" => $oValidator->errors()]]);
            }
            // Filtros
            $sFiltro = $oRequest->input('search', false);
            $sDeleted = $oRequest->input('deleted', 'no');
            $cUsuarios = $this->mUsuario
                ->withTrashed()
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
            return response()->json(["status" => "success", "data" => ["usuarios" => $cUsuarios]]);
        } catch (\Exception $e) {
            Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ':' . $e->getMessage());
            return response()->json(["status" => "fail", "data" => ["message" => "Error al obtener el recurso: " . $e->getMessage()]]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        echo '{"method":"' . __METHOD__ . '"}';
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @return \Illuminate\Http\Response
     */
    public function store(Request $oRequest)
    {
        //
        echo '{"method":"' . __METHOD__ . '"}';
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Muestra el recurso solicitado
        try {
            $oValidator = Validator::make(['id' => $id], [
                'id' => 'required|numeric',
            ]);
            if ($oValidator->fails()) {
                return response()->json(["status" => "fail", "data" => ["errors" => $oValidator->errors()]]);
            }
            // Busca usuario (borrados y no borrados)
            $oUsuario = $this->mUsuario->withTrashed()->find($id);
            // Carga tokens del usuario
            $oUsuario->load('tokens');
            if ($oUsuario == null) {
                return response()->json(["status" => "fail", "data" => ["message" => "Objeto no encontrado. Error: " . $e->getMessage()]]);
            } else {
                return response()->json(["status" => "success", "data" => ["usuario" => $oUsuario]]);
            }
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en ' . __METHOD__ . ' línea ' . __LINE__ . ':' . $e->getMessage());
            return response()->json(["status" => "fail", "data" => ["message" => "Error al obtener el recurso: " . $e->getMessage()]]);
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
        //
        echo '{"method":"' . __METHOD__ . '"}';
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        echo '{"method":"' . __METHOD__ . '"}';
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        echo '{"method":"' . __METHOD__ . '"}';
    }
}
