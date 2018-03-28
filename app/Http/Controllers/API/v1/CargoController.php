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

        // Encapsula, valida y formatea datos en PeticionCargo
        try {
            $oPeticionCargo = new PeticionCargo($oRequest->all());
        } catch (\Exception $e) {
            Log::error('Error on ' . __METHOD__ . ' line ' . __LINE__ . ':' . $e->getMessage());
            return ejsend_fail(['code' => $e->getCode() ?? '400', 'type' => 'Parámetros', 'message' => 'Error en parámetros de entrada.'], $e->getCode() ?? '400', ['errors' => $e->getMessage()]);
        }

        // @todo: Cambiar Procesadores\Amex\InternetDirect por Procesadores\sProcesadorAmex

        // Inicializa transaccion
        $aTrx = [
            'prueba' => $oPeticionCargo->prueba,
            'operacion' => 'pago',
            'monto' => $oPeticionCargo->monto,
            'forma_pago' => 'tarjeta',
            'datos_pago' => json_encode([
                'nombre' => $oPeticionCargo->tarjeta->nombre,
                'pan' => $oPeticionCargo->tarjeta->pan,
                'pan_hash' => $oPeticionCargo->tarjeta->pan_hash,
                'marca' => $oPeticionCargo->tarjeta->marca,
            ]),
            // Comercio
            'comercio_orden_id' => $oPeticionCargo->pedido['id'],
            'datos_comercio' => json_encode([
                'pedido' => $oPeticionCargo->pedido,
                'cliente' => $oPeticionCargo->cliente,
            ]),
            'datos_antifraude' => json_encode([]),
            'datos_claropagos' => json_encode([]),
            'datos_procesador' => json_encode([]),
            'datos_destino' => json_encode([]),
            // Catálogos
            'comercio' => $oPeticionCargo->comercio_uuid,
            'transaccion_estatus_id' => 4,
            'pais' => $oPeticionCargo->pedido['direccion_cargo']->pais,
            'moneda' => 'MXN',
            // Fechas
            'created_at' => date('Y-m-d H:i:s'),
        ];

//dump($oPeticionCargo->toArray());
//dump($aTrx);
//die;

//$oProcesador = new \App\Classes\Pagos\Procesadores\ProcesadorAmex();
//$oResultado = $oProcesador->carga($oPeticionCargo);
//dump($oResultado);
//die;


        // Evalúa en Antifraude
        if ($oPeticionCargo->tarjeta->_pan == '4111111111111111') {
            // Card OK 4111111111111111
            $aTrx['datos_antifraude'] = json_encode(['response_code' => '100', 'response_description' => 'Transaction OK', 'error' => false]);
        } if ($oPeticionCargo->tarjeta->_pan == '4222222222222220') {
            // Card declined 4222222222222220
            $aTrx['estatus'] = 'declinada';
            $aTrx['datos_antifraude'] = json_encode(['error' => true, 'response_code' => '220', 'response_description' => 'Decline - Generic Decline.']);
        } if ($oPeticionCargo->tarjeta->_pan == '4000000000000069') {
            // Card expired 4000000000000069 -> Declinada por fraude
            $aTrx['estatus'] = 'rechazada-antifraude';
            $aTrx['datos_antifraude'] = json_encode(['error' => true, 'response_code' => '205', 'response_description' => 'Decline - Stolen or lost card.']);
        } else {
            $aTrx['datos_antifraude'] = json_encode(['response_code' => '100', 'response_description' => 'Transaction OK', 'error' => false]);
        }

        // Procesa cargo
        if ($oPeticionCargo->tarjeta->marca == 'amex') {
            // Procesa transacción con procesador de pagos
            $oProcesador = new \App\Classes\Pagos\Procesadores\Amex\InternetDirect();
            $aAmexPago = [
                'pan' => $oPeticionCargo->tarjeta->_pan,
                'amount' => $oPeticionCargo->monto,
                'datetime' => date('ymdhis'),
                'date_exp' => $oPeticionCargo->tarjeta->expiracion_anio . $oPeticionCargo->tarjeta->expiracion_mes,
                'cvv' => $oPeticionCargo->tarjeta->cvv2,
                'direccion' => $oPeticionCargo->pedido['direccion_cargo'],
                'direccion_envio' => $oPeticionCargo->pedido['direccion_envio'],
            ];
            $aTrx['datos_procesador'] = json_encode($oProcesador->sendTransaction($aAmexPago));
            $aTrx['estatus'] = 'completada'; // @todo: Cambiar acorde a la respuesta del procesador
        } else {
            // Procesa transacción con procesador de pagos
            $oProcesador = new \App\Classes\Pagos\Procesadores\Prosa\VentaManualService($aTrx['prueba']);
            $aProsaPago = [
                'nombre' => $oPeticionCargo->tarjeta->nombre,
                'pan' => $oPeticionCargo->tarjeta->_pan,
                'amount' => $oPeticionCargo->monto,
                'datetime' => date('ymdhis'),
                'date_exp' => $oPeticionCargo->tarjeta->expiracion_anio . $oPeticionCargo->tarjeta->expiracion_mes,
                'cvv' => $oPeticionCargo->tarjeta->cvv2,
                'direccion' => $oPeticionCargo->pedido['direccion_cargo'],
                'email' => $oPeticionCargo->cliente['email'],
                'productos' => [
                    [
                        'Quantity' => $oPeticionCargo->pedido['articulos'],
                        'description' => $oPeticionCargo->descripcion,
                        'nombre' => $oPeticionCargo->cliente['nombre'],
                        'unitPrice' => $oPeticionCargo->monto,
                        'amount' => $oPeticionCargo->monto,
                        'Id' => $oPeticionCargo->pedido['id'],
                    ],
                ],
            ];
            $aTrx['datos_procesador'] = json_encode($oProcesador->sendTransaction($aProsaPago['amount'], $aProsaPago['productos'], $aProsaPago['pan'], $aProsaPago['nombre'], $aProsaPago['cvv'], $aProsaPago['date_exp']));
            //$aTrx['datos_procesador'] = '{"headers":{},"original":{"status":"success","data":{"message":"Venta generada correctamente","response_code":"00","importantData":{"orderId":25198,"authNum":"152099","transactionId":27172},"prueba":true}},"exception":null}';
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
