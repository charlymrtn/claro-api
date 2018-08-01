<?php

namespace App\Classes\Pagos\Procesos;

use App;
use Auth;
use Config;
use Exception;
use Carbon\Carbon;
use Webpatser\Uuid\Uuid;

use App\Models\Transaccion;
use App\Classes\Sistema\Mensaje;
use App\Classes\Pagos\Base\Error;
use App\Classes\Pagos\Parametros\PeticionCargo;
use App\Classes\Pagos\Parametros\RespuestaCargo;
use App\Classes\Pagos\Parametros\PeticionAntifraude;
use App\Classes\Pagos\Parametros\RespuestaAntifraude;

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
        // Inicializa variables
        $usuario = Auth::user();

        // 1. Inicializa transaccion
        $sUuid = Uuid::generate(4);
        $this->mTransaccion->create([
            'uuid' => $sUuid,
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
                'token' => $oPeticionCargo->tarjeta->token,
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
        $oTrx = $this->mTransaccion->find($sUuid);

        // 2. Evalua transacción con antifraude
        if ($oTrx->prueba) {
            // 2.1 Si es prueba, determina resultado dependiendo de la tarjeta
            if ($oPeticionCargo->tarjeta->pan_hash == 'd365f00d9f327b170214485dc6f72b22971521cb5647fa95f10ae827441ea7b0') {
                // Tarjeta rechazada por sistema antifraude 4000000000000044
                $oTrx->datos_antifraude = ['resultado' => 'rojo', 'score' => 90, 'codigo' => '220', 'descripcion' => 'Transacción muy riesgosa'];
                $oTrx->estatus = 'rechazada-antifraude';
                $oError = new Error([
                    'codigo' => '220',
                    'tipo' => 'Antifraude',
                    'descripcion' => 'Transacción muy riesgoza',
                ]);
            } else {
                $oTrx->datos_antifraude = ['resultado' => 'verde', 'score' => 25, 'codigo' => '100', 'descripcion' => 'Transaction de bajo riesgo'];
                $oTrx->estatus = 'aprobada-antifraude';
            }
        } else {
            // 2.2 @todo: Envía transacción a antifraude
            $oTrx->datos_antifraude = ['resultado' => 'verde', 'score' => 20, 'codigo' => '100', 'descripcion' => 'Transaction de bajo riesgo'];
            $oTrx->estatus = 'aprobada-antifraude';
        }
        $oTrx->save();

        // 3. Evalua si debe ser procesada la transacción
        if ($oTrx->estatus == 'aprobada-antifraude') {
            // 3.1 Evalua si es una prueba
            if ($oTrx->prueba) {
                // 3.1.1 Si es prueba, determina resultado dependiendo hash de tarjeta
                if ($oPeticionCargo->tarjeta->pan_hash == '9f88e9918393352639b4da04c8327e8af33d9433b09b3ebff7b070bb21c6cd87') {
                    // 4222222222222220 Tarjeta rechazada
                    $oTrx->estatus = 'rechazada-banco';
                    $oError = new Error(['codigo' => '05', 'tipo' => 'Banco', 'descripcion' => 'Rechazada por el banco']);
                    $oTrx->datos_procesador = ['status' => "fail", 'data' => ['message' => "Tarjeta rechazada. No se puede realizar el cargo.", "response_code" => "05", "importantData" => ["transactionId" => rand(100000, 999999), "prueba" => true]]];
                } else if ($oPeticionCargo->tarjeta->pan_hash == '19568e82af57bc0aa78afc6d720baadd1aec8a6796b5f15ae1362ce9453bbd3d') {
                    // 4000000000000069 Tarjeta expirada
                    $oTrx->estatus = 'rechazada-banco';
                    $oError = new Error(['codigo' => '54', 'tipo' => 'Banco', 'descripcion' => 'Tarjeta Expirada']);
                    $oTrx->datos_procesador = ['status' => "fail", 'data' => ['message' => "Tarjeta rechazada. No se puede realizar el cargo.", "response_code" => "54", "importantData" => ["transactionId" => rand(100000, 999999), "prueba" => true]]];
                } else if ($oPeticionCargo->tarjeta->pan_hash == 'd401ec386a00fd893ca2b55a5a4e9a5410265df6c2c4aa25886c68b6c76deedc') {
                    // 4444444444444448 Tarjeta sin fondos suficientes
                    $oTrx->estatus = 'rechazada-banco';
                    $oError = new Error(['codigo' => '51', 'tipo' => 'Banco', 'descripcion' => 'Saldo insuficiente']);
                    $oTrx->datos_procesador = ['status' => "fail", 'data' => ['message' => "Tarjeta rechazada. Tarjeta sin fondos suficientes.", "response_code" => "51", "importantData" => ["transactionId" => rand(100000, 999999), "prueba" => true]]];
                } else if ($oPeticionCargo->tarjeta->pan_hash == 'a0bb05ca01a2e6a38c3c8ea7f474a9841ea6e86f9a4f02d0e1dd1d8a56da15d1') {
                    // 4000000000000119 Tarjeta robada
                    $oTrx->estatus = 'rechazada-banco';
                    $oError = new Error(['codigo' => '43', 'tipo' => 'Banco', 'descripcion' => 'Recoger Tarjeta (Stolen Card)']);
                    $oTrx->datos_procesador = ['status' => "fail", 'data' => ['message' => "Tarjeta rechazada. Tarjeta reportada como robada.", "response_code" => "43", "importantData" => ["transactionId" => rand(100000, 999999), "prueba" => true]]];
                } else if ($oPeticionCargo->tarjeta->pan_hash == 'b15c095826c5c28840e7e9d3cec2e95fcfacc21d085e704e90c233697941a601') {
                    // 340000000000009 Tarjeta rechazada
                    $oTrx->estatus = 'rechazada-banco';
                    $oError = new Error(['codigo' => '05', 'tipo' => 'Banco', 'descripcion' => 'Rechazada por el banco']);
                    $oTrx->datos_procesador = ['status' => "fail", 'data' => ['message' => "Tarjeta rechazada. No se puede realizar el cargo.", "response_code" => "05", "importantData" => ["transactionId" => rand(100000, 999999), "prueba" => true]]];
                } else if ($oPeticionCargo->tarjeta->pan_hash == 'af50ee3a63d1619deb1c0361e89699f15be695023d510839cc1819a2627a7082') {
                    // 373737373737374 Tarjeta expirada
                    $oTrx->estatus = 'rechazada-banco';
                    $oError = new Error(['codigo' => '54', 'tipo' => 'Banco', 'descripcion' => 'Tarjeta expirada']);
                    $oTrx->datos_procesador = ['status' => "fail", 'data' => ['message' => "Tarjeta rechazada. No se puede realizar el cargo.", "response_code" => "54", "importantData" => ["transactionId" => rand(100000, 999999), "prueba" => true]]];
                } else if ($oPeticionCargo->tarjeta->pan_hash == '1927c2a318b0288088e995f17a5b8924b8284632efbb87997c3657d738ebaafd') {
                    // 370000000000002 Tarjeta sin fondos suficientes
                    $oTrx->estatus = 'rechazada-banco';
                    $oError = new Error(['codigo' => '51', 'tipo' => 'Banco', 'descripcion' => 'Saldo insuficiente']);
                    $oTrx->datos_procesador = ['status' => "fail", 'data' => ['message' => "Tarjeta rechazada. Tarjeta sin fondos suficientes.", "response_code" => "51", "importantData" => ["transactionId" => rand(100000, 999999), "prueba" => true]]];
                } else if ($oPeticionCargo->tarjeta->pan_hash == 'dddaa7c91adedadebec87310c5e83977cac4b4d0e924c6cc34fd8d947fbf4686') {
                    // 4111111111111111 Tarjeta aprobada visa
                    $oTrx->datos_procesador = ['status' => "success", 'data' => ['message' => "Venta generada correctamente", "response_code" => "00", "importantData" => ["orderId" => str_random(6), "authNum" => rand(100000, 999999), "transactionId" => rand(100000, 999999), "prueba" => true]]];
                    $oTrx->estatus = 'completada';
                } else if ($oPeticionCargo->tarjeta->pan_hash == 'c546462fd30ac7c17d2594e583ba8420cfd88e3e4914c0d88d40342f2e9fbfe0') {
                    // 5105105105105100 Tarjeta aprobada mastercard
                    $oTrx->datos_procesador = ['status' => "success", 'data' => ['message' => "Venta generada correctamente", "response_code" => "00", "importantData" => ["orderId" => str_random(6), "authNum" => rand(100000, 999999), "transactionId" => rand(100000, 999999), "prueba" => true]]];
                    $oTrx->estatus = 'completada';
                } else if ($oPeticionCargo->tarjeta->pan_hash == '428ed9725b7c878bcaedc4eb40b41fd345a4ea9b7ca42330a2a446cc0cc61f95') {
                    // 341111111111111 Tarjeta aprobada amex
                    $oTrx->datos_procesador = ['status' => "success", 'data' => ['message' => "Venta generada correctamente", "response_code" => "00", "importantData" => ["orderId" => str_random(6), "authNum" => rand(100000, 999999), "transactionId" => rand(100000, 999999), "prueba" => true]]];
                    $oTrx->estatus = 'completada';
                } else {
                    // Default - Tarjeta rechazada
                    $oTrx->estatus = 'rechazada-banco';
                    $oError = new Error(['codigo' => '05', 'tipo' => 'Banco', 'descripcion' => 'Rechazada por el banco']);
                    $oTrx->datos_procesador = ['status' => "fail", 'data' => ['message' => "Tarjeta rechazada. No se puede realizar el cargo.", "response_code" => "05", "importantData" => ["transactionId" => rand(100000, 999999), "prueba" => true]]];
                }
            } else {
                // 3.2 Define procesador a usar dependiendo de la afiliación y tarjeta proporcionada

                // @todo: Define afiliación a usar
                $cAfiliaciones = collect($usuario->getAfiliaciones());

                $oTrx->datos_procesador = $cAfiliaciones->toJson();
                $oTrx->estatus = 'completada';

//                if ($oPeticionCargo->tarjeta->marca == 'amex' && $cAfiliaciones->contains('procesador', 'amex')) {
//                    // @todo: Define afiliación a usar
//                    $oAfiliacion = $cAfiliaciones->firstWhere('procesador', 'amex');
//                    // Procesa transacción con procesador de pagos
//                    // @todo: Cambiar Procesadores\Amex\InternetDirect por Procesadores\sProcesadorAmex
//                    $oProcesador = new \App\Classes\Pagos\Procesadores\Amex\InternetDirect();
//                    // @todo: Define configuración de afiliación
//                    #$oProcesador->setAfiliacion($oAfiliacion);
//                    $aAmexPago = [
//                        'pan' => $oPeticionCargo->tarjeta->_pan,
//                        'amount' => $oPeticionCargo->monto,
//                        'datetime' => date('ymdhis'),
//                        'date_exp' => $oPeticionCargo->tarjeta->expiracion_anio . $oPeticionCargo->tarjeta->expiracion_mes,
//                        'cvv' => $oPeticionCargo->tarjeta->cvv2,
//                        //'direccion' => $oPeticionCargo->direccion_cargo,
//                        //'direccion_envio' => array_merge(
//                        //    $oPeticionCargo->pedido->direccion->toArray(),
//                        //    ['telefono' => $oPeticionCargo->pedido->direccion->telefono->numero]
//                        //),
//                    ];
//                    $oPago = $oProcesador->sendTransaction($aAmexPago);
//                    $oTrx->datos_procesador = json_decode(json_encode($oPago), true);
//                    if ($oPago->status == 'sucess') {
//                        $oTrx->estatus = 'completada';
//                    } else if (in_array($oPago->status, ['fail', 'failed'])) {
//                        $oTrx->estatus = 'rechazada-banco';
//                        $oError = new Error([
//                            'codigo' => '12',
//                            'tipo' => 'Banco',
//                            'descripcion' => 'No se puede realizar la venta',
//                        ]);
//                    } else {
//                        $oTrx->estatus = 'completada';
//                    }

//                } else if ($cAfiliaciones->contains('procesador', 'eglobal')) {
//                    // @todo: Define afiliación a usar
//                    $oAfiliacion = $cAfiliaciones->firstWhere('procesador', 'eglobal');
//                    // Procesa transacción con procesador de pagos
//                    // @todo: Cambiar Procesadores\Amex\InternetDirect por Procesadores\sProcesadorAmex
//                    $oProcesador = new \App\Classes\Pagos\Procesadores\Prosa\VentaManualService($oTrx->prueba);
//                    // @todo: Define configuración de afiliación
//                    #$oProcesador->setAfiliacion($oAfiliacion);
//                    $aProsaPago = [
//                        'nombre' => $oPeticionCargo->tarjeta->nombre,
//                        'pan' => $oPeticionCargo->tarjeta->_pan,
//                        'amount' => $oPeticionCargo->monto,
//                        'datetime' => date('ymdhis'),
//                        'date_exp' => $oPeticionCargo->tarjeta->expiracion_anio . $oPeticionCargo->tarjeta->expiracion_mes,
//                        'cvv' => $oPeticionCargo->tarjeta->cvv2,
//                        'direccion' => $oPeticionCargo->direccion_cargo,
//                        'email' => $oPeticionCargo->cliente->email,
//                        'productos' => [
//                            [
//                                'Quantity' => $oPeticionCargo->pedido->articulos,
//                                'description' => $oPeticionCargo->descripcion,
//                                'nombre' => $oPeticionCargo->cliente->nombre,
//                                'unitPrice' => $oPeticionCargo->monto,
//                                'amount' => $oPeticionCargo->monto,
//                                'Id' => $oPeticionCargo->pedido->id,
//                            ],
//                        ],
//                    ];
//                    $oPago = $oProcesador->sendTransaction($aProsaPago['amount'], $aProsaPago['productos'], $aProsaPago['pan'], $aProsaPago['nombre'], $aProsaPago['cvv'], $aProsaPago['date_exp'])->getData();
//                    $oTrx->datos_procesador = json_decode(json_encode($oPago), true);
//                    //$oTrx->datos_procesador = '{"status":"success","data":{"message":"Venta generada correctamente","response_code":"00","importantData":{"orderId":25198,"authNum":"152099","transactionId":27172},"prueba":true}}';
//                    if ($oPago->data->response_code == '00') {
//                        $oTrx->estatus = 'completada';
//                        //$oTrx->autorizacion = $oPago->data->importantData->authNum;
//                    } else {
//                        $oTrx->estatus = 'rechazada-banco';
//                        //$oTrx->mensaje = $oPago->data->message;
//                        //$oTrx->autorizacion = 0;
//                        $oError = new Error([
//                            'codigo' => '05',
//                            'tipo' => 'Banco',
//                            'descripcion' => 'Rechazada por el banco',
//                        ]);
//                    }

//                } else if ($cAfiliaciones->contains('procesador', 'prosa')) {
//                    // @todo: Define afiliación a usar
//                    $oAfiliacion = $cAfiliaciones->firstWhere('procesador', 'prosa');
//                    // Procesa transacción con procesador de pagos
//                    // @todo: Cambiar Procesadores\Amex\InternetDirect por Procesadores\sProcesadorAmex
//                    $oProcesador = new \App\Classes\Pagos\Procesadores\Prosa\VentaManualService($oTrx->prueba);
//                    // @todo: Define configuración de afiliación
//                    #$oProcesador->setAfiliacion($oAfiliacion);
//                    $aProsaPago = [
//                        'nombre' => $oPeticionCargo->tarjeta->nombre,
//                        'pan' => $oPeticionCargo->tarjeta->_pan,
//                        'amount' => $oPeticionCargo->monto,
//                        'datetime' => date('ymdhis'),
//                        'date_exp' => $oPeticionCargo->tarjeta->expiracion_anio . $oPeticionCargo->tarjeta->expiracion_mes,
//                        'cvv' => $oPeticionCargo->tarjeta->cvv2,
//                        'direccion' => $oPeticionCargo->direccion_cargo,
//                        'email' => $oPeticionCargo->cliente->email,
//                        'productos' => [
//                            [
//                                'Quantity' => $oPeticionCargo->pedido->articulos,
//                                'description' => $oPeticionCargo->descripcion,
//                                'nombre' => $oPeticionCargo->cliente->nombre,
//                                'unitPrice' => $oPeticionCargo->monto,
//                                'amount' => $oPeticionCargo->monto,
//                                'Id' => $oPeticionCargo->pedido->id,
//                            ],
//                        ],
//                    ];
//                    $oPago = $oProcesador->sendTransaction($aProsaPago['amount'], $aProsaPago['productos'], $aProsaPago['pan'], $aProsaPago['nombre'], $aProsaPago['cvv'], $aProsaPago['date_exp'])->getData();
//                    $oTrx->datos_procesador = json_decode(json_encode($oPago), true);
//                    //$oTrx->datos_procesador = '{"status":"success","data":{"message":"Venta generada correctamente","response_code":"00","importantData":{"orderId":25198,"authNum":"152099","transactionId":27172},"prueba":true}}';
//                    if ($oPago->data->response_code == '00') {
//                        $oTrx->estatus = 'completada';
//                        //$oTrx->autorizacion = $oPago->data->importantData->authNum;
//                    } else {
//                        $oTrx->estatus = 'rechazada-banco';
//                        //$oTrx->mensaje = $oPago->data->message;
//                        //$oTrx->autorizacion = 0;
//                        $oError = new Error([
//                            'codigo' => '05',
//                            'tipo' => 'Banco',
//                            'descripcion' => 'Rechazada por el banco',
//                        ]);
//                    }
//                }
            }
        }
        $oTrx->save();

        // 4. Envía transacción a Admin y Clientes
        // @todo: Cambiar envío a tareas y mensajes únicamente para que ese sistema envíe estos mensajes a los otros sistemas.
        $oMensajeResultadoA = $this->oMensaje->envia('clientes', '/api/admin/transaccion', 'POST', $oTrx->toJson());
        #dump($oMensajeResultadoA);
        $oMensajeResultadoB = $this->oMensaje->envia('admin', '/api/admin/transaccion', 'POST', $oTrx->toJson());
        #dump($oMensajeResultadoB);

        // 5. Regresa resultado en RespuestaCargo
        $aRespuesta = [
            'id' => $oTrx->uuid,
            'monto' => $oTrx->monto,
            'autorizacion' => '',
            'tipo' => 'cargo',
            'fecha' => $oTrx->created_at,
            'orden_id' => $oPeticionCargo->pedido->id,
            'cliente_id' => $oPeticionCargo->cliente->id,
            'estatus' => $oTrx->estatus,
            'prueba' => $oTrx->prueba,
        ];
        if (isset($oError)) {
            $aRespuesta['error'] = $oError;
        }
        $oRespuestaCargo = new RespuestaCargo($aRespuesta);
        // Prepara tarjeta en respuesta
        $oRespuestaTarjeta = $oPeticionCargo->tarjeta;
        unset($oRespuestaTarjeta->pan_hash);
        $oRespuestaCargo->tarjeta = $oRespuestaTarjeta;

        #dump($oRespuestaCargo->toArray());
        return $oRespuestaCargo;
    }

    // }}}
}