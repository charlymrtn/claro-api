<?php

namespace app\Http\Controllers\Bbva;

use Log;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaccion;
use App\Classes\Pagos\Parametros\PeticionCargo;
//use App\Classes\Sistema\Mensaje;
use Webpatser\Uuid\Uuid;
use App\Classes\Pagos\Procesadores\Bbva\Mensaje;


require public_path('b.php');
use app\Prueba\Bbva\BbvaTest;


class BbvaController extends Controller
{

    /**
     * Crea nueva instancia.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $oRequest)
    {
        $sPrueba = $oRequest->input('prueba', '1');
        $sTipo = $oRequest->input('tipo', 'envio_online');
        $sAccion = $oRequest->input('accion', 'prueba');
        $sTrxReq = base64_decode(urldecode($oRequest->input('trx_request', null)));
        $sTrxResp = base64_decode(urldecode($oRequest->input('trx_response', null)));
        // Prepara prueba
        $oBbvaTest = new BbvaTest();
        try {
            $oResultado = $oBbvaTest->pruebas($sPrueba, $sTipo, $sAccion, $sTrxReq, $sTrxResp);
        } catch (\Exception $e) {
            $iCode = $e->getCode();
            if (empty($iCode)) {
                $iCode = 520;
            }
            return ejsend_error(['code' => $iCode, 'type' => 'Sistema', 'message' => $e->getMessage() . ' ' . $e->getLine()], $iCode);
        }

        if (in_array($sTipo, ['datos_json', 'envio_json'])) {
            // Obtiene los datos a enviar de la prueba
            return ejsend_success($oResultado);
        } else if ($sTipo == 'envio_online') {
            echo "\n<br>Ejecutando prueba {$sPrueba}...";
            return $oResultado;
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
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $oRequest)
    {

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



    public function batch()
    {
        $i = 1;

        $batch_header_1 = [

            sprintf("%06s", $i++), // [6] Número de registro
            '1', // [1] Tipo de registro. Fijo: 1
            'EGLO', // [4] Número de sistema. Fijo: EGLO
            '180420', // [6] Fecha de transmisión (AAMMDD)
            'STS     ', // [8] Nombre de la cadena (convenido con BBVA Bancomer)
            '01', // [2] Número NN fijo. Número asignado por BBVA Bancomer a la Interred
            '01', // [2] Número de ventana que corresponde en el día
            '01.30', // [5] Versión del Formato del archivo. Fijo: 01.30
            'cpg1200418', // [8] Nombre físico del presente archivo
            '00', // [2] Sentido del archivo: 00 = viaja de la Interred hacia BBVA Bancomer 01 = viaja de BBVA Bancomer hacia la Interred
            '                                                                             ', // [77] Espacios para uso futuro
        ];
        $batch_header_2 = [
            sprintf("%06s", $i++), // [6] Número consecutivo de registro
            '2', // [1] Tipo de registro. Fijo: 2
            '04372512', // [8] Identificador propio del Negocio por parte de la Interred
            '04372512', // [8] Número que BBVA Bancomer otorga al negocio para su identificación
            '00000000', // [8] Número de referencia a la cuenta de cheques. Es opcional y solo informativo
            '000', // [3] Número de la sucursal (opcional y solo informativo)
            '0000000', // [7] Número de la cuenta de cheques  (opcional y solo es informativo
            '00000000', // [8] Número de referencia de cargos parciales
            '                                                                        ', // [72] Espacios para uso futuro
        ];


        $batch_line = [
            '000003', // [6] Número consecutivo de registro
            '3', // [1] Tipo de registro. Fijo 3
            '161019', // [6] Fecha valor del pagare (AAMMDD)
            '1', // [1] Clave de la operación del Pagaré 1= Compra On-Line (Aut.Bnmx) 2= Compra On-Line (Aut.Cadena) 3= Compra Off-Line 4= Devolución 5= Compra diferida On-Line 6= Devolución Diferida Off-Line
            '125085', // [6] Número con el cual autorizó BBVA Bancomer
            '0000011100', // [10] Monto de la transacción
            // [23] Número único para la localización de pagares “
                '7455546', // [7] 7455546” = Valor Fijo.
                '6', // [1] “A” = último dígito del año.
                '293', // [3] “DDD = fecha juliana.
                '00600300023', // [11] “99999999999” = 11 posiciones libre numérico.
                '9', // [1]  “1” = dígito verificador ( módulo 10)
            '4772133014153321   ', // [19] Número de tarjeta (BBVA Bancomer, Visa, MC, BBVA Bancomer, Carnet, Amex)
            '0000', // [4] Motivo de rechazo del movimiento. Campo exclusivo para BBVA Bancomer. La interred debe enviar el valor fijo: 0000
            '0000', // [4] Estatus del registro. La interred debe enviar el valor fijo: 0000
            '102130', // [6] Hora en que se efectúo la transacción (HHMMSS)
            '0000', // [4] Indicador de Comercio Electrónico: 0 = No es transacción de Comercio Electrónico 5 =Transacción electrónica segura con certificado del tarjetahabiente 6 =Transacción electrónica segura sin certificado del tarjetahabiente, con certificado del negocio 7 =Transacción de comercio electrónico sin certificados, con canal encriptado 8 = Transacción de comercio electrónico No segura
            '01', // [2] Modo de ingreso de datos de la cuentas emisora: “00” Información desconocida “01” Información digitada “05” Información obtenida de tarjeta con chip. “07” Contactless “80” FallBack  “90” Información obtenida de la banda magnética del plástico “91” Contactless MSD (banda magnética)
            '000000000', // [9] Cash back 7 enteros 2 decimales
            '1', // [1] Capacidad de la terminal para lectura de datos: '1' No hay terminal (elaborado manualmente) '2' Con lectora de banda '3' Contactless  '4' Lector de caracteres ópticos '5' Lector de CHIP(circuito integrado)
            '00', // [2] Plan a utilizarse '00' Sin plan '03' Plan sin intereses '05' Plan con intereses '07' Compre hoy pague después
            '00', // [2] Número de meses en que el pago no se hace exigible(Compre hoy, pague después)
            '00', // [2] Número de meses en que se van a dividir los pagos (con o sin intereses), justificado con ceros a la izquierdo.
            '000000', // [6] Número de boleta utilizado en la transacción
            '0000', // [4] Número de terminal donde se efectuó la transacción
            '000', // [3] Número de tienda donde se efectuó la transacción
        ];

        $respuesta[1] = json_decode('{"3":"180000","4":"000000183000","7":"0419141337","11":"117691","12":"091337","13":"0419","15":"0419","17":"0419","22":"012","25":"59","35":"214772135000003584=2005","37":"180419091337","38":"575196","39":"00","41":"0000CP01        ","48":"0275462742            00000000","49":"484","58":"1390000001830000000018300010000098170000001000000000010000000      000000000000000000000000000000000000000000002010000000000015200009817004840","60":"000","63":"124& 0000600124! Q100002 00! Q200002 09! C400012 102510003600! 0400020             Y       ! C000026 340  001          7  1 0 0"}', true);
        $respuesta[2] = json_decode('{"3":"000000","4":"000000022300","7":"0419141937","11":"180514","12":"091937","13":"0419","15":"0419","17":"0419","22":"012","25":"59","35":"214152315000122697=2005","37":"180419091937","38":"575198","39":"00","41":"0000CP01        ","48":"0275462742            00000000","49":"484","60":"000","63":"124& 0000600124! Q100002 00! Q200002 09! C400012 102510003600! 0400020             O       ! C000026 427  001          7  1 0 0"}', true);
        $respuesta[3] = json_decode('{"3":"000000","4":"000000053255","7":"0419142145","11":"192978","12":"092145","13":"0419","15":"0419","17":"0419","22":"012","25":"59","35":"214152315000122697=2005","37":"180419092145","38":"575200","39":"00","41":"0000CP01        ","48":"0275462742            00000000","49":"484","60":"000","63":"124& 0000600124! Q100002 00! Q200002 09! C400012 102510003600! 0400020             O       ! C000026 427  001          7  1 0 0"}', true);
        $respuesta[4] = json_decode('{"3":"180000","4":"000000072800","7":"0419142236","11":"343342","12":"092236","13":"0419","15":"0419","17":"0419","22":"012","25":"59","35":"214772135000003584=2005","37":"180419092236","38":"575201","39":"00","41":"0000CP01        ","48":"0275462742            00000000","49":"484","58":"1390000000728000000007280010000097442000000981700000009817000      000000000000000000000000000000000000000000002010000000000015200009744204840","60":"000","63":"124& 0000600124! Q100002 00! Q200002 09! C400012 102510003600! 0400020             Y       ! C000026 340  001          7  1 0 0"}', true);
        $respuesta[5] = json_decode('{"3":"180000","4":"000000041200","7":"0419142315","11":"585003","12":"092315","13":"0419","15":"0419","17":"0419","22":"012","25":"59","35":"214772135000003584=2005","37":"180419092315","38":"000000","39":"49","41":"0000CP01        ","48":"0275462742            00000000","49":"484","60":"000","63":"124& 0000600124! Q100002 9 ! Q200002 09! C400012 102510003600! 0400020             D       ! C000026 123  001          7  1 0 0"}', true);
        $respuesta[7] = json_decode('{"3":"180000","4":"000000081200","7":"0419142519","11":"834170","12":"092519","13":"0419","15":"0419","17":"0419","22":"012","25":"59","35":"214152315000122697=2005","37":"180419092519","38":"000000","39":"49","41":"0000CP01        ","48":"0275462742            00000000","49":"484","60":"000","63":"124& 0000600124! Q100002 9 ! Q200002 09! C400012 102510003600! 0400020             O       ! C000026 456  001          7  1 0 0"}', true);
        $respuesta[8] = '{"3":"000000","4":"000000022750","7":"0419153933","11":"637287","12":"103933","13":"0419","15":"0419","17":"0419","22":"012","25":"59","35":"215413330089020011=2512","37":"180419103933","38":"005613","39":"00","41":"0000CP01        ","48":"0275462742            00000000","49":"484","60":"000","63":"124& 0000600124! Q100002 9 ! Q200002 09! C400012 102510003600! 0400020                     ! C000026 601  001          7  1 0  "}';
        $respuesta[9] = '{"3":"000000","4":"000000030200","7":"0419154015","11":"615525","12":"104015","13":"0419","15":"0419","17":"0419","22":"012","25":"59","35":"215413330089020078=2512","37":"180419104015","38":"005614","39":"00","41":"0000CP01        ","48":"0275462742            00000000","49":"484","60":"000","63":"124& 0000600124! Q100002 9 ! Q200002 09! C400012 102510003600! 0400020                     ! C000026 201  001          7  1 0  "}';
        $respuesta[10] = '{"3":"000000","4":"000000015600","7":"0419154127","11":"967890","12":"104127","13":"0419","15":"0419","17":"0419","22":"012","25":"59","35":"215413330089020086=2512","37":"180419104127","38":"005615","39":"00","41":"0000CP01        ","48":"0275462742            00000000","49":"484","60":"000","63":"124& 0000600124! Q100002 9 ! Q200002 09! C400012 102510003600! 0400020                     ! C000026 201  001          7  1 0  "}';

        $registros = 0;
        $totales = [
            'compras' => 0,
            'monto_compras' => 0,
            'cargos' => 0,
            'monto_cargos' => 0,
            'negocio' => 0,
        ];
        $batch_linea = [];
        foreach($respuesta as $res) {
            if ($res[39] == '00') {
                $linea = [
                    sprintf("%06s", $i++), // [6] Número consecutivo de registro
                    '3', // [1] Tipo de registro. Fijo 3
                    substr($res[37], 0, 6), // [6] Fecha valor del pagare (AAMMDD)
                    '1', // [1] Clave de la operación del Pagaré 1= Compra On-Line (Aut.Bnmx) 2= Compra On-Line (Aut.Cadena) 3= Compra Off-Line 4= Devolución 5= Compra diferida On-Line 6= Devolución Diferida Off-Line
                    $res[38] ?? '000000', // [6] Número con el cual autorizó BBVA Bancomer
                    substr($res[4], 2), // [10] Monto de la transacción
                    // [23] Número único para la localización de pagares “
                        '7455546', // [7] 7455546” = Valor Fijo.
                        substr($res[37], 1, 1), // [1] “A” = último dígito del año.
                        date("z", strtotime(substr($res[37], 0, 2) . '-' . substr($res[37], 2, 2) . '-' . substr($res[37], 4, 2))), // [3] “DDD = fecha juliana.
                        sprintf("%011s", $res[11]), // [11] “99999999999” = 11 posiciones libre numérico.
                        $this->calcLuhn($res[11]), // [1]  “1” = dígito verificador ( módulo 10)
                    sprintf("%- 19s", substr($res[35], 2, strpos($res[35], '=') - 2)), // [19] Número de tarjeta (BBVA Bancomer, Visa, MC, BBVA Bancomer, Carnet, Amex)
                    '0000', // [4] Motivo de rechazo del movimiento. Campo exclusivo para BBVA Bancomer. La interred debe enviar el valor fijo: 0000
                    '0000', // [4] Estatus del registro. La interred debe enviar el valor fijo: 0000
                    substr($res[37], 6, 6), // [6] Hora en que se efectúo la transacción (HHMMSS)
                    '0007', // [4] Indicador de Comercio Electrónico: 0 = No es transacción de Comercio Electrónico 5 =Transacción electrónica segura con certificado del tarjetahabiente 6 =Transacción electrónica segura sin certificado del tarjetahabiente, con certificado del negocio 7 =Transacción de comercio electrónico sin certificados, con canal encriptado 8 = Transacción de comercio electrónico No segura
                    '01', // [2] Modo de ingreso de datos de la cuentas emisora: “00” Información desconocida “01” Información digitada “05” Información obtenida de tarjeta con chip. “07” Contactless “80” FallBack  “90” Información obtenida de la banda magnética del plástico “91” Contactless MSD (banda magnética)
                    '000000000', // [9] Cash back 7 enteros 2 decimales
                    '1', // [1] Capacidad de la terminal para lectura de datos: '1' No hay terminal (elaborado manualmente) '2' Con lectora de banda '3' Contactless  '4' Lector de caracteres ópticos '5' Lector de CHIP(circuito integrado)
                    '00', // [2] Plan a utilizarse '00' Sin plan '03' Plan sin intereses '05' Plan con intereses '07' Compre hoy pague después
                    '00', // [2] Número de meses en que el pago no se hace exigible(Compre hoy, pague después)
                    '00', // [2] Número de meses en que se van a dividir los pagos (con o sin intereses), justificado con ceros a la izquierdo.
                    '000000', // [6] Número de boleta utilizado en la transacción
                    '0000', // [4] Número de terminal donde se efectuó la transacción
                    '000', // [3] Número de tienda donde se efectuó la transacción
                ];
                $batch_linea[] = implode('', $linea) . "\n";
                $registros += 1;
                if ($res[3] == '180000' || $res[3] == '000000') {
                    $totales['compras'] += 1;
                    $totales['monto_compras'] += $res[4];
//                } else if ($res[3] == '000000') {
//                    $totales['cargos'] += 1;
//                    $totales['monto_cargos'] += $res[4];
                }
            }
        }

        $batch_footer_1 = [
            sprintf("%06s", $i), // [6] Número consecutivo de registro
            '4', // [1] Tipo de registro. Fijo: 4
            sprintf("%07s", $totales['compras']), // [7] Número de compras en el archivo
            sprintf("%014s", $totales['monto_compras']), // [14] Monto de las compras del archivo
            sprintf("%07s", $totales['cargos']), // [7] Número de cargos del archivo
            sprintf("%014s", $totales['monto_cargos']), // [14] Monto de los cargos del archivo
            sprintf("%06s", $totales['negocio']), // [6] Número de Header`s de Negocio (registros tipo 2) y de Detalle (registros tipo3)
            '000000', // [6] Hora en que BBVA Bancomer recibió el archivo  de entrada (HHMMSS). La Interred enviará el valor fijo: 000000
            '000000', // [6] Hora en que BBVA Bancomer finalizó el proceso del archivo de entrada y generó el archivo de Retorno con los rechazos (HHMMSS). La Interred enviará el valor fijo: 000000
            '                                                      ', // [54] Espacios para uso futuro
        ];

        $batch = implode('', $batch_header_1) . "\n" . implode('', $batch_header_2) . "\n" . implode('', $batch_linea) . implode('', $batch_footer_1);

        return $batch;

    }

    private function calcLuhn(string $sNum): int
    {
        $sChecksum = '';
        foreach (str_split(strrev($sNum . '0')) as $i => $d) {
            $sChecksum .= $i % 2 !== 0 ? $d * 2 : $d;
        }
        $iDigit = strrev((string) array_sum(str_split($sChecksum)))[0];
        return (10 - $iDigit);
    }

}
