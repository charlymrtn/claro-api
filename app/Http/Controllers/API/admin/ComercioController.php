<?php

namespace App\Http\Controllers\API\Admin;

use Log;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\API\Admin\UsuarioController;

class ComercioController extends UsuarioController
{
    /**
     * Obtiene registro del usuario del comercio con su uuid.
     *
     * @param  uuid  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        // Muestra el recurso solicitado
        try {
            $oValidator = Validator::make(['id' => $id], [
                'id' => 'required|uuid|size:36',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Busca usuario (borrados y no borrados)
            $oUsuario = $this->mUsuario->where('comercio_uuid', $id)->first();
            if ($oUsuario == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Usuario no encontrado');
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            }
            // Llama al método padre con el id del usuario
            // @todo: cambiar llamada por método protegido en UsuarioController para evitar doble búsqueda aunque exista en cache
            return parent::show($oUsuario->id);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_error(['code' => 500, 'type' => 'Sistema', 'message' => 'Error al obtener el recurso: ' . $e->getMessage()]);
        }
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
        $oValidator = Validator::make(['id' => $id], [
            'id' => 'required|uuid|size:36',
        ]);
        if ($oValidator->fails()) {
            return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
        }
        // Busca usuario
        $oUsuario = $this->mUsuario->where('comercio_uuid', $id)->first();
        if ($oUsuario == null) {
            Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Usuario no encontrado');
            return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
        }
        // Llama al método padre con el id del usuario
        // @todo: cambiar llamada por método protegido en UsuarioController para evitar doble búsqueda aunque exista en cache
        return parent::update($oRequest, $oUsuario->id);
    }

    /**
     * Borra el modelo.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id): JsonResponse
    {
        $oValidator = Validator::make(['id' => $id], [
            'id' => 'required|uuid|size:36',
        ]);
        if ($oValidator->fails()) {
            return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
        }
        // Busca usuario
        $oUsuario = $this->mUsuario->where('comercio_uuid', $id)->first();
        if ($oUsuario == null) {
            Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Usuario no encontrado');
            return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
        }
        // Llama al método padre con el id del usuario
        // @todo: cambiar llamada por método protegido en UsuarioController para evitar doble búsqueda aunque exista en cache
        return parent::delete($oUsuario->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $oValidator = Validator::make(['id' => $id], [
            'id' => 'required|uuid|size:36',
        ]);
        if ($oValidator->fails()) {
            return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
        }
        // Busca usuario
        $oUsuario = $this->mUsuario->where('comercio_uuid', $id)->first();
        if ($oUsuario == null) {
            Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Usuario no encontrado');
            return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
        }
        // Llama al método padre con el id del usuario
        // @todo: cambiar llamada por método protegido en UsuarioController para evitar doble búsqueda aunque exista en cache
        return parent::destroy($oUsuario->id);
    }
}
