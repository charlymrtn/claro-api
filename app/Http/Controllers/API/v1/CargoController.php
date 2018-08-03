<?php

namespace app\Http\Controllers\API\v1;

use Log;
use App;
use Exception;
use Validator;
use Carbon\Carbon;
use Webpatser\Uuid\Uuid;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Classes\Pagos\Parametros\PeticionCargo;
use App\Classes\Pagos\Base\Contacto;
use App\Classes\Pagos\Base\Pedido;
use App\Classes\Pagos\Base\Direccion;
use App\Classes\Pagos\Base\Telefono;
use App\Classes\Pagos\Medios\TarjetaCredito;
use App\Classes\Pagos\Procesos\Cargo;
use App\Classes\Sistema\Mensaje;
use App\Models\Medios\Tarjeta;
use App\Models\Transaccion;

class CargoController extends Controller
{

    /**
     * Cargo instance.
     *
     * @var \App\Classes\Pagos\Procesos\Cargo
     */
    protected $oCargo;

    /**
     * Tarjeta instance.
     *
     * @var \App\Models\Medios\Tarjeta
     */
    protected $mTarjeta;


    /**
     * Crea nueva instancia.
     *
     * @return void
     */
    public function __construct(Cargo $cargo, Tarjeta $tarjeta)
    {
        $this->oCargo = $cargo;
        $this->mTarjeta = $tarjeta;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function cargo(Request $oRequest)
    {
        // Formatea y encapsula datos en PeticionCargo
        try {

            // Obtiene comercio_uuid del token del usuario de la petición
            $sComercioUuid = $oRequest->user()->comercio_uuid;

            // Define valores por default antes de validación
            $oRequest->merge([
                'comercio_uuid'=> $sComercioUuid,
            ]);

            // Valida datos de entrada
            $oValidator = Validator::make($oRequest->toArray(), [
                'comercio_uuid' => 'required|string',
                'descripcion' => 'max:250',
                'prueba' => 'boolean',
                'monto' => 'required',
                'parcialidades' => 'numeric|min:0|max:48',
                // TARJETA
                    'tarjeta.cvv2' => 'required_without:tarjeta.token|numeric',
                    // Con token
                    'tarjeta.token' => 'required_without:tarjeta.pan|string',
                    // Sin token
                    'tarjeta.pan' => 'required_without:tarjeta.token|numeric',
                    'tarjeta.nombre' => 'required_without:tarjeta.token|min:3|max:60',
                    'tarjeta.expiracion_mes' => 'required_without:tarjeta.token|numeric',
                    'tarjeta.expiracion_anio' => 'required_without:tarjeta.token|numeric',
                    'tarjeta.inicio_mes' => 'numeric',
                    'tarjeta.inicio_anio' => 'numeric',
                    'tarjeta.nombres' => 'required_without_all:tarjeta.token,tarjeta.nombre|min:3|max:30',
                    'tarjeta.apellido_paterno' => 'required_without_all:tarjeta.token,tarjeta.nombre|min:3|max:30',
                    'tarjeta.apellido_materno' => 'required_without_all:tarjeta.token,tarjeta.nombre|min:3|max:30',
                    'tarjeta.direccion' => 'array',
                // PEDIDO
                    'pedido.id' => 'max:48',
                    'pedido.direccion_envio' => 'array',
                    'pedido.articulos' => 'numeric',
                // CLIENTE
                    'cliente.id' => 'string',
                    'cliente.nombre' => 'min:3|max:30',
                    'cliente.apellido_paterno' => 'min:3|max:30',
                    'cliente.apellido_materno' => 'min:3|max:30',
                    'cliente.email' => 'email',
                    'cliente.telefono' => 'string',
                    'cliente.direccion' => 'array',
                    'cliente.creacion' => 'date',
            ]);
            if ($oValidator->fails()) {
                $sCode = '400';
                Log::error('Error de validación de parámetros: ' . json_encode($oValidator->errors()));
                return ejsend_fail(['code' => $sCode, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], $sCode, ['errors' => $oValidator->errors()]);
            }

            // Valida y define tarjeta
            $uTarjetaToken = $oRequest->input('tarjeta.token', null);
            if (!empty($uTarjetaToken)) {
                // Tarjeta existente
                $oTarjeta = $this->mTarjeta->where('comercio_uuid', $sComercioUuid)->find($uTarjetaToken);
                if ($oTarjeta == null) {
                    Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Tarjeta no encontrada');
                    return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Tarjeta no encontrada.'], 404);
                }
                $oTarjetaCredito = new TarjetaCredito([
                    'nombre' => $oTarjeta->nombre,
                    'expiracion_mes' => $oTarjeta->expiracion_mes,
                    'expiracion_anio' => $oTarjeta->expiracion_anio,
                    'token' => $uTarjetaToken,
                ]);
                if ($oRequest->input('tarjeta.cvv2', false)) {
                    $oTarjetaCredito['cvv2'] = $oRequest->input('tarjeta.cvv2');
                }
                $oTarjetaCredito->pan_hash = $oTarjeta->pan_hash;
                $oTarjetaCredito->iin = $oTarjeta->iin;
                $oTarjetaCredito->marca = $oTarjeta->marca;
                $oTarjetaCredito->terminacion = $oTarjeta->terminacion;
            } else {
                // Nueva tarjeta
                $oTarjetaCredito = new TarjetaCredito([
                    'pan' => $oRequest->input('tarjeta.pan'),
                    'nombre' => $oRequest->input('tarjeta.nombre'),
                    'cvv2' => $oRequest->input('tarjeta.cvv2'),
                    'expiracion_mes' => $oRequest->input('tarjeta.expiracion_mes'),
                    'expiracion_anio' => $oRequest->input('tarjeta.expiracion_anio'),
                ]);
            }

            // Define ambiente de pruebas
            if (in_array(App::environment(), ['local', 'dev', 'sandbox'])) {
                $bPrueba = true;
            } else {
                $bPrueba = $oRequest->input('prueba', true);
            }

            // Define PeticionCargo
            $oPeticionCargo = new PeticionCargo([
                'comercio_uuid' => $oRequest->input('comercio_uuid'),
                'prueba' => $bPrueba,
                'descripcion' => $oRequest->input('descripcion', ''),
                'monto' => $oRequest->input('monto', '0.00'),
                'puntos' => $oRequest->input('puntos', 0),
                'parcialidades' => $oRequest->input('parcialidades', 0),
                'diferido' => $oRequest->input('diferido', 0),
                'tarjeta' => $oTarjetaCredito,
                'pedido' => new Pedido([
                    'id' => $oRequest->input('pedido.id', 0),
                    'articulos' => $oRequest->input('pedido.articulos', 1),
                    'peso' => $oRequest->input('pedido.peso', 0),
                    'total' => $oRequest->input('pedido.total', $oRequest->input('monto', '0.00')),
                    'direccion_envio' => new Direccion([
                        'pais' => $oRequest->input('pedido.direccion.pais', 'MEX'),
                        'estado' => $oRequest->input('pedido.direccion.estado', 'CMX'),
                        'ciudad' => $oRequest->input('pedido.direccion.ciudad', 'CDMX'),
                        'municipio' => $oRequest->input('pedido.direccion.municipio', 'Delegación'),
                        'linea1' => $oRequest->input('pedido.direccion.linea1', ''),
                        'linea2' => $oRequest->input('pedido.direccion.linea2', ''),
                        'linea3' => $oRequest->input('pedido.direccion.linea3', ''),
                        'cp' => $oRequest->input('pedido.direccion.cp', '0000'),
                        'telefono' => new Telefono([
                            'tipo' => $oRequest->input('pedido.direccion.telefono.tipo', 'desconocido'),
                            'codigo_pais' => $oRequest->input('pedido.direccion.telefono.codigo_pais', '52'),
                            'codigo_area' => $oRequest->input('pedido.direccion.telefono.codigo_area', '55'),
                            'numero' => $oRequest->input('pedido.direccion.telefono', '0000000000'),
                            'extension' => $oRequest->input('pedido.direccion.extension', null),
                        ]),
                    ]),
                ]),
                'direccion_cargo' => new Direccion([
                    'pais' => $oRequest->input('pedido.direccion.pais', 'MEX'),
                    'estado' => $oRequest->input('pedido.direccion.estado', 'CMX'),
                    'ciudad' => $oRequest->input('pedido.direccion.ciudad', 'CDMX'),
                    'municipio' => $oRequest->input('pedido.direccion.municipio', 'Delegación'),
                    'linea1' => $oRequest->input('pedido.direccion.linea1', ''),
                    'linea2' => $oRequest->input('pedido.direccion.linea2', ''),
                    'linea3' => $oRequest->input('pedido.direccion.linea3', ''),
                    'cp' => $oRequest->input('pedido.direccion.cp', '0000'),
                    'telefono' => new Telefono([
                        'tipo' => $oRequest->input('pedido.direccion.telefono.tipo', 'desconocido'),
                        'codigo_pais' => $oRequest->input('pedido.direccion.telefono.codigo_pais', '52'),
                        'codigo_area' => $oRequest->input('pedido.direccion.telefono.codigo_area', '55'),
                        'numero' => $oRequest->input('pedido.direccion.telefono', '0000000000'),
                        'extension' => $oRequest->input('pedido.direccion.extension', null),
                    ]),
                ]),
                'cliente' => new Contacto([
                    'id' => $oRequest->input('cliente.id', 0),
                    'nombre' => $oRequest->input('cliente.nombre'),
                    'apellido_paterno' => $oRequest->input('cliente.apellido_paterno'),
                    'apellido_materno' => $oRequest->input('cliente.apellido_materno'),
                    'genero' => $oRequest->input('cliente.genero', 'Desconocido'),
                    'email' => $oRequest->input('cliente.email'),
                    'telefono' => new Telefono([
                        'tipo' => $oRequest->input('cliente.telefono.tipo', 'desconocido'),
                        'codigo_pais' => $oRequest->input('cliente.telefono.codigo_pais', '52'),
                        'codigo_area' => $oRequest->input('cliente.telefono.codigo_area', '55'),
                        'numero' => $oRequest->input('cliente.telefono', '0000000000'),
                        'extension' => $oRequest->input('cliente.extension', null),
                    ]),
                    'nacimiento' => $oRequest->input('cliente.nacimiento', null),
                    'creacion' => $oRequest->input('cliente.creacion', null),
                    'cliente.direccion' => new Direccion([
                        'pais' => $oRequest->input('pedido.direccion.pais', 'MEX'),
                        'estado' => $oRequest->input('pedido.direccion.estado', 'CMX'),
                        'ciudad' => $oRequest->input('pedido.direccion.ciudad', 'CDMX'),
                        'municipio' => $oRequest->input('pedido.direccion.municipio', 'Delegación'),
                        'linea1' => $oRequest->input('pedido.direccion.linea1', ''),
                        'linea2' => $oRequest->input('pedido.direccion.linea2', ''),
                        'linea3' => $oRequest->input('pedido.direccion.linea3', ''),
                        'cp' => $oRequest->input('pedido.direccion.cp', '0000'),
                        'telefono' => new Telefono([
                            'tipo' => $oRequest->input('pedido.direccion.telefono.tipo', 'desconocido'),
                            'codigo_pais' => $oRequest->input('pedido.direccion.telefono.codigo_pais', '52'),
                            'codigo_area' => $oRequest->input('pedido.direccion.telefono.codigo_area', '55'),
                            'numero' => $oRequest->input('pedido.direccion.telefono', '0000000000'),
                            'extension' => $oRequest->input('pedido.direccion.extension', null),
                        ]),
                    ]),
                ]),
            ]);
            // Envía petición a proceso de cargo
            $oCargo = $this->oCargo->carga($oPeticionCargo);
            // Regresa resultados
            return ejsend_success(['cargo' => $oCargo]);
        } catch (\Exception $e) {
            if (empty($e->getCode())) {
                $sCode = '400';
            } else {
                $sCode = $e->getCode();
            }
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_error(['code' => $sCode, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], $sCode, ['errors' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        //
    }

    /**
     * Cancela cargo.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @param  string $uuid
     * @return string Json result string in JSend format
     */
    public function cancel(Request $oRequest, string $uuid)
    {
        // Cancela cargo
        try {
            // Obtiene comercio_uuid del token del usuario de la petición
            $sComercioUuid = $oRequest->user()->comercio_uuid;
            // Obtiene transacción original
            $oTrx = Transaccion::find($uuid);
            if ($oTrx == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Transacción no encontrada');
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Transacción no encontrada.'], 404);
            }
            // Define valores por default antes de validación
            $oRequest->merge([
                'comercio_uuid' => $sComercioUuid,
            ]);
            // Valida request
            $oValidator = Validator::make($oRequest->toArray(), [
                'comercio_uuid' => 'required|string',
                'monto' => 'required',
                'pedido.id' => 'max:48',
                'cliente.id' => 'string',
            ]);
            if ($oValidator->fails()) {
                $sCode = '400';
                Log::error('Error de validación de parámetros: ' . json_encode($oValidator->errors()));
                return ejsend_fail(['code' => $sCode, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], $sCode, ['errors' => $oValidator->errors()]);
            }
            // Valida fecha para cancelación
            $oFechaHoy = Carbon::now();
            // Valida si ya terminó el día
            $oFechaDesde = Carbon::now();
            $oFechaDesde->hour = 6;
            $oFechaDesde->minute = 0;
            $oFechaDesde->second = 0;
            $oFechaLimite = Carbon::now();
            $oFechaLimite->hour = 21;
            $oFechaLimite->minute = 0;
            $oFechaLimite->second = 0;
            if (!$oFechaHoy->between($oFechaDesde, $oFechaLimite)) {
                $sCode = '412';
                Log::error('Error procesando cancelación: Hora de intento de transacción no permitida.');
                return ejsend_fail(['code' => $sCode, 'type' => 'Reglas', 'message' => 'Error procesando cancelación: Hora de intento de transacción no permitidas.'], $sCode);
            }
            // Valida si la trx es del mismo día
            if ($oFechaHoy->diffInDays($oTrx->created_at) > 0) {
                $sCode = '412';
                Log::error('Error procesando cancelación: Fecha de transacción mayor al día límite.');
                return ejsend_fail(['code' => $sCode, 'type' => 'Precondición', 'message' => 'Error procesando cancelación: Fecha de transacción mayor al día límite.'], $sCode);
            }
            // Valida transacción
            if ($oRequest->input('monto', '0.00') != $oTrx->monto) {
                $sCode = '400';
                Log::error('Error procesando cancelación: El monto de la transacción original no coincide.');
                return ejsend_fail(['code' => $sCode, 'type' => 'Parámetros', 'message' => 'Error procesando cancelación: Error en parámetros de entrada.'], $sCode, ['errors' => ['monto' => 'El monto de la transacción original no coincide.']]);
            }
            if ($oTrx->estatus != 'completada') {
                $sCode = '412';
                Log::error('Error procesando cancelación: La transacción no se puede cancelar.');
                return ejsend_fail(['code' => $sCode, 'type' => 'Precondición', 'message' => 'Error procesando cancelación: La transacción no se puede cancelar.'], $sCode, ['errors' => ['estatus' => 'El estatus de la transacción no permite cancelación.']]);
            }
            // Realiza cancelación
            $oTrx->estatus = 'cancelada';
            $oTrx->save();
            $oTrx = Transaccion::find($uuid);
            // ========================================================================
            // Envía transacciones a admin y clientes
            $oMensajeCP = new Mensaje();
            $oMensajeResultadoA = $oMensajeCP->envia('clientes', '/api/admin/transaccion/' . $oTrx->uuid, 'PUT', $oTrx->toJson());
            #dump($oMensajeResultadoA);
            $oMensajeResultadoB = $oMensajeCP->envia('admin', '/api/admin/transaccion/' . $oTrx->uuid, 'PUT', $oTrx->toJson());
            #dump($oMensajeResultadoB);
            // ========================================================================
            // Regresa respuesta
            $aResponse = [
                'id' => $uuid,
                'monto' => $oTrx->monto,
                'cargo' => $uuid,
                'autorizacion_id' => $oTrx->datos_procesador['data']['importantData']['authNum'],
                'tipo' => 'cancelacion',
                'orden_id' => $oRequest->input('orden_id', null),
                'cliente_id' => $oRequest->input('cliente_id', null),
                'estatus' => $oTrx->estatus,
            ];
            return ejsend_success(['cancelacion' => $aResponse]);
        } catch (\Exception $e) {
            if (empty($e->getCode())) {
                $sCode = '400';
            } else {
                $sCode = $e->getCode();
            }
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_error(['code' => $sCode, 'type' => 'Parámetros', 'message' => 'Error al procesar la cancelación.'], $sCode, ['errors' => $e->getMessage()]);
        }
    }

    /**
     * REembolsa cargo.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @param  string $uuid
     * @return string Json result string in JSend format
     */
    public function refund(Request $oRequest, string $uuid)
    {
        // Reembolsa cargo
        try {
            // Obtiene comercio_uuid del token del usuario de la petición
            $sComercioUuid = $oRequest->user()->comercio_uuid;
            // Obtiene transacción original
            $oTrx = Transaccion::find($uuid);
            if ($oTrx == null) {
                Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ': Transacción no encontrada');
                return ejsend_fail(['code' => 404, 'type' => 'General', 'message' => 'Transacción no encontrada.'], 404);
            }
            // Define valores por default antes de validación
            $oRequest->merge([
                'comercio_uuid' => $sComercioUuid,
            ]);
            // Valida request
            $oValidator = Validator::make($oRequest->toArray(), [
                'comercio_uuid' => 'required|string',
                'monto' => 'required',
                'pedido.id' => 'max:48',
                'cliente.id' => 'string',
            ]);
            if ($oValidator->fails()) {
                $sCode = '400';
                Log::error('Error de validación de parámetros: ' . json_encode($oValidator->errors()));
                return ejsend_fail(['code' => $sCode, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], $sCode, ['errors' => $oValidator->errors()]);
            }
            // Valida fecha para cancelación
            $oFechaHoy = Carbon::now();
            // Valida si ya terminó el día
            $oFechaDesde = Carbon::now();
            $oFechaDesde->hour = 6;
            $oFechaDesde->minute = 0;
            $oFechaDesde->second = 0;
            $oFechaLimite = Carbon::now();
            $oFechaLimite->hour = 21;
            $oFechaLimite->minute = 0;
            $oFechaLimite->second = 0;
            if (!$oFechaHoy->between($oFechaDesde, $oFechaLimite)) {
                $sCode = '412';
                Log::error('Error procesando reembolso: Hora de intento de transacción no permitida.');
                return ejsend_fail(['code' => $sCode, 'type' => 'Reglas', 'message' => 'Error procesando reembolso: Hora de intento de transacción no permitidas.'], $sCode);
            }
            // Valida si la trx es del mismo día
            if ($oFechaHoy->diffInDays($oTrx->created_at) < 1) {
                $sCode = '412';
                Log::error('Error procesando reembolso: Fecha de transacción menor al día permitido.');
                return ejsend_fail(['code' => $sCode, 'type' => 'Precondición', 'message' => 'Error procesando reembolso: Fecha de transacción menor al día permitido.'], $sCode);
            }
            // Valida transacción
            if ($oRequest->input('monto', '0.00') != $oTrx->monto) {
                $sCode = '400';
                Log::error('Error procesando reembolso: El monto de la transacción original no coincide.');
                return ejsend_fail(['code' => $sCode, 'type' => 'Parámetros', 'message' => 'Error procesando reembolso: Error en parámetros de entrada.'], $sCode, ['errors' => ['monto' => 'El monto de la transacción original no coincide.']]);
            }
            if ($oTrx->estatus != 'completada') {
                $sCode = '412';
                Log::error('Error procesando reembolso: La transacción no se puede reembolsar.');
                return ejsend_fail(['code' => $sCode, 'type' => 'Precondición', 'message' => 'Error procesando reembolso: La transacción no se puede reembolsar.'], $sCode, ['errors' => ['estatus' => 'El estatus de la transacción no permite reembolso.']]);
            }
            // Realiza cancelación
            $oTrx->estatus = 'reembolsada';
            $oTrx->save();
            $oTrx = Transaccion::find($uuid);
            // ========================================================================
            // Envía transacciones a admin y clientes
            $oMensajeCP = new Mensaje();
            $oMensajeResultadoA = $oMensajeCP->envia('clientes', '/api/admin/transaccion/' . $oTrx->uuid, 'PUT', $oTrx->toJson());
            #dump($oMensajeResultadoA);
            $oMensajeResultadoB = $oMensajeCP->envia('admin', '/api/admin/transaccion/' . $oTrx->uuid, 'PUT', $oTrx->toJson());
            #dump($oMensajeResultadoB);
            // ========================================================================
            // Regresa respuesta
            $aResponse = [
                'id' => $uuid,
                'monto' => $oTrx->monto,
                'cargo' => $uuid,
                'autorizacion_id' => $oTrx->datos_procesador['data']['importantData']['authNum'],
                'tipo' => 'reembolso',
                'orden_id' => $oRequest->input('orden_id', null),
                'cliente_id' => $oRequest->input('cliente_id', null),
                'estatus' => $oTrx->estatus,
            ];
            return ejsend_success(['reembolso' => $aResponse]);
        } catch (\Exception $e) {
            // Define error
            if (empty($e->getCode())) {
                $sCode = 400; $sErrorType = 'Parámetros';
            } else {
                $sCode = (int) $e->getCode(); $sErrorType = 'Sistema';
            }
            // Registra error
            Log::error('Error on ' . __METHOD__ . ' line ' . $e->getLine() . ':' . $e->getMessage());
            return ejsend_error(['code' => $sCode, 'type' => $sErrorType, 'message' => 'Error al procesar el reembolso: ' . $e->getMessage(), $sCode, ['errors' => $e->getMessage()]]);
        }
    }
}
