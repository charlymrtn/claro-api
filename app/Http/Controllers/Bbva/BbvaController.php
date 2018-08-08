<?php

namespace app\Http\Controllers\Bbva;

use Log;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Webpatser\Uuid\Uuid;
use App\Http\Controllers\Controller;
use App\Models\Transaccion;
//use App\Classes\Sistema\Mensaje;
use App\Classes\Pagos\Parametros\PeticionCargo;
use App\Classes\Pagos\Procesadores\Bbva\Mensaje;
use App\Classes\Pagos\Procesadores\Bbva\Interred as BBVAInterred;


use App;
use Carbon\Carbon;
use App\Classes\Sistema\Mensaje as MensajeCP;
use App\Classes\Pagos\Medios\TarjetaCredito;
use App\Classes\Pagos\Base\PlanPago;

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



    public function batch(string $fecha)
    {
        $i = 1;
        $sFecha = $fecha ?? '2018-08-02';
        $dFecha = strtotime($sFecha);
        $fecha_ymd = date('ymd', $dFecha);

        $sFechaProc = strtotime("+1 day", $dFecha);
        $fecha_proc_ymd = date('ymd', $sFechaProc);
        $batch_filename = 'rmd1' . date('dmy', $sFechaProc) . '.txt';

        $batch_header_1 = [
            sprintf("%06s", $i++), // [6] Número de registro
            '1', // [1] Tipo de registro. Fijo: 1
            'EGLO', // [4] Número de sistema. Fijo: EGLO
            $fecha_proc_ymd, // [6] Fecha de transmisión (AAMMDD)
            'STS     ', // [8] Nombre de la cadena (convenido con BBVA Bancomer)
            '01', // [2] Número NN fijo. Número asignado por BBVA Bancomer a la Interred
            '01', // [2] Número de ventana que corresponde en el día
            '01.30', // [5] Versión del Formato del archivo. Fijo: 01.30
            substr($batch_filename, 0, 8), // [8] Nombre físico del presente archivo
            '00', // [2] Sentido del archivo: 00 = viaja de la Interred hacia BBVA Bancomer 01 = viaja de BBVA Bancomer hacia la Interred
            str_repeat(' ', 136), // [136] Espacios para uso futuro
        ];
        $batch_header_2 = [
            sprintf("%06s", $i++), // [6] Número consecutivo de registro
            '2', // [1] Tipo de registro. Fijo: 2
            '05462742', // [8] Identificador propio del Negocio por parte de la Interred
            '05462742', // [8] Número que BBVA Bancomer otorga al negocio para su identificación
            '00000000', // [8] Número de referencia a la cuenta de cheques. Es opcional y solo informativo
            '000', // [3] Número de la sucursal (opcional y solo informativo)
            '0000000', // [7] Número de la cuenta de cheques  (opcional y solo es informativo
            '00000000', // [8] Número de referencia de cargos parciales
            str_repeat(' ', 131), // [131] Espacios para uso futuro
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


        $aTrx['180802'] = [
			// Prueba 1
            'AcdJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODA1MjE4MDAwMDAwMDAwMDE4MzcwMDA4MDIxNjU2MzM4NjEzNDQxMTU2MzMwODAyMDgwMjA4MDIwMTI1OTIxNDc3MjEzNTAwMDAwMzU4ND0yMDA1MTgwODAyMTE1NjMzOTc1MTM1MDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MTM5MDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAxMDAwMDAwMjAwMDAwMDAwMDAyMDAwMDAwMDAwMDIwMDAwMCAgICAgIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMjAxMDAwMDAwMDAwMDAxNTIwMDAwMDIwMDAwNDg0MDAwMDEyNCYgMDAwMDYwMDEyNCEgUTEwMDAwMiAwMCEgUTIwMDAwMiAwOSEgQzQwMDAxMiAxMDI1MTAwMDM2MDAhIDA0MDAwMjAgICAgICAgICAgICAgWSAgICAgICAhIEMwMDAwMjYgMzQwICAwMDEgICAgICAgICAgNyAgMSAwIDA=',

			// Prueba 2
            'ATlJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjAwMDAwMDAwMDAwMDAyMjkwMDA4MDIxNjU5NDQ3Nzk2ODUxMTU5NDQwODAyMDgwMjA4MDIwMTI1OTIxNDE1MjMxNTAwMDEyMjY5Nz0yMDA1MTgwODAyMTE1OTQ0OTc1MTM4MDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMTI0JiAwMDAwNjAwMTI0ISBRMTAwMDAyIDAwISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICBPICAgICAgICEgQzAwMDAyNiA0MjcgIDAwMSAgICAgICAgICA3ICAxIDAgMA==',

			// Prueba 3
            'ATlJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjAwMDAwMDAwMDAwMDA1Mzk1NTA4MDIxNzAwMzMzODUwNzcxMjAwMzMwODAyMDgwMjA4MDIwMTI1OTIxNDE1MjMxNTAwMDEyMjY5Nz0yMDA1MTgwODAyMTIwMDMzOTc1MTM5MDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMTI0JiAwMDAwNjAwMTI0ISBRMTAwMDAyIDAwISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICBPICAgICAgICEgQzAwMDAyNiA0MjcgIDAwMSAgICAgICAgICA3ICAxIDAgMA==',

			// Prueba 4
            'AcdJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODA1MjE4MDAwMDAwMDAwMDA3MzUwMDA4MDIxNzAxMjg5NTg0NzkxMjAxMjgwODAyMDgwMjA4MDIwMTI1OTIxNDc3MjEzNTAwMDAwMzU4ND0yMDA1MTgwODAyMTIwMTI4OTc1MTQyMDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MTM5MDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAxMDAwMDAwMjAwMDAwMDAwMDAyMDAwMDAwMDAwMDIwMDAwMCAgICAgIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMjAxMDAwMDAwMDAwMDAxNTIwMDAwMDIwMDAwNDg0MDAwMDEyNCYgMDAwMDYwMDEyNCEgUTEwMDAwMiAwMCEgUTIwMDAwMiAwOSEgQzQwMDAxMiAxMDI1MTAwMDM2MDAhIDA0MDAwMjAgICAgICAgICAgICAgWSAgICAgICAhIEMwMDAwMjYgMzQwICAwMDEgICAgICAgICAgNyAgMSAwIDA=',

			// Prueba 9
            'AcdJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODA1MjE4MDAwMDAwMDAwMDAzNjYwMDA4MDIxNzA1NTQ5ODg0OTkxMjA1NTQwODAyMDgwMjA4MDIwMTI1OTIxNDc3MjEzNTAwMDAwMzU4ND0yMDA1MTgwODAyMTIwNTU0OTc1MTQ4MDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MTM5MDAwMDAwMDM2NjAwMDAwMDAwMzY2MDAxMDAwMDAwMTYzNDAwMDAwMDAyMDAwMDAwMDAwMDIwMDAwMCAgICAgIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMjAxMDAwMDAwMDAwMDAxNTIwMDAwMDE2MzQwNDg0MDAwMDEyNCYgMDAwMDYwMDEyNCEgUTEwMDAwMiAwMCEgUTIwMDAwMiAwOSEgQzQwMDAxMiAxMDI1MTAwMDM2MDAhIDA0MDAwMjAgICAgICAgICAgICAgWSAgICAgICAhIEMwMDAwMjYgMzQwICAwMDEgICAgICAgICAgNyAgMSAwIDA=',

			// Prueba 10
            'AcdJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODA1MjE4MDAwMDAwMDAwMDEzNjg1NTA4MDIxNzA4MzY5NjgzMjAxMjA4MzYwODAyMDgwMjA4MDIwMTI1OTIxNDc3MjEzNTAwMDAwMzU4ND0yMDA1MTgwODAyMTIwODM2OTc1MTUxMDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MTM5MDAwMDAwMTM2ODUwMDAwMDAxMzY4NTAxMDAwMDAwMDI2NTUwMDAwMDAxNjM0MDAwMDAwMDE2MzQwMCAgICAgIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMjAxMDAwMDAwMDAwMDAxNTIwMDAwMDAyNjU1NDg0MDAwMDEyNCYgMDAwMDYwMDEyNCEgUTEwMDAwMiAwMCEgUTIwMDAwMiAwOSEgQzQwMDAxMiAxMDI1MTAwMDM2MDAhIDA0MDAwMjAgICAgICAgICAgICAgWSAgICAgICAhIEMwMDAwMjYgMzQwICAwMDEgICAgICAgICAgNyAgMSAwIDA=',

			// Prueba 11
            'ATlJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjAwMDAwMDAwMDAwMDA4NzgwMDA4MDIxNzEwNDE3MDE2NTcxMjEwNDEwODAyMDgwMjA4MDIwMTI1OTIxNDE1MjMxNTAwMDEyMjY5Nz0yMDA1MTgwODAyMTIxMDQxOTc1MTUzMDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMTI0JiAwMDAwNjAwMTI0ISBRMTAwMDAyIDAwISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICBPICAgICAgICEgQzAwMDAyNiA0MjcgIDAwMSAgICAgICAgICA3ICAxIDAgMA==',

			// Prueba 12
            'AdVJU08wMjQwMDAwNzcwMjEwMzIzQTg0MDAyRTgxODA1MjE4MDAwMDAwMDAwMDA1MTYwMDA4MDIxNzExNDUzNzU5OTAxMjExNDUwODAyMDgwMjA4MDIwMTIyMTQ3NzIxMzUwMDAwMDM1ODQ9MjAwNTE4MDgwMjEyMTE0NTk3NTE1NTAwMDAwMENQMDEgICAgICAgIDAyNzU0NjI3NDIgICAgICAgICAgICAwMDAwMDAwMDQ4NDEzOTAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMTAwMDAwMDAyNjU1MDAwMDAwMDI2NTUwMDAwMDAwMjY1NTAgICAgICAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDIwMTAwMDAwMDAwMDAwMTUyMDAwMDAwMjY1NTQ4NDAwMDAxNDAmIDAwMDA3MDAxNDAhIFExMDAwMDIgMDAhIFEyMDAwMDIgMDkhIFE2MDAwMDYgMDAwNjAzISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICBZICAgICAgICEgQzAwMDAyNiAzNDAgIDAwMSAgICAgICAgICA3ICAxIDAgMA==',

			// Prueba 13
            'AdVJU08wMjQwMDAwNzcwMjEwMzIzQTg0MDAyRTgxODA1MjE4MDAwMDAwMDAwMDA4NDE4MDA4MDIxNzE0NDMyMTA1MzQxMjE0NDMwODAyMDgwMjA4MDIwMTIyMTQ3NzIxMzUwMDAwMDM1ODQ9MjAwNTE4MDgwMjEyMTQ0Mzk3NTE1ODAwMDAwMENQMDEgICAgICAgIDAyNzU0NjI3NDIgICAgICAgICAgICAwMDAwMDAwMDQ4NDEzOTAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMTAwMDAwMDAyNjU1MDAwMDAwMDI2NTUwMDAwMDAwMjY1NTAgICAgICAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDIwMTAwMDAwMDAwMDAwMTUyMDAwMDAwMjY1NTQ4NDAwMDAxNDAmIDAwMDA3MDAxNDAhIFExMDAwMDIgMDAhIFEyMDAwMDIgMDkhIFE2MDAwMDYgMDAxMjAzISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICBZICAgICAgICEgQzAwMDAyNiAzNDAgIDAwMSAgICAgICAgICA3ICAxIDAgMA==',

			// Prueba 14
            'AdVJU08wMjQwMDAwNzcwMjEwMzIzQTg0MDAyRTgxODA1MjE4MDAwMDAwMDAwMDA5NDUwMDA4MDIxNzE1NDE1ODYzMTcxMjE1NDEwODAyMDgwMjA4MDIwMTIyMTQ3NzIxMzUwMDAwMDM1ODQ9MjAwNTE4MDgwMjEyMTU0MTk3NTE2MjAwMDAwMENQMDEgICAgICAgIDAyNzU0NjI3NDIgICAgICAgICAgICAwMDAwMDAwMDQ4NDEzOTAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMTAwMDAwMDAyNjU1MDAwMDAwMDI2NTUwMDAwMDAwMjY1NTAgICAgICAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDIwMTAwMDAwMDAwMDAwMTUyMDAwMDAwMjY1NTQ4NDAwMDAxNDAmIDAwMDA3MDAxNDAhIFExMDAwMDIgMDAhIFEyMDAwMDIgMDkhIFE2MDAwMDYgMDAxODAzISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICBZICAgICAgICEgQzAwMDAyNiAzNDAgIDAwMSAgICAgICAgICA3ICAxIDAgMA==',

			// Prueba 17
            'APdJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjIwMDAwMDAwMDAwMDAyMjkwMDA4MDIxNzE4NDQzNTkyMzIxMjE4NDQwODAyMDgwMjA4MDIwMTI1OTIxNDE1MjMxNTAwMDEyMjY5Nz0yMDA1MTgwODAyMTIxODQ0MjAyNDMzMDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMDU4JiAwMDAwNDAwMDU4ISBRMTAwMDAyIDkgISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMA==',

			// Prueba 18
            'APdJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjIwMDAwMDAwMDAwMDEzNjg1NTA4MDIxNzE5MzY0MTQ2NDIxMjE5MzYwODAyMDgwMjA4MDIwMTI1OTIxNDc3MjEzNTAwMDAwMzU4ND0yMDA1MTgwODAyMTIxOTM2MjAyNDM0MDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMDU4JiAwMDAwNDAwMDU4ISBRMTAwMDAyIDkgISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMA==',

			// Prueba 19
            'AQVJU08wMjQwMDAwNzcwMjEwMzIzQTg0MDAyRTgxODAxMjIwMDAwMDAwMDAwMDA1MTYwMDA4MDIxNzIwMjAzMTE4NjAxMjIwMjAwODAyMDgwMjA4MDIwMTIyMTQ3NzIxMzUwMDAwMDM1ODQ9MjAwNTE4MDgwMjEyMjAyMDIwMjQzNTAwMDAwMENQMDEgICAgICAgIDAyNzU0NjI3NDIgICAgICAgICAgICAwMDAwMDAwMDQ4NDAwMDA3NCYgMDAwMDUwMDA3NCEgUTEwMDAwMiA5ICEgUTIwMDAwMiAwOSEgUTYwMDAwNiAwMDA2MDMhIEM0MDAwMTIgMTAyNTEwMDAzNjAw',

			// Prueba 1m
            'ATlJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjAwMDAwMDAwMDAwMDAyMzIwMDA4MDIxNjQ1MDA4MDU3OTQxMTQ1MDAwODAyMDgwMjA4MDIwMTI1OTIxNTQxMzMzMDA4OTAyMDAxMT0yNTEyMTgwODAyMTE0NTAwMDA2MDA3MDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMTI0JiAwMDAwNjAwMTI0ISBRMTAwMDAyIDkgISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICAgICAgICAgICEgQzAwMDAyNiA2MDEgIDAwMSAgICAgICAgICA3ICAxIDAgIA==',

			// Prueba 2m
            'ATlJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjAwMDAwMDAwMDAwMDAzMDcwMDA4MDIxNjQ2MDUyMjQ1NTQxMTQ2MDUwODAyMDgwMjA4MDIwMTI1OTIxNTQxMzMzMDA4OTAyMDA3OD0yMjEyMTgwODAyMTE0NjA1MDA2MDA4MDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMTI0JiAwMDAwNjAwMTI0ISBRMTAwMDAyIDkgISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICAgICAgICAgICEgQzAwMDAyNiAyMDEgIDAwMSAgICAgICAgICA3ICAxIDAgIA==',

			// Prueba 3m
            'ATlJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjAwMDAwMDAwMDAwMDAxNjEwMDA4MDIxNjQ2NTIzODExNDQxMTQ2NTIwODAyMDgwMjA4MDIwMTI1OTIxNTQxMzMzMDA4OTAyMDA4Nj0yMjEyMTgwODAyMTE0NjUyMDA2MDA5MDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMTI0JiAwMDAwNjAwMTI0ISBRMTAwMDAyIDkgISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICAgICAgICAgICEgQzAwMDAyNiAyMDEgIDAwMSAgICAgICAgICA3ICAxIDAgIA==',

			// Prueba 4m
            'ATlJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjAwMDAwMDAwMDAwMDEzMTEwMDA4MDIxNjQ3NTgxNDg3NTAxMTQ3NTgwODAyMDgwMjA4MDIwMTI1OTIxNTQxMzMzMDA4OTAxMDQ4Mz0yMjEyMTgwODAyMTE0NzU4MDA2MDEwMDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMTI0JiAwMDAwNjAwMTI0ISBRMTAwMDAyIDkgISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICAgICAgICAgICEgQzAwMDAyNiAyMDEgIDAwMSAgICAgICAgICA3ICAxIDAgIA==',

			// Prueba 5m
            'ATlJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjAwMDAwMDAwMDAwMDA0NTYwMDA4MDIxNjQ4NTQ1NDI1MzAxMTQ4NTQwODAyMDgwMjA4MDIwMTI1OTIxNTQxMzMzMDA4OTAxMDQ0Mj0yMjEyMTgwODAyMTE0ODU0MDA2MDExMDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMTI0JiAwMDAwNjAwMTI0ISBRMTAwMDAyIDkgISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICAgICAgICAgICEgQzAwMDAyNiAyMDEgIDAwMSAgICAgICAgICA3ICAxIDAgIA==',
			// Caso 28 Prueba  equivocada sin reverso
            'AcdJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODA1MjE4MDAwMDAwMDAwMDA3NjgwMDA4MDIxNjI0MzQ1NjgzMzcxMTI0MzQwODAyMDgwMjA4MDIwMTI1OTIxNDc3MjEzNTAwMDAwMzU4ND0yMDA1MTgwODAyMTEyNDM0OTc1MDk3MDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MTM5MDAwMDAwMDc2ODAwMDAwMDAwNzY4MDAxMDAwMDAwMTIzMjAwMDAwMDAyMDAwMDAwMDAwMDIwMDAwMCAgICAgIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMjAxMDAwMDAwMDAwMDAxNTIwMDAwMDEyMzIwNDg0MDAwMDEyNCYgMDAwMDYwMDEyNCEgUTEwMDAwMiAwMCEgUTIwMDAwMiAwOSEgQzQwMDAxMiAxMDI1MTAwMDM2MDAhIDA0MDAwMjAgICAgICAgICAgICAgWSAgICAgICAhIEMwMDAwMjYgMzQwICAwMDEgICAgICAgICAgNyAgMSAwIDA=',

        ];

        $respuesta = [];
        foreach($aTrx[$fecha_ymd] as $sTrx) {
            $sRespuesta = base64_decode($sTrx);
            $oInterred = new BBVAInterred();
            $aMensajeISO = $oInterred->procesaMensaje($sRespuesta);
            $respuesta[] = $aMensajeISO['iso_parsed'];
        }

        $totales = [
            'compras' => 0,
            'monto_compras' => 0,
            'cargos' => 0,
            'monto_cargos' => 0,
            'negocio' => 1,
        ];

        $aTrxs = [];
        foreach($respuesta as $res) {
            if (empty($res[39])) {
                #echo "<br>Skipped: " . print_r($res, true);
            } else if ($res[39] == '00') {

                // REGCAD-CVE-OPERACIÓN
                if ($res[3] == '200000') {
                    $REGCAD_CVE_OPERACIÓN = 4;
                } else {
                    $REGCAD_CVE_OPERACIÓN = 1;
                }

                // DEfine línea
                $linea = [
                    0, //sprintf("%06s", $i++), // REGCAD-NUM-REG [6] Número consecutivo de registro
                    '3', // REGCAD-TIPO-REG [1] Tipo de registro. Fijo 3
                    substr($res[37], 0, 6), // REGCAD-FECHA [6] Fecha valor del pagare (AAMMDD)

                    $REGCAD_CVE_OPERACIÓN, // REGCAD-CVE-OPERACIÓN [1] Clave de la operación del Pagaré 1= Compra On-Line (Aut.Bnmx) 2= Compra On-Line (Aut.Cadena) 3= Compra Off-Line 4= Devolución 5= Compra diferida On-Line 6= Devolución Diferida Off-Line
                    $res[38] ?? '000000', // REGCAD-AUTORIZACION [6] Número con el cual autorizó BBVA Bancomer
                    substr($res[4], 2), // REGCAD-IMPORTE [10] Monto de la transacción
                    // REGCAD-REFERENCIA [23] Número único para la localización de pagares “
                        '7455546', // [7] 7455546” = Valor Fijo.
                        substr($res[37], 1, 1), // [1] “A” = último dígito del año.
                        date("z", strtotime(substr($res[37], 0, 2) . '-' . substr($res[37], 2, 2) . '-' . substr($res[37], 4, 2))) + 1, // [3] “DDD = fecha juliana.
                        sprintf("%011s", $res[11]), // [11] “99999999999” = 11 posiciones libre numérico.
                        $this->calcLuhn($res[11]), // [1]  “1” = dígito verificador ( módulo 10)
                    sprintf("%- 19s", substr($res[35], 2, strpos($res[35], '=') - 2)), // REGCAD-TARJETA [19] Número de tarjeta (BBVA Bancomer, Visa, MC, BBVA Bancomer, Carnet, Amex)
                    '0000', // REGCAD-MOT-RECH [4] Motivo de rechazo del movimiento. Campo exclusivo para BBVA Bancomer. La interred debe enviar el valor fijo: 0000
                    '0000', // REGCAD-STAT-REG [4] Estatus del registro. La interred debe enviar el valor fijo: 0000
                    substr($res[37], 6, 6), // REGCAD-TIME [6] Hora en que se efectúo la transacción (HHMMSS)
                    '0007', // REGCAD-INDIC-COM-ELEC [4] Indicador de Comercio Electrónico: 0 = No es transacción de Comercio Electrónico 5 =Transacción electrónica segura con certificado del tarjetahabiente 6 =Transacción electrónica segura sin certificado del tarjetahabiente, con certificado del negocio 7 =Transacción de comercio electrónico sin certificados, con canal encriptado 8 = Transacción de comercio electrónico No segura
                    '01', // [2] Modo de ingreso de datos de la cuentas emisora: “00” Información desconocida “01” Información digitada “05” Información obtenida de tarjeta con chip. “07” Contactless “80” FallBack  “90” Información obtenida de la banda magnética del plástico “91” Contactless MSD (banda magnética)
                    '000000000', // [9] Cash back 7 enteros 2 decimales
                    '1', // [1] Capacidad de la terminal para lectura de datos: '1' No hay terminal (elaborado manualmente) '2' Con lectora de banda '3' Contactless  '4' Lector de caracteres ópticos '5' Lector de CHIP(circuito integrado)
                    $this->obtienePlan($res[63])['plan'], // [2] Plan a utilizarse '00' Sin plan '03' Plan sin intereses '05' Plan con intereses '07' Compre hoy pague después
                    $this->obtienePlan($res[63])['diferido'], // [2] Número de meses en que el pago no se hace exigible(Compre hoy, pague después)
                    $this->obtienePlan($res[63])['parcialidades'], // [2] Número de meses en que se van a dividir los pagos (con o sin intereses), justificado con ceros a la izquierdo.
                    '000000', // [6] Número de boleta utilizado en la transacción
                    '0000', // [4] Número de terminal donde se efectuó la transacción
                    '000', // [3] Número de almacen/tienda donde se efectuó la transacción
                    $res[49], // [3] Código de moneda numérico ISO 484 = Pesos Mexicanos 840 = Dólares Americanos
                    ' ', // [1] Estatus DCC: Bandera que indica si la transacción es DCC 'A' El tarjetahabiente aceptó que su transacción fuera   operada en DCC ' '  (espacio en blanco) La transacción no es DCC
                    sprintf("% 25s", ''), // [25] Nombre del comercio para cuando la interred es agregador o integrador Nombre de agregador seguido de un asterisco (*), seguido del nombre del Sub Agregado (7*17) ejemplo (Aaaaaaa*Bbbbbbbbbbbbbb) En caso de no ser agregador debe de llegar informado con espacios en blanco (si aplica) ID
                    sprintf("%- 8s", ''), // sprintf("%- 8s", substr($res[60], 0, 4) . substr($res[60], 12, 4)), // [8] Id del dispositivo o punto de venta informado de derecha a izquierda
                    sprintf("% 10s", ''), // [10] Número de serie del dispositivo utilizado, este se obtiene del campo 63, token ES, subcampo 2
                    str_repeat(' ', 12), // [12] Espacios para uso futuro
                ];
                if (!empty($res[38])) {
                    if (!empty($aTrxs[$res[38]])) {
                        unset($aTrxs[$res[38]]);
                        if ($res[3] == '180000' || $res[3] == '000000') {
                            $totales['compras'] -= 1;
                            $totales['monto_compras'] -= $res[4];
                        } else if ($res[3] == '200000') {
                            $totales['cargos'] -= 1;
                            $totales['monto_cargos'] -= $res[4];
                        }
                        $totales['negocio'] -= 1;
                    } else {
                        $aTrxs[$res[38]] = $linea;
                        if ($res[3] == '180000' || $res[3] == '000000') {
                            $totales['compras'] += 1;
                            $totales['monto_compras'] += $res[4];
                        } else if ($res[3] == '200000') {
                            $totales['cargos'] += 1;
                            $totales['monto_cargos'] += $res[4];
                        }
                        $totales['negocio'] += 1;
                    }
                }
            }
        }
        // Escribe registros en batch
        $registros = 0;
        $batch_linea = [];
        foreach($aTrxs as $iTrxId => $aTrnx) {
            $aTrnx[0] = sprintf("%06s", $i++); // REGCAD-NUM-REG [6] Número consecutivo de registro
            $batch_linea[] = implode('', $aTrnx) . "\n";
            #$totales['compras'] += 1;
            #$totales['monto_compras'] += $aTrx[5];
            $registros += 1;
        }

        $batch_footer_1 = [
            sprintf("%06s", $i), // [6] Número consecutivo de registro
            '4', // [1] Tipo de registro. Fijo: 4
            sprintf("%07s", $totales['compras']), // [7] Número de compras en el archivo
            sprintf("%014s", $totales['monto_compras']), // [14] Monto de las compras del archivo
            sprintf("%07s", $totales['cargos']), // TRCAD-NUM-DEB [7] Número de devoluciones del archivo
            sprintf("%014s", $totales['monto_cargos']), // TRCAD-IMP-DEB [14] Monto de las devoluciones del archivo
            sprintf("%06s", $totales['negocio']), // [6] Número de Header`s de Negocio (registros tipo 2) y de Detalle (registros tipo3)
            '000000', // [6] Hora en que BBVA Bancomer recibió el archivo  de entrada (HHMMSS). La Interred enviará el valor fijo: 000000
            '000000', // [6] Hora en que BBVA Bancomer finalizó el proceso del archivo de entrada y generó el archivo de Retorno con los rechazos (HHMMSS). La Interred enviará el valor fijo: 000000
            str_repeat(' ', 113), // [54] Espacios para uso futuro
        ];

        $batch = implode('', $batch_header_1) . "\n" . implode('', $batch_header_2) . "\n" . implode('', $batch_linea) . implode('', $batch_footer_1) . "\n";

        Storage::put($batch_filename, $batch);

        return $batch;

    }

    private function calcLuhn(string $sNum): int
    {
        $sChecksum = '';
        foreach (str_split(strrev($sNum . '0')) as $i => $d) {
            $sChecksum .= $i % 2 !== 0 ? $d * 2 : $d;
        }
        $iDigit = strrev((string) array_sum(str_split($sChecksum)))[0];
        $iResult = (10 - $iDigit);
        if ($iResult == 10) {
            $iResult = 0;
        }
        return $iResult;
    }

    private function obtienePlan($sCampo63): array
    {
        $aPlan = [
            'plan' => '00',
            'parcialidades' => '00',
            'diferido' => '00',
        ];
        if (!empty($sCampo63)) {
            // Obtiene data de tokens
            $sTokens = substr($sCampo63, 15);
            // Separa campo de tokens
            $aTmpTokens = explode('! ', $sTokens);
            $aTokens = [];
            foreach($aTmpTokens as $sTmpToken) {
                $aTokens[substr($sTmpToken, 0, 2)] = substr($sTmpToken, 2);
            }
            // Obtiene plan de pagos
            if (isset($aTokens['Q6'])) {
                $aPlan['plan'] = substr($aTokens['Q6'], -2, 2);
                $aPlan['parcialidades'] = substr($aTokens['Q6'], 8, 2);
                $aPlan['diferido'] = substr($aTokens['Q6'], 6, 2);
            }
        }
        return $aPlan;
    }
}


class BbvaTest
{
    private $oTarjetaCredito1 = null;
    private $oTarjetaDebito1 = null;
    private $oPeticionCargo = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Construye tarjetas de credito
        $this->oTarjetaCredito1 = new TarjetaCredito([
            // Crédito
            'pan' => '4772135000003584',
            'nombre' => 'Juan Perez Lopez',
            'cvv2' => '340',
            'nip' => '3344',
            'expiracion_mes' => '05',
            'expiracion_anio' => '20',
            'lealtad' => true,
        ]);
        $this->oTarjetaDebito1 = new TarjetaCredito([
            // Débito
            'pan' => '4152315000122697',
            'nombre' => 'Juan Perez Lopez',
            'cvv2' => '427',
            'nip' => '5897',
            'expiracion_mes' => '05',
            'expiracion_anio' => '20',
            'lealtad' => false,
        ]);
        $this->oTarjetaCreditoVisa = new TarjetaCredito([
            // Crédito
            'pan' => '4761739001010010', // '4761739001010416', '4761739001010119', '4761739001011133',
            'nombre' => 'Juan Perez Lopez',
            'cvv2' => '221',
            'expiracion_mes' => '12',
            'expiracion_anio' => '22',
            'lealtad' => true,
        ]);
//        $this->oTarjetaCreditoVisa = new TarjetaCredito([
//            // Crédito
//            'pan' => '4761739001010119',
//            'nombre' => 'Juan Perez Lopez',
//            'cvv2' => '830',
//            'expiracion_mes' => '12',
//            'expiracion_anio' => '17',
//            'lealtad' => true,
//        ]);
//        $this->oTarjetaCreditoVisa = new TarjetaCredito([
//            // Crédito
//            'pan' => '4761739001011133',
//            'nombre' => 'Juan Perez Lopez',
//            'cvv2' => '201',
//            'expiracion_mes' => '12',
//            'expiracion_anio' => '22',
//            'lealtad' => true,
//        ]);
        // Construye petición de cargo
        $this->oPeticionCargo = new PeticionCargo([
            'prueba' => true,
            'id' => Uuid::generate(4)->string,
            'tarjeta' => $this->oTarjetaCredito1,
            'monto' => 0,
            'puntos' => 0,
            'descripcion' => 'Prueba EGlobal BBVA',
            'diferido' => 0,
            'comercio_uuid' => '176f76a8-2670-4288-9800-1dd5f031a57e',
        ]);
    }

    public function pruebas(string $sPrueba, string $sTipo = 'envio_online', string $sAccion = 'prueba', string $sTrxReq = null, string $sTrxResp = null)
    {
        // Pruebas
        if ($sPrueba == '1') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1;
            $this->oPeticionCargo->monto = 1835.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTAS NORMALES';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '2') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1; // Debito
            $this->oPeticionCargo->monto = 227.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTAS NORMALES';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '3') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1; // Debito
            $this->oPeticionCargo->monto = 537.55;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTAS NORMALES';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '4') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1;
            $this->oPeticionCargo->monto = 733.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTAS NORMALES';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '5') {
            $this->oTarjetaCredito1->cvv2 = '123';
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1;
            $this->oPeticionCargo->monto = 417.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VALIDACIÓN DE CVV2';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '6') {
            $this->oTarjetaDebito1->cvv2 = '';
            $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1; // Debito
            $this->oPeticionCargo->monto = 383.70;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VALIDACIÓN DE CVV2';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '7') {
            $this->oTarjetaDebito1->cvv2 = '456';
            $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1; // Debito
            $this->oPeticionCargo->monto = 817.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VALIDACIÓN DE CVV2';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '8') {
            $this->oTarjetaCredito1->cvv2 = '';
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 835.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VALIDACIÓN DE CVV2';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '9o') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 0.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTAS CON PUNTOS - Consulta de Puntos';
            $aOpciones = ['tipo' => 'puntos_consulta'];
        } else if ($sPrueba == '9') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 365;
            $this->oPeticionCargo->puntos = 365;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTAS CON PUNTOS - Venta con Puntos';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '10') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 1367.55;
            $this->oPeticionCargo->puntos = 1367.50;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTAS CON PUNTOS - Venta Con Puntos Mixta';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '11') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1; // Débito
            $this->oPeticionCargo->monto = 877.00;
            $this->oPeticionCargo->puntos = 0;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTAS CON PUNTOS - Venta con Puntos';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '12') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 513.00;
            $this->oPeticionCargo->plan = new PlanPago(['plan' => 'msi', 'parcialidades' => 6]);
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTA CON PROMOCIÓN - Venta 6 MSI';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '13') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 839.80;
            $this->oPeticionCargo->plan = new PlanPago(['plan' => 'msi', 'parcialidades' => 12]);
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTA CON PROMOCIÓN - Venta 12 MSI';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '14') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 941.00;
            $this->oPeticionCargo->plan = new PlanPago(['plan' => 'msi', 'parcialidades' => 18]);
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTA CON PROMOCIÓN - Venta 18 MSI';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '15') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 87.00;
            $this->oPeticionCargo->plan = new PlanPago(['plan' => 'msi', 'parcialidades' => 6]);
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTA CON PROMOCIÓN - 6 MSI - Declinada';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '16') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1;
            $this->oPeticionCargo->monto = 2547.00;
            $this->oPeticionCargo->plan = new PlanPago(['plan' => 'msi', 'parcialidades' => 6]);
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTA CON PROMOCIÓN - 6 MSI - Declinada Débito';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '17') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1;
            $this->oPeticionCargo->monto = 227.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' DEVOLUCIONES - Devolución del caso 2';
            $aOpciones = ['tipo' => 'devolucion'];
        } else if ($sPrueba == '18') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 1367.55;
            //$this->oPeticionCargo->puntos = 1362.50;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' DEVOLUCIONES - Devolución del caso 10';
            $aOpciones = ['tipo' => 'devolucion'];
        } else if ($sPrueba == '19') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 513.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' DEVOLUCIONES - Devolución del caso 12?';
            $aOpciones = ['tipo' => 'devolucion'];
        } else if ($sPrueba == '20') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 355;
            $this->oPeticionCargo->puntos = 355;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' CANCELACIONES - Venta Con Puntos';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '21') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 355;
            $this->oPeticionCargo->puntos = 355;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' CANCELACIONES - Venta Con Puntos - Cancelaión';
            $aOpciones = [
                'tipo' => 'cancelacion',
                'referencia' => '180417115603', // CAMPO 37 de la respuesta
                'autorizacion' => '563178', // CAMPO 38 de la respuesta
                'mti_original' => '0200', // ?  Id de mensaje ISO de la transacción original
                'fecha_original' => '0417', // CAMPO 13 de la respuesta
                'hora_original' => '115603', // CAMPO 12 de la respuesta
                'fecha_captura_original' => '0417', // CAMPO 17 de la respuesta
            ];
        } else if ($sPrueba == '22') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1;
            $this->oPeticionCargo->monto = 768.15;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' CANCELACIONES - Venta normal';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '23') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1; // Credito
            $this->oPeticionCargo->monto = 768.15;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' CANCELACIONES - Venta normal - Cancelaión';
            $aOpciones = [
                'tipo' => 'cancelacion',
                'referencia' => '180411113522', // CAMPO 37 de la respuesta
                'autorizacion' => '300090', // CAMPO 38 de la respuesta
                'mti_original' => '0200', // ?  Id de mensaje ISO de la transacción original
                'fecha_original' => '0411', // CAMPO 13 de la respuesta
                'hora_original' => '113522', // CAMPO 12 de la respuesta
                'fecha_captura_original' => '0411', // CAMPO 17 de la respuesta
            ];
        } else if ($sPrueba == '24') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 513.00;
            $this->oPeticionCargo->plan = new PlanPago(['plan' => 'msi', 'parcialidades' => 6]);
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' CANCELACIONES - Venta 6 MSI';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '25') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 513.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' CANCELACIONES - Venta 6 MSI - Cancelaión';
            $aOpciones = [
                'tipo' => 'cancelacion',
                'referencia' => '180412101156', // CAMPO 37 de la respuesta
                'autorizacion' => '775066', // CAMPO 38 de la respuesta
                'mti_original' => '0200', // ?  Id de mensaje ISO de la transacción original
                'fecha_original' => '0412', // CAMPO 13 de la respuesta
                'hora_original' => '101156', // CAMPO 12 de la respuesta
                'fecha_captura_original' => '0412', // CAMPO 17 de la respuesta
            ];
        } else if ($sPrueba == '26') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1;
            $this->oPeticionCargo->monto = 622.20;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . '  REVERSOS AUTOMÁTICOS COMERCIO - Venta normal';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '27') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 516.00;
            $this->oPeticionCargo->plan = new PlanPago(['plan' => 'msi', 'parcialidades' => 6]);
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' REVERSOS AUTOMÁTICOS COMERCIO - Venta 6 MSI';
            $aOpciones = ['tipo' => 'puntos_compra', 'tipo_original' => 'puntos_compra'];
        } else if ($sPrueba == '28') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 766;
            $this->oPeticionCargo->puntos = 766;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' REVERSOS AUTOMÁTICOS COMERCIO - Venta con Puntos';
            $aOpciones = ['tipo' => 'puntos_compra', 'tipo_original' => 'puntos_compra'];
        } else if ($sPrueba == '29') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1;
            $this->oPeticionCargo->monto = 2135.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . '  REVERSOS AUTOMÁTICOS EG - Venta normal';
            $aOpciones = ['tipo' => 'compra', 'tipo_original' => 'compra'];
        } else if ($sPrueba == '30') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto =  1213.00 ;
            $this->oPeticionCargo->puntos = 1213.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' REVERSOS AUTOMÁTICOS EG - Venta con Puntos';
            $aOpciones = ['tipo' => 'puntos_compra', 'tipo_original' => 'puntos_compra'];
        } else if ($sPrueba == '31') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 1112.50;
            $this->oPeticionCargo->plan = new PlanPago(['plan' => 'msi', 'parcialidades' => 6]);
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' REVERSOS AUTOMÁTICOS EG - Venta 6 MSI';
            $aOpciones = ['tipo' => 'puntos_compra', 'tipo_original' => 'puntos_compra'];




        } else if ($sPrueba == '1v') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCreditoVisa; // Credito
            $this->oPeticionCargo->monto = 574.50;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VISA - VENTAS NORMALES - Venta normal';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '2v') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCreditoVisa; // Credito
            $this->oPeticionCargo->monto = 335.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VISA - VENTAS NORMALES - Venta normal';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '3v') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCreditoVisa; // Credito
            $this->oPeticionCargo->monto = 653.55;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VISA - REVERSOS AUTOMÁTICOS COMERCIO - Venta normal con reversos';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '4v') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCreditoVisa; // Credito
            $this->oPeticionCargo->monto = 832.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VISA - REVERSOS AUTOMÁTICOS COMERCIO - Venta normal con reversos';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '5v') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCreditoVisa; // Credito
            $this->oPeticionCargo->monto = 385.82;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VISA - REVERSOS AUTOMÁTICOS EG - Venta normal con reversos';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '6v') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCreditoVisa; // Credito
            $this->oPeticionCargo->monto = 1972.80;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VISA - REVERSOS AUTOMÁTICOS EG - Venta normal con reversos';
            $aOpciones = ['tipo' => 'compra'];




        } else if ($sPrueba == '1m') {
            $this->oPeticionCargo->tarjeta = new TarjetaCredito([
                // Crédito
                'pan' => '5413330089020011',
                'nombre' => 'Juan Perez Lopez',
                'cvv2' => '601',
                'expiracion_mes' => '12',
                'expiracion_anio' => '25',
                'lealtad' => false,
            ]);
            $this->oPeticionCargo->monto = 231.50;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' MC - VENTAS NORMALES';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '2m') {
            $this->oPeticionCargo->tarjeta = new TarjetaCredito([
                // Crédito
                'pan' => '5413330089020078',
                'nombre' => 'Juan Perez Lopez',
                'cvv2' => '201',
                'expiracion_mes' => '12',
                'expiracion_anio' => '25',
                'lealtad' => false,
            ]);
            $this->oPeticionCargo->monto = 306.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' MC - VENTAS NORMALES';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '3m') {
            $this->oPeticionCargo->tarjeta = new TarjetaCredito([
                // Crédito
                'pan' => '5413330089020086',
                'nombre' => 'Juan Perez Lopez',
                'cvv2' => '201',
                'expiracion_mes' => '12',
                'expiracion_anio' => '25',
                'lealtad' => false,
            ]);
            $this->oPeticionCargo->monto = 160.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' MC - VENTAS NORMALES';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '4m') {
            $this->oPeticionCargo->tarjeta = new TarjetaCredito([
                // Crédito
                'pan' => '5413330089010483',
                'nombre' => 'Juan Perez Lopez',
                'cvv2' => '201',
                'expiracion_mes' => '12',
                'expiracion_anio' => '25',
                'lealtad' => false,
            ]);
            $this->oPeticionCargo->monto = 1310.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' MC - VENTAS NORMALES';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '5m') {
            $this->oPeticionCargo->tarjeta = new TarjetaCredito([
                // Crédito
                'pan' => '5413330089010442',
                'nombre' => 'Juan Perez Lopez',
                'cvv2' => '201',
                'expiracion_mes' => '12',
                'expiracion_anio' => '25',
                'lealtad' => false,
            ]);
            $this->oPeticionCargo->monto = 455.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' MC - VENTAS NORMALES';
            $aOpciones = ['tipo' => 'compra'];
        } else {
            throw new \Exception("Prueba no encontrada", 404);
        }






        // Verifica acción a ejecutar
        if ($sAccion == 'cancelacion' && !empty($sTrxReq) && !empty($sTrxResp)) {
            $oInterredTrx = new BBVAInterred();
            $aMensajeTrxReq = $oInterredTrx->procesaMensaje($sTrxReq);
            $aMensajeTrxResp = $oInterredTrx->procesaMensaje($sTrxResp);
            unset($oInterredTrx);
            // Verifica si el plan tiene
            $aPlan = $this->obtienePlan($aMensajeTrxReq['iso_parsed']['63']);
            if (in_array($aPlan['plan'], ['03'])) {
                $this->oPeticionCargo->plan = new PlanPago(['plan' => 'msi', 'parcialidades' => $aPlan['parcialidades']]);
            } else if (in_array($aPlan['plan'], ['05'])) {
                $this->oPeticionCargo->plan = new PlanPago(['plan' => 'mci', 'parcialidades' => $aPlan['parcialidades']]);
            } else if (in_array($aPlan['plan'], ['07'])) {
                $this->oPeticionCargo->plan = new PlanPago(['plan' => 'diferido', 'parcialidades' => $aPlan['parcialidades'], 'diferimiento' => $aPlan['diferido']]);
            }
            $aOpcionesCancelacion = [
                'tipo' => 'cancelacion',
                'tipo_original' => $aOpciones['tipo'],
                'referencia' => $aMensajeTrxResp['iso_parsed']['37'], // CAMPO 37 de la respuesta
                'autorizacion' => $aMensajeTrxResp['iso_parsed']['38'], // CAMPO 38 de la respuesta
                'mti_original' => $aMensajeTrxReq['iso_mti'], // MTI de mensaje ISO de la transacción original
                'fecha_original' => $aMensajeTrxResp['iso_parsed']['13'], // CAMPO 13 de la respuesta
                'hora_original' => $aMensajeTrxResp['iso_parsed']['12'], // CAMPO 12 de la respuesta
                'fecha_captura_original' => $aMensajeTrxResp['iso_parsed']['17'], // CAMPO 17 de la respuesta
            ];
            $aOpciones = $aOpcionesCancelacion;
        }







        // Verifica tipo de prueba
        $bEnvio = false;
        $bEcho = false;
        if ($sTipo == 'datos_json') {
            $bEnvio = false;
            $bEcho = false;
        } else if ($sTipo == 'datos_online') {
            $bEnvio = false;
            $bEcho = true;
        } else if ($sTipo == 'envio_json') {
            $bEnvio = true;
            $bEcho = false;
        } else if ($sTipo == 'envio_online') {
            $bEnvio = true;
            $bEcho = true;
        }
        // Prepara tipo de mensaje
        if (in_array($aOpciones['tipo'], ['compra', 'puntos_compra', 'devolucion'])) {
            $sTipoMensaje = 'mensajeVenta';
        } else if (in_array($aOpciones['tipo'], ['cancelacion', 'reverso'])) {
            $sTipoMensaje = 'mensajeCancelacion';
        } else {
            throw new \Exception("Tipo de mensaje desconocido: {$aOpciones['tipo']}", 404);
        }
        if ($bEcho) {
            echo "\n<br>TipoMensaje: {$sTipoMensaje}";
        }


        // ========================================================================
        if ($bEcho) {
            echo "\n<br>Generando transacción";
        }
        if ($sTipo == 'envio_json' || $sTipo == 'envio_online') {
            // Prepara transacción
            $sUuid = Uuid::generate(4);
            $oTrx = new Transaccion([
                'uuid' => $sUuid,
                'prueba' => $this->oPeticionCargo->prueba,
                'operacion' => 'pago',
                'monto' => $this->oPeticionCargo->monto,
                'forma_pago' => 'tarjeta',
                'estatus' => 'pendiente',
                'datos_pago' => [
                    'nombre' => $this->oPeticionCargo->tarjeta->nombre,
                    'pan' => $this->oPeticionCargo->tarjeta->pan,
                    'pan_hash' => $this->oPeticionCargo->tarjeta->pan_hash,
                    'marca' => $this->oPeticionCargo->tarjeta->marca,
                ],
                // Comercio
                'datos_comercio' => [
                    'pedido' => $this->oPeticionCargo->pedido,
                    'cliente' => $this->oPeticionCargo->cliente,
                ],
                // Claropagos
                'datos_claropagos' => [],
                // Eventos
                'datos_antifraude' => [],
                'datos_procesador' => [],
                'datos_destino' => [],
                // Catálogos
                'comercio_uuid' => $this->oPeticionCargo->comercio_uuid,
                'transaccion_estatus_id' => 4,
                'pais' => 'MEX',
                'moneda' => 'MXN',
            ]);
            // Guarda transacción
            $oTrx->save();
            $oTrx = Transaccion::find($sUuid);
        }



        // Prepara mensaje y envía
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->$sTipoMensaje($this->oPeticionCargo, $aOpciones, $bEnvio, $bEcho);
        // Prepara resultado
        $aResultado = [
            'peticion' => [
                'mensaje_json' => $this->oPeticionCargo,
                'mensaje_b64' => base64_encode($oResultado->mensaje),
                'mensaje_hex' => $oResultado->mensaje_hex,
                'iso_mti' => $oResultado->iso->getMTI(),
                'iso_parsed' => $oResultado->iso->getDataArray(),
                'iso_validation' => $oResultado->iso->getIsoValidation(),
            ],
        ];


        // Prepara respuesta si fue enviado el mensaje
        $bRespuesta = false;
        if (isset($oResultado->respuesta)) {
            $aResultado['respuesta'] = $oResultado->respuesta;
            $bRespuesta = true;


            if ($sTipo == 'envio_json' || $sTipo == 'envio_online') {
                // ========================================================================
                $oTrx->datos_procesador = [
                    'request' => [
                        'json' => $this->oPeticionCargo->toJson(),
                        'b64' => base64_encode($oResultado->mensaje),
                    ],
                    'response' => [
                        'b64' => $oResultado->respuesta['mensaje_b64'],
                        'json' => $oResultado->respuesta['iso_parsed'],
                    ],
                ];
                if (isset($oTrx->datos_procesador['response']['json']['39']) && $oTrx->datos_procesador['response']['json']['39'] == '00') {
                    $oTrx->estatus = 'completada';
                } else {
                    $oTrx->estatus = 'rechazada-banco';
                }
                $oTrx->save();
            }
        }

        if ($sTipo == 'envio_json' || $sTipo == 'envio_online') {
            // Envía transacciones a admin y clientes
            #$oMensajeCP = new MensajeCP();
            #$oMensajeResultadoA = $oMensajeCP->envia('clientes', '/api/admin/transaccion', 'POST', $oTrx->toJson());
            #dump($oMensajeResultadoA);
            #$oMensajeResultadoB = $oMensajeCP->envia('admin', '/api/admin/transaccion', 'POST', $oTrx->toJson());
            #dump($oMensajeResultadoB);
        }

        // Pruebas de reverso automático
        if (in_array($sPrueba, ['26', '27', '28', '29', '30', '31', '3v', '4v', '5v', '6v'])) {
            $this->oPeticionCargo->descripcion = $this->oPeticionCargo->descripcion . ' - Reverso Automático';
            $aOpcionesReverso = [
                'tipo' => 'reverso',
                'tipo_original' => $aOpciones['tipo'] ?? 'puntos_compra',
                'mti_original' => $aResultado['peticion']['iso_mti'], // ?  Id de mensaje ISO de la transacción original
                'referencia' => $aResultado['peticion']['iso_parsed']['37'], // Campo 37
                'autorizacion' => $aResultado['respuesta']['iso_parsed']['38'] ?? '      ', // Campo 38 de la respuesta
                'fecha_original' => $aResultado['peticion']['iso_parsed']['13'], // CAMPO 13 de la respuesta
                'hora_original' => $aResultado['peticion']['iso_parsed']['12'], // CAMPO 12 de la respuesta
                'fecha_captura_original' => $aResultado['peticion']['iso_parsed']['17'], // CAMPO 17 de la respuesta
            ];
            // Prepara mensaje reverso y envía
            $oInterredProxyReverso = new InterredProxy();
            $oResultadoReverso = $oInterredProxyReverso->mensajeReverso($this->oPeticionCargo, $aOpcionesReverso, true, $bEcho);
            if (isset($oResultadoReverso->respuesta)) {
                $aResultado['reverso'] = $oResultadoReverso->respuesta;
            } else {
                $aResultado['reverso'] = [
                    'mensaje_json' => $this->oPeticionCargo,
                ];
            }
            // Prepara mensaje reenvío de reverso y envía
            $oResultadoReversoRet = $oInterredProxyReverso->mensajeReenvioReverso($this->oPeticionCargo, $aOpcionesReverso, true, $bEcho);
            if (isset($oResultadoReversoRet->respuesta)) {
                $aResultado['reverso_reenvio'] = $oResultadoReversoRet->respuesta;
            }
        }

        return $aResultado;
    }

    private function obtienePlan($sCampo63): array
    {
        $aPlan = [
            'plan' => '00',
            'parcialidades' => '00',
            'diferido' => '00',
        ];
        if (!empty($sCampo63)) {
            // Obtiene data de tokens
            $sTokens = substr($sCampo63, 15);
            // Separa campo de tokens
            $aTmpTokens = explode('! ', $sTokens);
            $aTokens = [];
            foreach($aTmpTokens as $sTmpToken) {
                $aTokens[substr($sTmpToken, 0, 2)] = substr($sTmpToken, 2);
            }
            // Obtiene plan de pagos
            if (isset($aTokens['Q6'])) {
                $aPlan['plan'] = substr($aTokens['Q6'], -2, 2);
                $aPlan['parcialidades'] = substr($aTokens['Q6'], 8, 2);
                $aPlan['diferido'] = substr($aTokens['Q6'], 6, 2);
            }
        }
        return $aPlan;
    }
}

class InterredProxy
{
    protected $oEglobalProxyCliente;
    protected $config = [
        'proxy' => [
            'ip' => '127.0.0.1', // '172.26.202.4'
            'port' => 8300, // 8315
            'proxy' => true,
            'timeout' => 25,
        ],
        'afiliacion' => '5462742',
    ];
    private $aCatalogoRespuestas = [
        '00' => 'Aprobada',
        '01' => 'Referida',
        '03' => 'Negocio Inválido',
        '04' => 'Recoger Tarjeta',
        '05' => 'Rechazada',
        '12' => 'Transacciòn inválida (FallBack)',
        '13' => 'Monto Inválido',
        '14' => 'Tarjeta Inválida (Invalid card number)',
        '30' => 'Error de formato',
        '40' => 'Función no soportada',
        '41' => 'Recoger Tarjeta (Lost Card)',
        '43' => 'Recoger Tarjeta (Stolen Card)',
        '45' => 'Promoción no permitida',
        '46' => 'Monto inferior mín promo',
        '47' => 'Transacción no realizada por haber excedido su límite permitido. Acuda a una sucursal bancaria.',
        '48' => 'CV2 Requerido',
        '49' => 'CV2 Inválido',
        '50' => 'Ha superado el número de transacciones rechazadas.',
        '51' => 'Saldo insuficiente',
        '53' => 'Cuenta inexistente',
        '54' => 'Tarjeta Expirada',
        '55' => 'NIP incorrecto',
        '57' => 'Comercio No Marcado / Marca De Cash Back O Advance No Permitida',
        '61' => 'Excede límite de monto de retiro',
        '62' => 'Bin De Tarjeta No Permitido',
        '65' => 'Intentos De Retiros Excedido',
        '69' => 'Número Celular no Asociado a Cuenta Express.',
        '70' => 'Error descifrando Track2',
        '71' => 'Debe inicializar llaves',
        '72' => 'Problema inicializando Llaves',
        '73' => 'Error en CRC',
        '75' => 'Número de intentos de NIP excedidos',
        '76' => 'Cuenta bloqueada',
        '82' => 'CVV/CVV2 incorrecto',
        '83' => 'Rechazada',
        '93' => 'Operación no disponible',
        'A3' => 'Límite de saldo superado con depósito',
        'A4' => 'Con este depósito excede el límite permitido para este producto por mes',
        'B1' => 'Transacción con datos de Campaña',
        'B2' => 'Servicio No Disponible. Promociones Especiales',
        'C1' => 'Producto no definido',
        'C2' => 'Producto vendido',
        'C3' => 'Producto invalido para venta',
        'C4' => 'Promoción Finalizada',
        'C5' => 'Sin autorización de venta',
        'C6' => 'Venta no permitida de producto',
        'C7' => 'Venta no permitida por tipo de transacción',
        'C8' => 'Plazos no definidos',
        'C9' => 'Número máximo de venta',
        'CA' => 'Monto de transacción invalido',
        'CB' => 'Producto no puede ser devuelto',
    ];

    // ********************************************************************************************************************

    private function preparaMensaje($sIso, $bEcho = false)
    {
        $sMensaje = 'ISO023400070' . $sIso;
        return $this->isoLength(strlen('00' . $sMensaje)) . $sMensaje;
    }

    private function isoLength(int $size)
    {
        $part = str_split(sprintf("%04s", dechex($size)), 2);
        return chr(hexdec($part[0])) . chr(hexdec($part[1]));
    }

    private function isoTipoCompra(PeticionCargo $oPeticionCargo, array $aTipo = [], $bEcho = false)
    {
        // Define campos
        $oMensaje = new Mensaje();
        $oMensaje->setMTI('0200');
        $oMensaje->setData(3, $oMensaje->formateaCampo3($aTipo)); // Processing Code
        $oMensaje->setData(4, $oMensaje->formateaCampo4($oPeticionCargo->monto)); // Transaction Amount - Monto de la transacción con centavos
        $oMensaje->setData(7, gmdate('mdHis')); // Date & time
        $oMensaje->setData(11, $oMensaje->generateSystemsTraceAuditNumber()); // Systems Trace Audit Number
        $oMensaje->setData(12, date('His')); // Hora local de la transacción
        $oMensaje->setData(13, date('md')); // Date & time - Día local de la transacción
        $oMensaje->setData(17, date('md')); // Date & time - Día en el cual la transacción es registrada por el Adquirente
        $oMensaje->setData(22, '012'); // PoS Entry Mode
        #$oMensaje->setData(23, ''); //
        if (in_array($oPeticionCargo->plan->plan ?? [], ['msi', 'mci', 'diferido'])) {
            // Deaparece el campo 25... Puff! Motivo: Pos nomas.
        } else {
            $oMensaje->setData(25, '59'); // Point of Service Condition Code - 59 = Comercio Electrónico
        }
        $oMensaje->setData(32, '12'); // Acquiring Institution Identification Code
        $oMensaje->setData(35, $oMensaje->formateaCampo35($oPeticionCargo->tarjeta)); // Track 2 Data
        $oMensaje->setData(37, date('ymdhis')); // Retrieval Reference Number
        // A petición de eglobal
        //if (!empty($aTipo['tipo']) && $aTipo['tipo'] == 'devolucion' && !empty($aTipo['autorizacion'])) {
        //    $oMensaje->setData(38, $aTipo['autorizacion']); // Authorization Identification Response
        //}
        $oMensaje->setData(41, '0000CP01        '); // Card Acceptor Terminal Identification
        //$oMensaje->setData(43, 'Radiomovil DIPSA SA CVCMXCMXMX'); //  Card Acceptor Name/Location
        $oMensaje->setData(48, '5462742            00000000'); // Additional DataRetailer Data - Define la afiliación del Establecimiento
        $oMensaje->setData(49, '484'); // Transaction Currency Code.
        if (!empty($aTipo['tipo']) && $aTipo['tipo'] == 'puntos_compra') {
            if (in_array($oPeticionCargo->plan->plan ?? [], ['msi', 'mci', 'diferido'])) {
                $oMensaje->setData(58, $oMensaje->formateaCampo58(['importe_total' => 0, 'importe_puntos' => 0]));
            } else {
                $oMensaje->setData(58,
                $oMensaje->formateaCampo58([
                    'importe_total' => $oMensaje->formateaCampo4($oPeticionCargo->monto),
                    'importe_puntos' => $oPeticionCargo->puntos,
                ]));
            }
        }
        #$oMensaje->setData(54, '000000000000')); // Additional Amounts - Monto del cash advance/back con centavos
        #$oMensaje->setData(55, ''); //
        #$oMensaje->setData(59, ''); //
        $oMensaje->setData(60, 'CLPGTES1+0000000'); // POS Terminal Data
        $oMensaje->setData(63, $oMensaje->formateaCampo63([
            'mti' => '0200',
            // C0
            'cvv2' => $oPeticionCargo->tarjeta->cvv2,
            'indicador_cvv2' => $oPeticionCargo->tarjeta->cvv2 ? 'presente' : 'no_presente',
            // Q6
            'parcialidades' => $oPeticionCargo->plan->parcialidades ?? 0,
            'diferimiento' => $oPeticionCargo->diferido ?? 0,
            'plan' => $oPeticionCargo->plan->plan ?? 's',
        ], $aTipo)); // POS Additional Data
        #$oMensaje->setData(103, ''); //
        if ($bEcho) {
            echo "<pre>" . print_r($oMensaje->getDataArray(), true) . "</pre>";
        }

        return $oMensaje;
    }

    private function isoTipoCancelacion(PeticionCargo $oPeticionCargo, array $aTipo = [], $bEcho = false)
    {
        // Define campos
        $oMensaje = new Mensaje();
        $oMensaje->setMTI('0420');
        $oMensaje->setData(3, $oMensaje->formateaCampo3($aTipo)); // Processing Code
        $oMensaje->setData(4, $oMensaje->formateaCampo4($oPeticionCargo->monto)); // Transaction Amount - Monto de la transacción con centavos
        $oMensaje->setData(7, gmdate('mdHis')); // Date & time
        $oMensaje->setData(11, $oMensaje->generateSystemsTraceAuditNumber()); // Systems Trace Audit Number
        $oMensaje->setData(12, date('His')); // Hora local de la transacción
        $oMensaje->setData(13, date('md')); // Date & time - Día local de la transacción
        $oMensaje->setData(15, date('md')); // Settlement Date: MMDD - Día en el cual se esta contabilizando la transacción
        $oMensaje->setData(17, date('md')); //  Capture Date: MMDD - Día en el cual la transacción es registrada por el Adquirente
        $oMensaje->setData(22, '012'); // PoS Entry Mode
        $oMensaje->setData(25, '59'); // Point of Service Condition Code - 59 = Comercio Electrónico
        $oMensaje->setData(32, '12'); // Acquiring Institution Identification Code
        $oMensaje->setData(35, $oMensaje->formateaCampo35($oPeticionCargo->tarjeta)); // Track 2 Data
        $oMensaje->setData(37, $aTipo['referencia']); // Retrieval Reference Number
        $oMensaje->setData(38, $aTipo['autorizacion'] ?? '      '); // Authorization Identification Response
        $oMensaje->setData(39, $oMensaje->formateaCampo39($aTipo['cancelacion_motivo'] ?? 'cancelacion')); // Motivo cancelación
        $oMensaje->setData(41, '0000CP01        '); // Card Acceptor Terminal Identification
        $oMensaje->setData(48, '5462742            00000000'); // Additional DataRetailer Data - Define la afiliación del Establecimiento
        $oMensaje->setData(49, '484'); // Transaction Currency Code.
        #$oMensaje->setData(54, '000000000000')); // Additional Amounts - Monto del cash advance/back con centavos NO IMPLEMENTAOD
        if (isset($aTipo['cancelacion_motivo']) && $aTipo['cancelacion_motivo'] == 'emv_fail') {
            $oMensaje->setData(55, ''); // NO IMPLEMENTAOD
        }
        #$oMensaje->setData(59, ''); // NO IMPLEMENTAOD
        $oMensaje->setData(60, 'CLPGTES1+0000000'); // POS Terminal Data
        $oMensaje->setData(63,
            $oMensaje->formateaCampo63([
                'mti' => '0200',
                // C0
                'cvv2' => $oPeticionCargo->tarjeta->cvv2,
                'indicador_cvv2' => $oPeticionCargo->tarjeta->cvv2 ? 'presente' : 'no_presente',
                // Q6
                'parcialidades' => $oPeticionCargo->plan->parcialidades ?? 0,
                'diferimiento' => $oPeticionCargo->plan->diferido ?? 0,
                'plan' => $oPeticionCargo->plan->plan ?? '',
            ], $aTipo)); // POS Additional Data
        $oMensaje->setData(90, $oMensaje->formateaCampo90($aTipo)); // Motivo cancelación
        #$oMensaje->setData(103, ''); //
        if ($bEcho) {
            echo "<pre>" . print_r($oMensaje->getDataArray(), true) . "</pre>";
        }
        return $oMensaje;
    }

    private function isoTipoReverso(PeticionCargo $oPeticionCargo, array $aOpciones = [], $bEcho = false)
    {
        if (isset($aOpciones['autorizacion']) && $aOpciones['autorizacion'] == '000000') { $aOpciones['autorizacion'] = '      '; }
        // Define campos
        $oMensaje = new Mensaje();
        $oMensaje->setMTI('0420');
        $oMensaje->setData(3, $oMensaje->formateaCampo3(['tipo' => $aOpciones['tipo_original'] ?? 'puntos_compra'])); // Processing Code
        $oMensaje->setData(4, $oMensaje->formateaCampo4($oPeticionCargo->monto)); // Transaction Amount - Monto de la transacción con centavos
        $oMensaje->setData(7, gmdate('mdHis')); // Date & time
        $oMensaje->setData(11, $oMensaje->generateSystemsTraceAuditNumber()); // Systems Trace Audit Number
        $oMensaje->setData(12, date('His')); // Hora local de la transacción
        $oMensaje->setData(13, date('md')); // Date & time - Día local de la transacción
        $oMensaje->setData(15, date('md')); // Settlement Date: MMDD - Día en el cual se esta contabilizando la transacción
        $oMensaje->setData(17, date('md')); //  Capture Date: MMDD - Día en el cual la transacción es registrada por el Adquirente
        $oMensaje->setData(22, '012'); // PoS Entry Mode
        if (in_array($oPeticionCargo->plan->plan ?? [], ['msi', 'mci', 'diferido'])) {
            // Deaparece el campo 25... Puff! Motivo: Pos nomas.
        } else {
            $oMensaje->setData(25, '59'); // Point of Service Condition Code - 59 = Comercio Electrónico
        }
        $oMensaje->setData(32, '12'); // Acquiring Institution Identification Code
        $oMensaje->setData(35, $oMensaje->formateaCampo35($oPeticionCargo->tarjeta)); // Track 2 Data
        $oMensaje->setData(37, $aOpciones['referencia']); // Retrieval Reference Number
        $oMensaje->setData(38, $aOpciones['autorizacion'] ?? '      '); // Authorization Identification Response
        $oMensaje->setData(39, $oMensaje->formateaCampo39($aOpciones['cancelacion_motivo'] ?? 'timeout')); // Motivo cancelación
        $oMensaje->setData(41, '0000CP01        '); // Card Acceptor Terminal Identification
        $oMensaje->setData(48, '5462742            00000000'); // Additional DataRetailer Data - Define la afiliación del Establecimiento
        $oMensaje->setData(49, '484'); // Transaction Currency Code.
        #$oMensaje->setData(54, '000000000000')); // Additional Amounts - Monto del cash advance/back con centavos NO IMPLEMENTAOD
        if (isset($aOpciones['cancelacion_motivo']) && $aOpciones['cancelacion_motivo'] == 'emv_fail') {
            $oMensaje->setData(55, ''); // NO IMPLEMENTAOD
        }
        #$oMensaje->setData(59, ''); // NO IMPLEMENTAOD
        $oMensaje->setData(60, 'CLPGTES1+0000000'); // POS Terminal Data
        $oMensaje->setData(63,
            $oMensaje->formateaCampo63([
                'mti' => '0200',
                // C0
                'cvv2' => $oPeticionCargo->tarjeta->cvv2,
                'indicador_cvv2' => $oPeticionCargo->tarjeta->cvv2 ? 'presente' : 'no_presente',
                // Q6
                'parcialidades' => $oPeticionCargo->plan->parcialidades ?? 0,
                'diferimiento' => $oPeticionCargo->diferido,
                'plan' => $oPeticionCargo->plan->plan ?? '',
            ], $aOpciones)); // POS Additional Data
        $oMensaje->setData(90, $oMensaje->formateaCampo90($aOpciones)); // Motivo cancelación
        #$oMensaje->setData(103, ''); //
        if ($bEcho) {
            echo "<pre>" . print_r($oMensaje->getDataArray(), true) . "</pre>";
        }

        return $oMensaje;
    }

    private function isoTipoReenvioReverso(PeticionCargo $oPeticionCargo, array $aOpciones = [], $bEcho = false)
    {
        $oMensaje = $this->isoTipoReverso($oPeticionCargo, $aOpciones, $bEcho);
        // Define campos
        $oMensaje->setMTI('0421');
        // Debug
        if ($bEcho) {
            echo "<pre>" . print_r($oMensaje->getDataArray(), true) . "</pre>";
        }
        // Regresa mensaje
        return $oMensaje;
    }

    // ********************************************************************************************************************

    public function mensajeVenta(PeticionCargo $oPeticionCargo, array $aOpciones = [], $bEnvia = false, $bEcho = false)
    {
        if ($bEcho) {
            echo "<br>Preparando mensaje de Venta...";
        }
        // Actualiza opciones con default
        $aOpciones = array_merge(['tipo' => 'puntos_compra'], $aOpciones);
        // Define campos
        $sIso = $this->isoTipoCompra($oPeticionCargo, $aOpciones, $bEcho);
        $sMensaje = $this->preparaMensaje($sIso->getISO(false), $bEcho);
        // Evalua retorno
        $aResultado = [
            'mensaje' => $sMensaje,
            'mensaje_hex' => $this->ascii2hex($sMensaje),
            'iso' => $sIso,
        ];
        if ($bEnvia) {
            $aResultadoEnvio = $this->enviaMensaje(Uuid::generate(4)->string, $sIso->getValue(11), $sMensaje, $bEcho);
            #dump($aResultadoEnvio);
            if (!empty($aResultadoEnvio) && !empty($aResultadoEnvio['respuesta'])) {
                $aResultado['respuesta'] = [
                    'mensaje_b64' => $aResultadoEnvio['respuesta']->respuesta ?? 'ERROR',
                    'mensaje_hex' => $this->ascii2hex($aResultadoEnvio['respuesta']->respuesta ?? 'ERROR'),
                    'iso_header' => $aResultadoEnvio['respuesta_iso']['header'],
                    'iso_mti' => $aResultadoEnvio['respuesta_iso']['iso_mti'],
                    'iso_parsed' => $aResultadoEnvio['respuesta_iso']['iso_parsed'],
                    'iso_validation' => $aResultadoEnvio['respuesta_iso']['iso_validation'],
                ];
            }
        }
        // Regresa resultado
        return (object) $aResultado;
    }

    public function mensajeCancelacion(PeticionCargo $oPeticionCargo, array $aOpciones = [], $bEnvia = false, $bEcho = false)
    {
        if ($bEcho) {
            echo "<br>Preparando mensaje de Cancelación...";
        }
        // Actualiza opciones con default
        $aOpciones = array_merge(['tipo' => 'puntos_compra'], $aOpciones);
        // Define campos
        $sIso = $this->isoTipoCancelacion($oPeticionCargo, $aOpciones, $bEcho);
        $sMensaje = $this->preparaMensaje($sIso->getISO(false), $bEcho);
        // Evalua retorno
        $aResultado = [
            'mensaje' => $sMensaje,
            'mensaje_hex' => $this->ascii2hex($sMensaje),
            'iso' => $sIso,
        ];
        if ($bEnvia) {
            $aResultadoEnvio = $this->enviaMensaje(Uuid::generate(4)->string, $sIso->getValue(11), $sMensaje, $bEcho);
            #dump($aResultadoEnvio);
            if (!empty($aResultadoEnvio) && !empty($aResultadoEnvio['respuesta'])) {
                $aResultado['respuesta'] = [
                    'mensaje_b64' => $aResultadoEnvio['respuesta']->respuesta ?? 'ERROR',
                    'mensaje_hex' => $this->ascii2hex($aResultadoEnvio['respuesta']->respuesta ?? 'ERROR'),
                    'iso_header' => $aResultadoEnvio['respuesta_iso']['header'],
                    'iso_mti' => $aResultadoEnvio['respuesta_iso']['iso_mti'],
                    'iso_parsed' => $aResultadoEnvio['respuesta_iso']['iso_parsed'],
                    'iso_validation' => $aResultadoEnvio['respuesta_iso']['iso_validation'],
                ];
            }
        }
        // Regresa resultado
        return (object) $aResultado;
    }

    public function mensajeReverso(PeticionCargo $oPeticionCargo, array $aOpciones = [], $bEnvia = false, $bEcho = false)
    {
        if ($bEcho) {
            echo "<br>Preparando mensaje de Reverso...";
        }
        // Actualiza opciones con default
        $aOpciones = array_merge(['tipo' => 'puntos_compra'], $aOpciones);
        // Define campos
        $sIso = $this->isoTipoReverso($oPeticionCargo, $aOpciones, $bEcho);
        $sMensaje = $this->preparaMensaje($sIso->getISO(false), $bEcho);
        // Evalua retorno
        $aResultado = [
            'mensaje' => $sMensaje,
            'mensaje_hex' => $this->ascii2hex($sMensaje),
            'iso' => $sIso,
        ];
        if ($bEnvia) {
            $aResultadoEnvio = $this->enviaMensaje(Uuid::generate(4)->string, $sIso->getValue(11), $sMensaje, $bEcho);
            #dump($aResultadoEnvio);
            if (!empty($aResultadoEnvio) && !empty($aResultadoEnvio['respuesta'])) {
                $aResultado['respuesta'] = [
                    'mensaje_b64' => $aResultadoEnvio['respuesta']->respuesta ?? 'ERROR',
                    'mensaje_hex' => $this->ascii2hex($aResultadoEnvio['respuesta']->respuesta ?? 'ERROR'),
                    'iso_header' => $aResultadoEnvio['respuesta_iso']['header'],
                    'iso_mti' => $aResultadoEnvio['respuesta_iso']['iso_mti'],
                    'iso_parsed' => $aResultadoEnvio['respuesta_iso']['iso_parsed'],
                    'iso_validation' => $aResultadoEnvio['respuesta_iso']['iso_validation'],
                ];
            }
        }
        // Regresa resultado
        return (object) $aResultado;
    }

    public function mensajeReenvioReverso(PeticionCargo $oPeticionCargo, array $aOpciones = [], $bEnvia = false, $bEcho = false)
    {
        if ($bEcho) {
            echo "<br>Preparando mensaje de Reenvío de Reverso...";
        }
        // Actualiza opciones con default
        $aOpciones = array_merge(['tipo' => 'puntos_compra'], $aOpciones);
        // Define campos
        $sIso = $this->isoTipoReenvioReverso($oPeticionCargo, $aOpciones, $bEcho);
        $sMensaje = $this->preparaMensaje($sIso->getISO(false), $bEcho);
        // Evalua retorno
        $aResultado = [
            'mensaje' => $sMensaje,
            'mensaje_hex' => $this->ascii2hex($sMensaje),
            'iso' => $sIso,
        ];
        if ($bEnvia) {
            $aResultadoEnvio = $this->enviaMensaje(Uuid::generate(4)->string, $sIso->getValue(11), $sMensaje, $bEcho);
            #dump($aResultadoEnvio);
            if (!empty($aResultadoEnvio) && !empty($aResultadoEnvio['respuesta'])) {
                $aResultado['respuesta'] = [
                    'mensaje_b64' => $aResultadoEnvio['respuesta']->respuesta ?? 'ERROR',
                    'mensaje_hex' => $this->ascii2hex($aResultadoEnvio['respuesta']->respuesta ?? 'ERROR'),
                    'iso_header' => $aResultadoEnvio['respuesta_iso']['header'],
                    'iso_mti' => $aResultadoEnvio['respuesta_iso']['iso_mti'],
                    'iso_parsed' => $aResultadoEnvio['respuesta_iso']['iso_parsed'],
                    'iso_validation' => $aResultadoEnvio['respuesta_iso']['iso_validation'],
                ];
            }
        }
        // Regresa resultado
        return (object) $aResultado;
    }


    public function enviaMensaje(string $sId, string $sStan, string $sMensaje, $bEcho = false): array
    {
        // Prepara resultado
        $aResponseResult = [
            'status' => 'fail',
            'status_message' => 'Unknown error.',
            'status_code' => '520',
            'response' => null,
        ];

        if ($bEcho) {
            echo "<br>Enviando mensaje: " . $sMensaje;
        }

        // Conecta
        try {
            $this->oEglobalProxyCliente = stream_socket_client('tcp://' . $this->config['proxy']['ip'] . ':' . $this->config['proxy']['port'], $aResponseResult['status_code'],
            $aResponseResult['status_message'], $this->config['proxy']['timeout'], STREAM_CLIENT_CONNECT);
            if ($this->oEglobalProxyCliente === false) {
                throw new \Exception("Error al crear el socket: " . socket_strerror(socket_last_error()));
            }
            // Define timeout
            stream_set_timeout($this->oEglobalProxyCliente, $this->config['proxy']['timeout']);
            stream_set_blocking($this->oEglobalProxyCliente, 0);
            stream_set_read_buffer($this->oEglobalProxyCliente, 0);
            // Espera mensaje de conexión
            $oData = $this->recibeEglobalProxy($bEcho);
            if ($oData->conexion != 'success') {
                $aResponseResult['status_message'] = 'Error al conectarse a eglobalProxyServer';
                $aResponseResult['status_code'] = 502;
                return $aResponseResult;
            }
            // Prepara mensaje
            $aRequest = [
                'accion' => 'send',
                'transaccion_id' => $sId,
                'stan' => $sStan,
                'encoding' => 'base64',
                'mensaje_b64' => base64_encode($sMensaje),
            ];
            // Envía mensaje
            if ($bEcho) {
                echo "<br>Escribiendo en el socket: " . print_r($aRequest, true) . "\n";
            }
            $jRequest = json_encode($aRequest);
            $size = strlen($jRequest);
            if ($bEcho) {
                echo "<br>Escribiendo en el socket: " . print_r($jRequest, true) . "\n";
            }
            if (empty($size)) {
                if ($bEcho) {
                    echo "<br>Error: Mensaje a enviar vacío!\n";
                }
                $aResponseResult['status_message'] = 'Error: Mensaje a enviar vacío';
                $aResponseResult['status_code'] = 502;
                return $aResponseResult;
            }
            $bytes = fwrite($this->oEglobalProxyCliente, $jRequest, $size);
            if ($bytes === false || $bytes < $size) {
                if ($bEcho) {
                    echo "<br>Error al escribir en el socket!\n";
                }
                $aResponseResult['status_message'] = 'Error al escribir en el socket';
                $aResponseResult['status_code'] = 502;
                return $aResponseResult;
            } else {
                if ($bEcho) {
                    echo "<br>Mensaje enviado OK ({$bytes}).\n";
                }
            }
            // Espera respuesta
            $aMensajeISO = [];
            $oData = $this->recibeEglobalProxy($bEcho);
            // Procesa mensaje
            if (!empty($oData->respuesta)) {
                try {
                    if ($oData->encoding == 'base64') {
                        $sRespuesta = base64_decode($oData->respuesta);
                    } else {
                        $sRespuesta = $oData->respuesta;
                    }
                    if ($bEcho) {
                        dump($sRespuesta);
                    }
                    $oInterred = new BBVAInterred();
                    $aMensajeISO = $oInterred->procesaMensaje($sRespuesta);
                    if ($bEcho) {
                        echo "<br>Respuesta recibida (iso): <pre>" . print_r($aMensajeISO['iso_parsed'], true) . "</pre>";
                    }
                    #dump($aMensajeISO['iso_validation']);
                    #$jMensajeISO = json_encode($aMensajeISO['iso_parsed']);
                    #dump($jMensajeISO);
                    #echo "<br>Respuesta recibida (iso): {$jMensajeISO} \n";
                    // Evalua resultado campo 39
                    if (isset($aMensajeISO['iso_parsed'][39]) && isset($this->aCatalogoRespuestas[$aMensajeISO['iso_parsed'][39]])) {
                        if ($bEcho) {
                            echo "<br><h3>Resutado: " . $this->aCatalogoRespuestas[$aMensajeISO['iso_parsed'][39]] . " (" . $aMensajeISO['iso_parsed'][39] . ") </h3>\n";
                        }
                    }
                } catch (\Exception $e) {
                    #$jMensajeISO = "{}";

                }
            }
            // Prepara respuesta
            $aResponseResult['status_message'] = 'Mensaje enviado y respuesta recibida';
            $aResponseResult['status_code'] = 200;
            $aResponseResult['status'] = 'success';
            $aResponseResult['respuesta'] = $oData;
            $aResponseResult['respuesta_iso'] = $aMensajeISO;
            if (fclose($this->oEglobalProxyCliente) === false) {
                if ($bEcho) {
                    echo "<br>Error al cerrar el socket: " . socket_strerror(socket_last_error()) . "\n";
                }
                throw new \Exception("Error al cerrar el socket: " . socket_strerror(socket_last_error()));
            } else {
                $this->oEglobalProxyCliente = false;
                if ($bEcho) {
                    echo "<br>Socket cerrado OK.\n";
                }
            }
        } catch (\Exception $e) {
            if ($bEcho) {
                echo "<br>Error: " . $e->getMessage() . ". Archivo: " . $e->getFile() . " Línea " . $e->getLine();
            }
            $aResponseResult['status_message'] = $e->getMessage() . ". Archivo: " . $e->getFile() . " Línea " . $e->getLine();
            $aResponseResult['status_code'] = $e->getCode();
            return $aResponseResult;
        }
        return $aResponseResult;
    }

    public function recibeEglobalProxy($bEcho = false)
    {
        // Prepara variables
        $sMensaje = null;
        $oTime = Carbon::now();
        // Recibe datos
        if ($bEcho) {
            echo "\n<br>Esperando respuesta de eglobal proxy...";
        }
        while (empty($sMensaje)) {
            usleep(10000);
            $sMensaje = stream_get_contents($this->oEglobalProxyCliente);
            if ($oTime->diffInSeconds() > 30) {
                break;
            }
            // Mensaje raw
            if ($bEcho) {
                echo "\n<br>Respuesta recibida (str): " . $sMensaje;
            }
        }
        // Decodifica respuesta
        $jRespuesta = json_decode($sMensaje);
        #$this->loguea("    Respuesta recibida (str): " . $sMensaje, 'debug');
        #$this->loguea("    Respuesta recibida (hex): " . $this->ascii2hex($sMensaje), 'debug');
        if (!empty($jRespuesta) && !empty($jRespuesta->data)) {
            return $jRespuesta->data;
        } else {
            return $jRespuesta;
        }
    }

    public function ascii2hex($ascii, $bSeprator = true)
    {
        $hex = '';
        for ($i = 0; $i < strlen($ascii); $i++) {
            $byte = strtoupper(dechex(ord($ascii{$i})));
            $byte = str_repeat('0', 2 - strlen($byte)) . $byte;
            if ($bSeprator) {
                $hex .= $byte . " ";
            }
        }
        return $hex;
    }

}