<?php

namespace app\Http\Controllers\API\v1;

use Log;
use Auth;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Medios\Tarjeta;
use App\Classes\Pagos\Medios\TarjetaCredito;
use App\Classes\Pagos\Base\Direccion;
use App\Classes\Pagos\Base\Telefono;

class TarjetaController extends Controller
{

    /**
     * Tarjeta instance.
     *
     * @var \App\Models\Medios\Tarjeta
     */
    protected $mTarjeta;

    /**
     * TarjetaController constructor.
     *
     * @param Tarjeta $tarjeta
     */
    public function __construct(Tarjeta $tarjeta)
    {
        $this->mTarjeta = $tarjeta;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $oRequest): JsonResponse
    {
        try {
            // Verifica las variables para despliegue de datos
            $oValidator = Validator::make($oRequest->all(), [
                'per_page' => 'numeric|between:5,100',
                'order' => 'max:30|in:uuid,nombre,marca,comercio_uuid,cliente_uuid,iin,pan,terminacion,created_at,updated_at,deleted_at',
                'search' => 'max:100',
                'sort' => 'in:asc,desc',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Filtro
            $sFiltro = $oRequest->input('search', false);
            $sDeleted = $oRequest->input('deleted', 'yes');
            // Busca tarjeta
            $aTarjeta = $this->mTarjeta
                ->withTrashed()
                ->where(
                    function ($q) use ($sFiltro) {
                        if ($sFiltro !== false) {
                            return $q
                                ->orWhere('uuid', 'like', "%$sFiltro%")
                                ->orWhere('nombre', 'like', "%$sFiltro%")
                                ->orWhere('marca', 'like', "%$sFiltro%")
                                ->orWhere('comercio_uuid', 'like', "%$sFiltro%")
                                ->orWhere('cliente_uuid', 'like', "%$sFiltro%")
                                ->orWhere('pan', 'like', "%$sFiltro%");
                        }
                    }
                )
                ->where(
                    function ($q) use ($sDeleted) {
                        if ($sDeleted == 'no') {
                            return $q->whereNull('deleted_at');
                        } else if ($sDeleted == 'yes') {
                            return $q;
                        } else if ($sDeleted == 'only') {
                            return $q->whereNotNull('deleted_at');
                        }
                    }
                )
                ->orderBy($oRequest->input('order', 'uuid'), $oRequest->input('sort', 'asc'))
                ->paginate((int) $oRequest->input('per_page', 25));
            // Envía datos paginados
            return ejsend_success(["status" => "success", "data" => ["tarjetas" => $aTarjeta]]);
        } catch (\Exception $e) {
            // Registra error
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return ejsend_error([
                'code' => 500,
                'type' => 'Tarjeta',
                'message' => 'Error al obtener el recurso: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $oRequest): JsonResponse
    {
        // Formatea y encapsula datos
        try {
            // Crea tarjeta y valida
            $oTarjetaCredito = $this->tarjetaRequest($oRequest);
            // Guarda resultado en base de datos
            $oTarjeta = $this->mTarjeta->create(array_merge([
                    'comercio_uuid' => $oRequest->user()->comercio_uuid,
                    'default' => $oRequest->input('default', false),
                    'cargo_unico' => $oRequest->input('cargo_unico', true),
                ], $oTarjetaCredito->toArray()));
            // Envía tarjeta a bóveda
            // Guarda resultado en base de datos
            // Formatea respuestay regresa resultado
            return ejsend_success(['tarjeta' => $this->tarjetaResponse($oTarjeta)]);
        } catch (\Exception $e) {
            if (empty($e->getCode())) {
                $sCode = 400;
            } else {
                $sCode = (int) $e->getCode();
            }
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_fail(['code' => $sCode, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], $sCode, ['errors' => $e->getMessage()]);
        }
    }

    /**
     * Muestra tarjeta solicitada.
     *
     * @param  string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        // Busca tarjeta
        try {
            $oValidator = Validator::make(['uuid' => $uuid], [
                'uuid' => 'required|uuid',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Obtiene usuario del request
            $oUser = Auth::user();
            // Busca tarjeta
            $oTarjeta = $this->mTarjeta->where('comercio_uuid', $oUser->comercio_uuid)->find($uuid);
            if ($oTarjeta == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Objeto no encontrado');
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            } else {
                // Regresa usuario con clientes y tokens
                return ejsend_success(['tarjeta' => $this->tarjetaResponse($oTarjeta)]);
            }
        } catch (\Exception $e) {
            if (empty($e->getCode())) {
                $sCode = 400;
            } else {
                $sCode = (int) $e->getCode();
            }
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_fail(['code' => $sCode, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], $sCode, ['errors' => $e->getMessage()]);
        }
    }

    /**
     * Actualiza datos de tarjeta.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @param  string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $oRequest, string $uuid): JsonResponse
    {
        // Formatea y encapsula datos
        try {
            // Obtiene usuario del request
            $oUser = Auth::user();
            // Busca tarjeta
            $oTarjeta = $this->mTarjeta->where('comercio_uuid', $oUser->comercio_uuid)->find($uuid);
            if ($oTarjeta == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Tarjeta no encontrada:' . $uuid);
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Tarjeta no encontrada.'], 404);
            }
            // Crea TarjetaCredito y valida
            $oTarjetaCredito = $this->tarjetaRequest($oRequest);
            // Actualiza en base de datos
            $oTarjeta->update(array_merge([
                    'comercio_uuid' => $oRequest->user()->comercio_uuid,
                    'default' => $oRequest->input('default', false),
                    'cargo_unico' => $oRequest->input('cargo_unico', true),
                ], $oTarjetaCredito->toArray()));
            // Envía tarjeta a bóveda
            // Guarda resultado en base de datos
            // Formatea respuestay regresa resultado
            return ejsend_success(['tarjeta' => $this->tarjetaResponse($oTarjeta)]);
        } catch (\Exception $e) {
            if (empty($e->getCode())) {
                $sCode = 400;
            } else {
                $sCode = (int) $e->getCode();
            }
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_error(['code' => $sCode, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], $sCode, ['errors' => $e->getMessage()]);
        }
    }

    /**
     * Elimina tarjeta.
     *
     * @param  string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $uuid): JsonResponse
    {
        // Busca tarjeta
        try {
            // Valida datos de entrada
            $oValidator = Validator::make(['uuid' => $uuid], [
                'uuid' => 'required|uuid',
            ]);
            if ($oValidator->fails()) {
                return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
            }
            // Obtiene usuario del request
            $oUser = Auth::user();
            // Busca tarjeta
            $oTarjeta = $this->mTarjeta->where('comercio_uuid', $oUser->comercio_uuid)->find($uuid);
            if ($oTarjeta == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Objeto no encontrado');
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Objeto no encontrado.'], 404);
            } else {
                $oTarjeta->forceDelete();
                // Regresa usuario con clientes y tokens
                return ejsend_success(['tarjeta' => $this->tarjetaResponse($oTarjeta)]);
            }
        } catch (\Exception $e) {
            if (empty($e->getCode())) {
                $sCode = 400;
            } else {
                $sCode = (int) $e->getCode();
            }
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_fail(['code' => $sCode, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], $sCode, ['errors' => $e->getMessage()]);
        }
    }

    /**
     * Formato de request de tarjeta
     *
     * @param  Request $oRequest Request con datos de tarjeta
     * @return TarjetaCredito
     */
    private function tarjetaRequest(Request $oRequest): TarjetaCredito
    {
        return $oTarjetaCredito = new TarjetaCredito([
            'nombre' => $oRequest->input('nombre'),
            'pan' => $oRequest->input('pan'),
            'cvv2' => $oRequest->input('cvv2'),
            'expiracion_mes' => $oRequest->input('expiracion_mes'),
            'expiracion_anio' => $oRequest->input('expiracion_anio'),
            'direccion' => new Direccion([
                'linea1' => $oRequest->input('direccion.linea1', ''),
                'linea2' => $oRequest->input('direccion.linea2', ''),
                'linea3' => $oRequest->input('direccion.linea3', ''),
                'cp' => $oRequest->input('direccion.cp', '0000'),
                'pais' => $oRequest->input('direccion.pais', 'MEX'),
                'estado' => $oRequest->input('direccion.estado', 'CMX'),
                'ciudad' => $oRequest->input('direccion.ciudad', 'CDMX'),
                'municipio' => $oRequest->input('direccion.municipio', 'Delegación'),
                'telefono' => new Telefono([
                    'tipo' => $oRequest->input('direccion.telefono.tipo', 'desconocido'),
                    'codigo_pais' => $oRequest->input('direccion.telefono.codigo_pais', '52'),
                    'codigo_area' => $oRequest->input('direccion.telefono.codigo_area', '55'),
                    'numero' => $oRequest->input('direccion.telefono.numero', '0000000000'),
                    'extension' => $oRequest->input('direccion.telefono.extension', null),
                ]),
            ]),
        ]);
    }

    /**
     * Formato de respuesta de tarjeta
     *
     * @param  TarjetaCredito $oTarjeta Objeto de modelo de TarjetaCredito
     * @return array
     */
    private function tarjetaResponse(Tarjeta $oTarjeta): array
    {
        return [
            'token_tarjeta' => $oTarjeta->uuid,
            'pan' => $oTarjeta->pan,
            'iin' => $oTarjeta->iin,
            'terminacion' => $oTarjeta->terminacion,
            'marca' => $oTarjeta->marca,
            'nombre' => $oTarjeta->nombre,
            'expiracion_mes' => $oTarjeta->expiracion_mes,
            'expiracion_anio' => $oTarjeta->expiracion_anio,
            'direccion' => $oTarjeta->direccion,
            'cliente_id' => $oTarjeta->cliente_uuid,
            'default' => $oTarjeta->default,
            'cargo_unico' => $oTarjeta->cargo_unico,
        ];
    }
}