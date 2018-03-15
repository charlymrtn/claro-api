<?php

namespace app\Http\Controllers\API\v1;

use Log;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaccion;
use App\Classes\Pagos\Parametros\PeticionCargo;
use App\Classes\Sistema\Mensaje;
use Webpatser\Uuid\Uuid;

class CargoController extends Controller
{

    protected $mTransaccion;
    protected $oMensaje;

    /**
     * Crea nueva instancia.
     *
     * @return void
     */
    public function __construct(Transaccion $transaccion, Mensaje $mensaje)
    {
        $this->mTransaccion = $transaccion;
        $this->oMensaje = $mensaje;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $oRequest)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $oRequest)
    {

        /**
         * @todo: IMPORTANTE: Versión para demo, cambiar pasando el demo!
         */

        // Valida datos de entrada
        $oValidator = Validator::make($oRequest->all(), [
            'tarjeta' => 'required|required|array',
                'tarjeta.nombre' => 'required|min:3|max:30',
                'tarjeta.pan' => 'required|min:10|max:18',
                'tarjeta.cvv2' => 'required|min:3|max:4',
                'tarjeta.expiracion_mes' => 'required|size:2',
                'tarjeta.expiracion_anio' => 'required|min:2|max:4',
                'tarjeta.device_fingerprint' => 'max:255',
            'monto' => 'required',
            'descripcion' => 'max:250',
            'pedido' => 'required|array',
                'pedido.id' => 'max:48',
                'pedido.direccion_envio' => 'array',
                    'pedido.direccion_envio.pais' => 'string|size:3',
                    'pedido.direccion_envio.estado' => 'string|size:3',
                    'pedido.direccion_envio.ciudad' => 'string|max:60',
                    'pedido.direccion_envio.municipio' => 'string|max:60',
                    'pedido.direccion_envio.linea1' => 'string|max:120',
                    'pedido.direccion_envio.linea2' => 'string|max:120',
                    'pedido.direccion_envio.linea3' => 'string|max:120',
                    'pedido.direccion_envio.cp' => 'string|max:10',
                    'pedido.direccion_envio.longitud' => 'numeric',
                    'pedido.direccion_envio.latitud' => 'numeric',
                'pedido.direccion_cargo' => 'required|array',
                    'pedido.direccion_envio.pais' => 'string|size:3',
                    'pedido.direccion_envio.estado' => 'string|size:3',
                    'pedido.direccion_envio.ciudad' => 'string|max:60',
                    'pedido.direccion_envio.municipio' => 'string|max:60',
                    'pedido.direccion_envio.linea1' => 'string|max:120',
                    'pedido.direccion_envio.linea2' => 'string|max:120',
                    'pedido.direccion_envio.linea3' => 'string|max:120',
                    'pedido.direccion_envio.cp' => 'string|max:10',
                    'pedido.direccion_envio.longitud' => 'numeric',
                    'pedido.direccion_envio.latitud' => 'numeric',
                'pedido.articulos' => 'numeric',
            'cliente' => 'required|array',
                'cliente.id' => 'required|string',
                'cliente.nombre' => 'required|min:3|max:30',
                'cliente.apellido_paterno' => 'required|min:3|max:30',
                'cliente.apellido_materno' => 'min:3|max:30',
                'cliente.email' => 'required|email',
                'cliente.telefono' => 'string',
                'cliente.direccion' => 'array',
                    'cliente.direccion.pais' => 'string|size:3',
                    'cliente.direccion.estado' => 'string|size:3',
                    'cliente.direccion.ciudad' => 'string|max:60',
                    'cliente.direccion.municipio' => 'string|max:60',
                    'cliente.direccion.linea1' => 'string|max:120',
                    'cliente.direccion.linea2' => 'string|max:120',
                    'cliente.direccion.linea3' => 'string|max:120',
                    'cliente.direccion.cp' => 'string|max:10',
                    'cliente.direccion.longitud' => 'numeric',
                    'cliente.direccion.latitud' => 'numeric',
                'cliente.creacion' => 'date',
            'parcialidades' => 'numeric|min:0|max:48',
            'comercio_uuid' => 'required|string',
        ]);
        if ($oValidator->fails()) {
            return ejsend_fail(['code' => 400, 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], 400, ['errors' => $oValidator->errors()]);
        }

        // Procesa y sanitiza datos de tarjeta
        $oTarjetaCredito = new \App\Classes\Pagos\Medios\TarjetaCredito($oRequest->input('tarjeta'));

        // Inicializa transaccion
        $aTrx = [
            'prueba' => false,
            'operacion' => 'pago',
            'monto' => $oRequest->input('monto'),
            'forma_pago' => 'tarjeta',
            'datos_pago' => json_encode([
                'nombre' => $oTarjetaCredito->nombre,
                'pan' => $oTarjetaCredito->pan,
                'pan_hash' => $oTarjetaCredito->pan_hash,
                'marca' => $oTarjetaCredito->marca,
            ]),
            // Comercio
            'comercio_orden_id' => $oRequest->input('pedido.id'),
            'datos_comercio' => json_encode([
                'pedido' => $oRequest->input('pedido'),
                'cliente' => $oRequest->input('cliente'),
            ]),
            'datos_antifraude' => json_encode([]),
            'datos_claropagos' => json_encode([]),
            'datos_procesador' => json_encode([]),
            'datos_destino' => json_encode([]),
            // Catálogos
            'comercio' => $oRequest->input('comercio_uuid'),
            'transaccion_estatus_id' => 4,
            'pais' => 'MEX',
            'moneda' => 'MXN',
            // Fechas
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // Validación de prueba
        if ($oRequest->input('prueba') === "real"){
            $aTrx['prueba'] = false;
        } else {
            $aTrx['prueba'] = true;
        }

        // Evalúa en Antifraude
        if ($oTarjetaCredito->_pan == '4111111111111111') {
            // Card OK 4111111111111111
            $aTrx['datos_antifraude'] = json_encode(['response_code' => '100', 'response_description' => 'Transaction OK', 'error' => false]);
        } if ($oTarjetaCredito->_pan == '4222222222222220') {
            // Card declined 4222222222222220
            $aTrx['estatus'] = 'declinada';
            $aTrx['datos_antifraude'] = json_encode(['error' => true, 'response_code' => '220', 'response_description' => 'Decline - Generic Decline.']);
        } if ($oTarjetaCredito->_pan == '4000000000000069') {
            // Card expired 4000000000000069 -> Declinada por fraude
            $aTrx['estatus'] = rechazada-antifraude;
            $aTrx['datos_antifraude'] = json_encode(['error' => true, 'response_code' => '205', 'response_description' => 'Decline - Stolen or lost card.']);
        } else {
            $aTrx['datos_antifraude'] = json_encode(['response_code' => '100', 'response_description' => 'Transaction OK', 'error' => false]);
        }

        // Procesa cargo
        if ($oTarjetaCredito->marca == 'amex') {
            // Procesa transacción con procesador de pagos
            $oProcesador = new \App\Classes\Pagos\Procesadores\Amex\InternetDirect();
            $aAmexPago = [
                'pan' => $oTarjetaCredito->_pan,
                'amount' => $oRequest->input('monto'),
                'datetime' => date('ymdhis'),
                'date_exp' => $oTarjetaCredito->expiracion_anio . $oTarjetaCredito->expiracion_mes,
                'cvv' => $oTarjetaCredito->cvv2,
                'direccion' => [
                    'cp' => $oRequest->input('pedido.direccion_cargo.cp'),
                    'linea1' => $oRequest->input('pedido.direccion_cargo.linea1'),
                    'nombre' => $oRequest->input('cliente.nombre'),
                    'apellido_paterno' => $oRequest->input('cliente.apellido_paterno'),
                    'apellido_materno' => $oRequest->input('cliente.apellido_materno'),
                    'telefono' => $oRequest->input('cliente.telefono'),
                ],
                'direccion_envio' => [
                    'cp' => $oRequest->input('pedido.direccion_envio.cp'),
                    'linea1' => $oRequest->input('pedido.direccion_envio.linea1'),
                    'nombre' => $oRequest->input('cliente.nombre'),
                    'apellido_paterno' => $oRequest->input('cliente.apellido_paterno'),
                    'apellido_materno' => $oRequest->input('cliente.apellido_materno'),
                    'telefono' => $oRequest->input('cliente.telefono'),
                ],
            ];
            $aTrx['datos_procesador'] = json_encode($oProcesador->sendTransaction($aAmexPago));
            $aTrx['estatus'] = 'completada'; // @todo: Cambiar acorde a la respuesta del procesador
        } else {
            // Procesa transacción con procesador de pagos
            $oProcesador = new \App\Classes\Pagos\Procesadores\Prosa\VentaManualService($aTrx['prueba']);
            $aProsaPago = [
                'nombre' => $oTarjetaCredito->nombre,
                'pan' => $oTarjetaCredito->_pan,
                'amount' => $oRequest->input('monto'),
                'datetime' => date('ymdhis'),
                'date_exp' => $oTarjetaCredito->expiracion_anio . $oTarjetaCredito->expiracion_mes,
                'cvv' => $oTarjetaCredito->cvv2,
                'direccion' => [
                    'cp' => $oRequest->input('pedido.direccion_cargo.cp'),
                    'linea1' => $oRequest->input('pedido.direccion_cargo.linea1'),
                    'nombre' => $oRequest->input('cliente.nombre'),
                    'apellido_paterno' => $oRequest->input('cliente.apellido_paterno'),
                    'apellido_materno' => $oRequest->input('cliente.apellido_materno'),
                    'telefono' => $oRequest->input('cliente.telefono'),
                ],
                'email' => $oRequest->input('cliente.email'),
                'productos' => [
                    [
                        'Quantity' => $oRequest->input('pedido.articulos'),
                        'description' => $oRequest->input('descripcion'),
                        'nombre' => $oRequest->input('cliente.nombre'),
                        'unitPrice' => $oRequest->input('monto'),
                        'amount' => $oRequest->input('monto'),
                        'Id' => $oRequest->input('pedido.id'),
                    ],
                ],
            ];
            //$aTrx['datos_procesador'] = json_encode($oProcesador->sendTransaction($aProsaPago['amount'], $aProsaPago['productos'], $aProsaPago['pan'], $aProsaPago['nombre'], $aProsaPago['cvv'], $aProsaPago['date_exp']));
            $aTrx['datos_procesador'] = '{"headers":{},"original":{"status":"success","data":{"message":"Venta generada correctamente","response_code":"00","importantData":{"orderId":25198,"authNum":"152099","transactionId":27172},"prueba":true}},"exception":null}';
            $aTrx['estatus'] = 'completada'; // @todo: Cambiar acorde a la respuesta del procesador
        }

        // Genera transacción
        $oTrx = $this->mTransaccion->create($aTrx);

        // Envía transacción a Admin y Clientes
        // @todo: Cambiar envío a tareas y mensajes únicamente para que ese sistema envíe estos mensajes a los otros sistemas.
        $oMensajeResultado = $this->oMensaje->envia('clientes', '/api/admin/transaccion', 'POST', $oTrx->toJson());
        $oMensajeResultado = $this->oMensaje->envia('admin', '/api/admin/transaccion', 'POST', $oTrx->toJson());

        // Regresa resultado
print $oTrx->toJson();

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
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        //
    }
}
