<?php

namespace App\Classes\Pagos\Procesos;

use App;
use Config;
use Exception;
use Carbon\Carbon;

use App\Models\Transaccion;
use App\Classes\Sistema\Mensaje;
use App\Classes\Pagos\Parametros\PeticionCargo;

/**
 * Procesador de pagos para American Express
 */
class Cargo
{
    // {{{ properties

    /**
     * @var Transaccion Objeto Transaccion
     */
    protected $mTransaccion;

    /**
     * @var Mensaje Objeto Mensaje
     */
    protected $oMensaje;

    /**
     * @var PeticionCargo Objeto PeticionCargo
     */
    protected $oComercio;

    // }}}}

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos protegidos
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ protected functions

    // }}}

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos privados
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ private functions

    /**
     * Inicializa procesador de pagos.
     */
    private function inicializa(): void
    {
        // Define parámetros default de la configuración Claro Pagos
        $aParametros = [
            'api_url' => Config::get('claropagos.' . App::environment() . '.procesadores_pago.amex.api_url'),
            'origin' => Config::get('claropagos.' . App::environment() . '.procesadores_pago.amex.origin'),
        ];
        // Reemplaza y agrega parámetros del procesador de pagos
        $this->setParametros($aParametros);
        // Inicializa configuración del procesador de pagos

    }

    // }}}

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos públicos
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ public functions

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
     * Realiza cargo
     *
     * @param PeticionCargo $oPeticionCargo
     * @return void
     */
    public function carga(PeticionCargo $oPeticionCargo)
    {
        //
        // Inicializa transaccion
        $oTrx = $this->mTransaccion->create([
            'prueba' => $oPeticionCargo->prueba,
            'operacion' => 'pago',
            'monto' => $oPeticionCargo->monto,
            'forma_pago' => 'tarjeta',
            'estatus' => 'pendiente',
            'datos_pago' => [
                'nombre' => $oPeticionCargo->tarjeta->nombre,
                'pan' => $oPeticionCargo->tarjeta->pan,
                'pan_hash' => $oPeticionCargo->tarjeta->pan_hash,
                'marca' => $oPeticionCargo->tarjeta->marca,
            ],
            // Comercio
            'datos_comercio' => [
                'pedido' => $oPeticionCargo->pedido,
                'cliente' => $oPeticionCargo->cliente,
            ],
            // Claropagos
            'datos_claropagos' => [],
            // Eventos
            'datos_antifraude' => [],
            'datos_procesador' => [],
            'datos_destino' => [],
            // Catálogos
            'comercio_uuid' => $oPeticionCargo->comercio_uuid,
            'transaccion_estatus_id' => 4,
            'pais' => $oPeticionCargo->direccion_cargo->pais,
            'moneda' => 'MXN',
        ]);
        // Guarda transacción
        $oTrx->save();

        // Envía transacción a Admin y Clientes
        // @todo: Cambiar envío a tareas y mensajes únicamente para que ese sistema envíe estos mensajes a los otros sistemas.
        $oMensajeResultado = $this->oMensaje->envia('clientes', '/api/admin/transaccion', 'POST', $oTrx->toJson());
        dump($oMensajeResultado);
        $oMensajeResultado = $this->oMensaje->envia('admin', '/api/admin/transaccion', 'POST', $oTrx->toJson());
        dump($oMensajeResultado);

        // @todo: Carga configuración de afiliaciones y ruteo de transacciones del comercio

return $oTrx->toJson();

        // Define procesador a usar dependiendo de la afiliación

////$oProcesador = new \App\Classes\Pagos\Procesadores\ProcesadorAmex();
////$oResultado = $oProcesador->carga($oPeticionCargo);
//
//        // Evalúa en Antifraude
//        if ($oPeticionCargo->tarjeta->_pan == '4111111111111111') {
//            // Card accepted
//            $aTrx['estatus'] = 'completada';
//            $aTrx['datos_antifraude'] = json_encode(['response_code' => '100', 'response_description' => 'Transaction OK', 'error' => false]);
//            $aTrx['datos_procesador'] = '{"status":"success","data":{"message":"Venta generada correctamente","response_code":"00","importantData":{"orderId":25198,"authNum":"152099","transactionId":27172},"prueba":true}}';
//        } else if ($oPeticionCargo->tarjeta->_pan == '4222222222222220') {
//            // Card declined 4222222222222220
//            $aTrx['estatus'] = 'rechazada-antifraude';
//            $aTrx['datos_antifraude'] = json_encode(['error' => true, 'response_code' => '220', 'response_description' => 'Decline - Generic Decline.']);
//        } else if ($oPeticionCargo->tarjeta->_pan == '4000000000000069') {
//            // Card expired 4000000000000069 -> Declinada por fraude
//            $aTrx['estatus'] = 'rechazada-antifraude';
//            $aTrx['datos_antifraude'] = json_encode(['error' => true, 'response_code' => '205', 'response_description' => 'Decline - Stolen or lost card.']);
//        } else {
//            $aTrx['datos_antifraude'] = json_encode(['response_code' => '100', 'response_description' => 'Transaction OK', 'error' => false]);
//
//            // Procesa cargo
//            if ($oPeticionCargo->tarjeta->marca == 'amex') {
//                // Procesa transacción con procesador de pagos
//                $oProcesador = new \App\Classes\Pagos\Procesadores\Amex\InternetDirect();
//                $aAmexPago = [
//                    'pan' => $oPeticionCargo->tarjeta->_pan,
//                    'amount' => $oPeticionCargo->monto,
//                    'datetime' => date('ymdhis'),
//                    'date_exp' => $oPeticionCargo->tarjeta->expiracion_anio . $oPeticionCargo->tarjeta->expiracion_mes,
//                    'cvv' => $oPeticionCargo->tarjeta->cvv2,
////                    'direccion' => $oPeticionCargo->direccion_cargo,
////                    'direccion_envio' => array_merge(
////                        $oPeticionCargo->pedido->direccion->toArray(),
////                        ['telefono' => $oPeticionCargo->pedido->direccion->telefono->numero]
////                    ),
//                ];
//                $oPago = $oProcesador->sendTransaction($aAmexPago);
//                $aTrx['datos_procesador'] = json_encode($oPago);
//                if ($oPago->status == 'sucess') {
//                    $aTrx['estatus'] = 'completada';
//                } else if (in_array($oPago->status, ['fail', 'failed'])) {
//                    $aTrx['estatus'] = 'rechazada-banco';
//                } else {
//                    $aTrx['estatus'] = 'completada';
//                }
//            } else {
//                // @todo: Cambiar Procesadores\Amex\InternetDirect por Procesadores\sProcesadorAmex
//
//                // Procesa transacción con procesador de pagos
//                $oProcesador = new \App\Classes\Pagos\Procesadores\Prosa\VentaManualService($aTrx['prueba']);
//                $aProsaPago = [
//                    'nombre' => $oPeticionCargo->tarjeta->nombre,
//                    'pan' => $oPeticionCargo->tarjeta->_pan,
//                    'amount' => $oPeticionCargo->monto,
//                    'datetime' => date('ymdhis'),
//                    'date_exp' => $oPeticionCargo->tarjeta->expiracion_anio . $oPeticionCargo->tarjeta->expiracion_mes,
//                    'cvv' => $oPeticionCargo->tarjeta->cvv2,
//                    'direccion' => $oPeticionCargo->direccion_cargo,
//                    'email' => $oPeticionCargo->cliente->email,
//                    'productos' => [
//                        [
//                            'Quantity' => $oPeticionCargo->pedido->articulos,
//                            'description' => $oPeticionCargo->descripcion,
//                            'nombre' => $oPeticionCargo->cliente->nombre,
//                            'unitPrice' => $oPeticionCargo->monto,
//                            'amount' => $oPeticionCargo->monto,
//                            'Id' => $oPeticionCargo->pedido->id,
//                        ],
//                    ],
//                ];
//                $oPago = $oProcesador->sendTransaction($aProsaPago['amount'], $aProsaPago['productos'], $aProsaPago['pan'], $aProsaPago['nombre'], $aProsaPago['cvv'], $aProsaPago['date_exp'])->getData();
//                $aTrx['datos_procesador'] = json_encode($oPago);
//                //$aTrx['datos_procesador'] = '{"status":"success","data":{"message":"Venta generada correctamente","response_code":"00","importantData":{"orderId":25198,"authNum":"152099","transactionId":27172},"prueba":true}}';
//                if ($oPago->data->response_code == '00') {
//                    $aTrx['estatus'] = 'completada';
//                    //$aTrx['autorizacion'] = $oPago->data->importantData->authNum;
//                } else {
//                    $aTrx['estatus'] = 'rechazada-banco';
//                    //$aTrx['mensaje'] = $oPago->data->message;
//                    //$aTrx['autorizacion'] = 0;
//                }
//            }
//        }
//
//        // Genera transacción
//        $oTrx = $this->mTransaccion->create($aTrx);
//
//        // Envía transacción a Admin y Clientes
//        // @todo: Cambiar envío a tareas y mensajes únicamente para que ese sistema envíe estos mensajes a los otros sistemas.
//        $oMensajeResultado = $this->oMensaje->envia('clientes', '/api/admin/transaccion', 'POST', $oTrx->toJson());
//        $oMensajeResultado = $this->oMensaje->envia('admin', '/api/admin/transaccion', 'POST', $oTrx->toJson());
//
//        // Regresa resultado
//print $oTrx->toJson();
    }

    // }}}
}