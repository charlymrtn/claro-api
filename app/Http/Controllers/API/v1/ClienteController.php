<?php

namespace App\Http\Controllers\API\v1;

use Log;
use Auth;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Webpatser\Uuid\Uuid;
use App\Http\Controllers\ApiController;
use App\Models\Cliente;
use App\Classes\Pagos\Base\Direccion;
use App\Classes\Pagos\Base\Telefono;
use App\Http\Resources\v1\ClienteResource;
use App\Http\Resources\v1\ClienteCollectionResource;
use App\Http\Resources\v1\TarjetaResource;
use App\Http\Resources\v1\SuscripcionResource;

class ClienteController extends ApiController
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
     * Obtiene la lista de clientes creados por el comercio
     *
     * @param \Illuminate\Http\Request $oRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $oRequest): JsonResponse
    {
        // Regresa los registros de tarjeta paginados
        try {
            // Verifica las variables para despliegue de datos
            $oValidator = Validator::make($oRequest->all(), [
                // Datos de filtros
                'filtro' => 'max:100',
                // Datos de la paginación
                'registros_por_pagina' => 'numeric|between:5,100',
                'pagina' => 'numeric|min:1',
                'ordenar_por' => 'max:30|in:uuid,comercio_uuid,nombre,apellido_paterno,apellido_materno,sexo,email,nacimiento,estado',
                'orden' => 'in:asc,desc',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Obtiene usuario del request
            $oUser = Auth::user();
            // Filtro
            $sFiltro = $oRequest->input('filtro', false);
            // Busca cliente
            $cClientes = new ClienteCollectionResource($this->mCliente
                ->where('comercio_uuid', $oUser->comercio_uuid)
                ->where(
                    function ($q) use ($sFiltro) {
                        if ($sFiltro !== false) {
                            return $q
                                ->orWhere('uuid', 'like', "%$sFiltro%")
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
                ->orderBy($oRequest->input('ordenar_por', 'created_at'), $oRequest->input('orden', 'desc'))
                ->paginate((int) $oRequest->input('registros_por_pagina', 25), ['*'], 'pagina', (int) $oRequest->input('pagina', 1)));
            // Envía datos paginados
            return ejsend_success(['clientes' => $cClientes]);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_exception($e, 'Error al mostrar los recursos: ' . $e->getMessage());
        }
    }

    /**
     * Guarda un cliente
     *
     * @param  \Illuminate\Http\Request $oRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $oRequest): JsonResponse
    {
        // Guarda cliente
        try {
            // Valida estructura del request
            $this->validateJson($oRequest->getContent());
            // Obtiene comercio_uuid del token del usuario de la petición
            $sComercioUuid = $oRequest->user()->comercio_uuid;
            // Define valores por default antes de validación
            $oRequest->merge([
                'uuid' => Uuid::generate(4)->string,
                'comercio_uuid' => $sComercioUuid,
                'estado' => $oRequest->input('estado', 'activo'),
                'id_externo' => $oRequest->input('id_externo', $oRequest->input('email')),
            ]);
            // Parsea fechas
            $oRequest->merge($this->parseRequestDates($oRequest, ['creacion_externa', 'nacimiento']));
            // Valida campos
            $oValidator = Validator::make($oRequest->all(), $this->mCliente->rules);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
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
            $oClienteExistenteEmail = $this->mCliente
                ->where('comercio_uuid', '=', $sComercioUuid)
                ->where('email', '=', $oRequest->input('email'))
                ->first();
            if (!empty($oClienteExistenteEmail)) {
                return ejsend_fail(['code' => 409, 'type' => 'Parámetros', 'message' => 'Error al crear el recurso: Cliente existente con el mismo email (' . $oCliente->uuid . ')'], 409);
            }
            // Valida cliente creado anteriormente con mismo id_externo
            $oClienteExistenteId = $this->mCliente
                ->where('comercio_uuid', '=', $sComercioUuid)
                ->where('id_externo', '=', $oRequest->input('id_externo'))
                ->first();
            if (!empty($oClienteExistenteId)) {
                return ejsend_fail(['code' => 409, 'type' => 'Parámetros', 'message' => 'Error al crear el recurso: Cliente existente con el mismo id_externo (' . $oCliente->uuid . ')'], 409);
            }
            // Crea objeto
            $oCliente = new ClienteResource($this->mCliente->create($oRequest->all()));
            // Regresa resultados
            return ejsend_success(['cliente' => $oCliente]);
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return ejsend_exception($e, 'Error al crear el recurso: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene un recurso
     *
     * @param string  $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $uuid): JsonResponse
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
            $oCliente = new ClienteResource($this->mCliente->where('comercio_uuid', '=', $sComercioUuid)->find($uuid));
            if ($oCliente == null) {
                Log::error('Error on '.__METHOD__.' line '.__LINE__.': Cliente no encontrado:'.$uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Cliente no encontrado.'], 404);
            }
            // Regresa cliente
            return ejsend_success(['cliente' => $oCliente]);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return ejsend_exception($e, 'Error al mostrar el recurso: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza objeto Cliente.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @param  string  $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $oRequest, string $uuid): JsonResponse
    {
        try {
            // Obtiene comercio_uuid del token del usuario de la petición
            $sComercioUuid = $oRequest->user()->comercio_uuid;
            // Valida uuid
            $oIdValidator = Validator::make(['uuid' => $uuid], [
                'uuid' => 'required|uuid|size:36',
            ]);
            if ($oIdValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oIdValidator->errors()]);
            }
            // Valida estructura del request
            $this->validateJson($oRequest->getContent());
            // Busca cliente
            $oCliente = $this->mCliente->where('comercio_uuid', '=', $sComercioUuid)->find($uuid);
            if ($oCliente == null) {
                Log::error('Error on '.__METHOD__.' line '.__LINE__.': Cliente no encontrado:'.$uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            }
            // Define valores antes de validación
            $oRequest->merge([
                'comercio_uuid' => $sComercioUuid,
            ]);
            // Parsea fechas
            $oRequest->merge($this->parseRequestDates($oRequest, ['creacion_externa', 'nacimiento']));
            // Valida datos
            $oValidator = Validator::make($oRequest->all(), array_merge($this->mCliente->rules, [
                'email' => 'email',
            ]));
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
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
            return ejsend_success(['cliente' => new ClienteResource($oCliente)]);
        } catch (\Exception $e) {
            Log::error('Error on '.__METHOD__.' line '.$e->getLine().':'.$e->getMessage());
            return ejsend_exception($e, 'Error al actualizar el recurso: ' . $e->getMessage());
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
            // Valida borrado de cliente
            // Busca suscripciones activas
            if (!empty($oCliente->suscripciones) && $oCliente->suscripciones->isNotEmpty()) {
                Log::error('Error on '.__METHOD__.' line '.__LINE__.': El cliente cuenta con suscripciones, no se puede borrar.'.$uuid);
                return ejsend_fail(['code' => 412, 'type' => 'Cliente', 'message' => 'El cliente cuenta con suscripciones, no se puede borrar.'], 412);
            }
            // Borra Cliente
            $oCliente->forceDelete();
            return ejsend_success(['cliente' => new ClienteResource($oCliente)], 204);
        } catch (\Exception $e) {
            Log::error('Error on '.__METHOD__.' line '.$e->getLine().':'.$e->getMessage());
            return ejsend_exception($e, 'Error al borrar el recurso: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene las tarjetas del cliente
     *
     * @param string  $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function tarjetas($uuid): JsonResponse
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
            $oCliente = new ClienteResource($this->mCliente->with('tarjetas')->where('comercio_uuid', '=', $sComercioUuid)->find($uuid));
            if ($oCliente == null) {
                Log::error('Error on '.__METHOD__.' line '.__LINE__.': Cliente no encontrado:'.$uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Cliente no encontrado.'], 404);
            }
            // Regresa tarjetas del cliente
            #return ejsend_success(['tarjetas' => new TarjetaCollectionResource($oCliente->tarjetas)]);
            return ejsend_success(['tarjetas' => TarjetaResource::collection($oCliente->tarjetas)]);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return ejsend_exception($e, 'Error al obtener los recursos: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene las suscripciones del cliente
     *
     * @param string  $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function suscripciones($uuid): JsonResponse
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
            $oCliente = new ClienteResource($this->mCliente->with('suscripciones')->where('comercio_uuid', '=', $sComercioUuid)->find($uuid));
            if ($oCliente == null) {
                Log::error('Error on '.__METHOD__.' line '.__LINE__.': Cliente no encontrado:'.$uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Cliente no encontrado.'], 404);
            }
            // Regresa suscripciones del cliente
            return ejsend_success(['suscripciones' => SuscripcionResource::collection($oCliente->suscripciones)]);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return ejsend_exception($e, 'Error al obtener los recursos: ' . $e->getMessage());
        }
    }
}
