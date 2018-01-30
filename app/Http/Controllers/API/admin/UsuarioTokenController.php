<?php

namespace App\Http\Controllers\API\Admin;

use Log;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Laravel\Passport\Token;

class UsuarioTokenController extends Controller
{
    protected $mUsuario;
    protected $mToken;

    public function __construct(User $usuario, Token $token)
    {
        $this->mUsuario = $usuario;
        $this->mToken = $token;
    }

    /**
     * Regresa lista de tokens del usuario.
     *
     * @param \Illuminate\Http\Request $oRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($uid, Request $oRequest): JsonResponse
    {
        // Regresa todos los usuarios paginados
        try {
            // Verifica usuario
            $oValidator = Validator::make(['id' => $uid], [
                'id' => 'required|numeric',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Busca usuario (borrados y no borrados)
            $oUsuario = $this->mUsuario->withTrashed()->find($uid);
            if ($oUsuario == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Usuario no encontrado');
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            }
            // Verifica las variables para despliegue de datos
            $oValidator = Validator::make($oRequest->all(), [
                // Datos de la paginación y filtros
                'per_page' => 'numeric|between:5,100',
                'order' => 'max:30|in:id,name,created_at,updated_at,revoked',
                'search' => 'max:100',
                'revoked' => 'in:no,yes,only',
                'sort' => 'in:asc,desc',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Filtros
            $sFiltro = $oRequest->input('search', false);
            $sRevoked = $oRequest->input('revoked', 'no');
            $cTokens = $this->mToken
                ->where('user_id', $oUsuario->id)
                ->where(
                    function ($q) use ($sFiltro) {
                        if ($sFiltro !== false) {
                            return $q
                                ->orWhere('name', 'like', "%$sFiltro%");
                        }
                    }
                )
                ->where(
                    function ($q) use ($sRevoked) {
                        if ($sRevoked == 'no') {
                            return $q->where('revoked', false);
                        } elseif ($sRevoked == 'yes') {
                            return $q;
                        } elseif ($sRevoked == 'only') {
                            return $q->where('revoked', true);
                        }
                    }
                )
                ->orderBy($oRequest->input('order', 'id'), $oRequest->input('sort', 'asc'))
                ->paginate((int) $oRequest->input('per_page', 25));

            // Envía datos paginados
            return ejsend_success(['tokens' => $cTokens]);
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
    public function store($uid, Request $oRequest): JsonResponse
    {
        // Valida datos
        try {
            // Verifica usuario
            $oValidator = Validator::make(['id' => $uid], [
                'id' => 'required|numeric',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Busca usuario (borrados y no borrados)
            $oUsuario = $this->mUsuario->withTrashed()->find($uid);
            if ($oUsuario == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Usuario no encontrado');
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            }
            // Valida campos
            $oValidator = Validator::make($oRequest->all(), [
                'name' => 'min:2|max:255',
                'scopes' => 'max:255',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Crea token
            $oToken = $oUsuario->createToken($oRequest->input('name', 'Token'), $oRequest->input('scopes', []));
            return ejsend_success(['token' => $oToken]);
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . __LINE__ . ':' . $e->getMessage());
            return ejsend_error(['code' => 500, 'type' => 'Sistema', 'message' => 'Error al crear el recurso: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $uid
     * @param  string  $id Identificador de token
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($uid, $id): JsonResponse
    {
        // Muestra el recurso solicitado
        try {
            // Verifica usuario
            $oValidator = Validator::make(['id' => $uid], [
                'id' => 'required|numeric',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Busca usuario (borrados y no borrados)
            $oUsuario = $this->mUsuario->withTrashed()->find($uid);
            if ($oUsuario == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Usuario no encontrado');
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            }
            // Valida campos
            $oValidator = Validator::make(['id' => $uid], [
                'id' => 'required|string',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Busca usuario (borrados y no borrados)
            $oTokens = $this->mToken->find($id);
            if ($oTokens == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Token no encontrado');
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            }
            // Regresa usuario con tokens
            return ejsend_success(['token' => $oTokens]);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en ' . __METHOD__ . ' línea ' . __LINE__ . ':' . $e->getMessage());
            return ejsend_error(['code' => 500, 'type' => 'Sistema', 'message' => 'Error al obtener el recurso: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $uid
     * @param  string  $id Identificador de token
     * @return \Illuminate\Http\Response
     */
    public function edit($uid, $id)
    {
        // echo '{"method":"' . __METHOD__ . '"}';
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @param  int  $uid
     * @param  string  $id Identificador de token
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($uid, Request $oRequest, $id): JsonResponse
    {
        // No se deben actualizar los tokens
    }

    /**
     * Borra el modelo.
     *
     * @param  int  $uid
     * @param  string  $id Identificador de token
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($uid, $id): JsonResponse
    {
        // @todo: Se debe revocar el token
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $uid
     * @param  string  $id Identificador de token
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($uid): JsonResponse
    {
        // @todo: Se debe eliminar el token
    }
}
