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



    public function batch(string $fecha)
    {
        $i = 1;
        $sFecha = $fecha ?? '2018-05-11';
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


        $aTrx['180530'] = [
			// Prueba 1
            'AcdJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODA1MjE4MDAwMDAwMDAwMDE4MzQwMDA1MzAxNDE5NTgwNzkyMjQwOTE5NTgwNTMwMDUzMDA1MzAwMTI1OTIxNDc3MjEzNTAwMDAwMzU4ND0yMDA1MTgwNTMwMDkxOTU4NTc1MDAzMDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MTM5MDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAxMDAwMDAwMjAwMDAwMDAwMDAyMDAwMDAwMDAwMDIwMDAwMCAgICAgIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMjAxMDAwMDAwMDAwMDAxNTIwMDAwMDIwMDAwNDg0MDAwMDEyNCYgMDAwMDYwMDEyNCEgUTEwMDAwMiAwMCEgUTIwMDAwMiAwOSEgQzQwMDAxMiAxMDI1MTAwMDM2MDAhIDA0MDAwMjAgICAgICAgICAgICAgWSAgICAgICAhIEMwMDAwMjYgMzQwICAwMDEgICAgICAgICAgNyAgMSAwIDA=',
			// Prueba 2
            'ATlJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjAwMDAwMDAwMDAwMDAyMjYwMDA1MzAxNDIyNDY1ODI1NzQwOTIyNDYwNTMwMDUzMDA1MzAwMTI1OTIxNDE1MjMxNTAwMDEyMjY5Nz0yMDA1MTgwNTMwMDkyMjQ2NTc1MDA1MDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMTI0JiAwMDAwNjAwMTI0ISBRMTAwMDAyIDAwISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICBPICAgICAgICEgQzAwMDAyNiA0MjcgIDAwMSAgICAgICAgICA3ICAxIDAgMA==',
			// Prueba 3
            'ATlJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjAwMDAwMDAwMDAwMDA1MzY1NTA1MzAxNDI0MDAzNDQ2NDQwOTI0MDAwNTMwMDUzMDA1MzAwMTI1OTIxNDE1MjMxNTAwMDEyMjY5Nz0yMDA1MTgwNTMwMDkyNDAwNTc1MDA3MDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMTI0JiAwMDAwNjAwMTI0ISBRMTAwMDAyIDAwISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICBPICAgICAgICEgQzAwMDAyNiA0MjcgIDAwMSAgICAgICAgICA3ICAxIDAgMA==',
			// Prueba 4
            'AcdJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODA1MjE4MDAwMDAwMDAwMDA3MzIwMDA1MzAxNDI1NTIzNzA1MzYwOTI1NTIwNTMwMDUzMDA1MzAwMTI1OTIxNDc3MjEzNTAwMDAwMzU4ND0yMDA1MTgwNTMwMDkyNTUyNTc1MDA5MDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MTM5MDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAxMDAwMDAwMjAwMDAwMDAwMDAyMDAwMDAwMDAwMDIwMDAwMCAgICAgIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMjAxMDAwMDAwMDAwMDAxNTIwMDAwMDIwMDAwNDg0MDAwMDEyNCYgMDAwMDYwMDEyNCEgUTEwMDAwMiAwMCEgUTIwMDAwMiAwOSEgQzQwMDAxMiAxMDI1MTAwMDM2MDAhIDA0MDAwMjAgICAgICAgICAgICAgWSAgICAgICAhIEMwMDAwMjYgMzQwICAwMDEgICAgICAgICAgNyAgMSAwIDA=',
			// Prueba 9
            'AcdJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODA1MjE4MDAwMDAwMDAwMDAzNjQwMDA1MzAxNTA4NTcwMjgzNTkxMDA4NTcwNTMwMDUzMDA1MzAwMTI1OTIxNDc3MjEzNTAwMDAwMzU4ND0yMDA1MTgwNTMwMTAwODU3NTc1MDI2MDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MTM5MDAwMDAwMDM2NDAwMDAwMDAwMzY0MDAxMDAwMDAwMTYzNjAwMDAwMDAyMDAwMDAwMDAwMDIwMDAwMCAgICAgIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMjAxMDAwMDAwMDAwMDAxNTIwMDAwMDE2MzYwNDg0MDAwMDEyNCYgMDAwMDYwMDEyNCEgUTEwMDAwMiAwMCEgUTIwMDAwMiAwOSEgQzQwMDAxMiAxMDI1MTAwMDM2MDAhIDA0MDAwMjAgICAgICAgICAgICAgWSAgICAgICAhIEMwMDAwMjYgMzQwICAwMDEgICAgICAgICAgNyAgMSAwIDA=',
			// Prueba 10
            'AcdJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODA1MjE4MDAwMDAwMDAwMDEzNjY1NTA1MzAxNTEzMTg5NDM1NzYxMDEzMTgwNTMwMDUzMDA1MzAwMTI1OTIxNDc3MjEzNTAwMDAwMzU4ND0yMDA1MTgwNTMwMTAxMzE4NTc1MDMxMDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MTM5MDAwMDAwMTM2NjUwMDAwMDAxMzY2NTAxMDAwMDAwMDI2OTUwMDAwMDAxNjM2MDAwMDAwMDE2MzYwMCAgICAgIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMjAxMDAwMDAwMDAwMDAxNTIwMDAwMDAyNjk1NDg0MDAwMDEyNCYgMDAwMDYwMDEyNCEgUTEwMDAwMiAwMCEgUTIwMDAwMiAwOSEgQzQwMDAxMiAxMDI1MTAwMDM2MDAhIDA0MDAwMjAgICAgICAgICAgICAgWSAgICAgICAhIEMwMDAwMjYgMzQwICAwMDEgICAgICAgICAgNyAgMSAwIDA=',
			// Prueba 11
            'ATlJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjAwMDAwMDAwMDAwMDA4NzYwMDA1MzAxNTE0Mzc5MDc3MzcxMDE0MzcwNTMwMDUzMDA1MzAwMTI1OTIxNDE1MjMxNTAwMDEyMjY5Nz0yMDA1MTgwNTMwMTAxNDM3NTc1MDM0MDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMTI0JiAwMDAwNjAwMTI0ISBRMTAwMDAyIDAwISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICBPICAgICAgICEgQzAwMDAyNiA0MjcgIDAwMSAgICAgICAgICA3ICAxIDAgMA==',
			// Prueba 12
            'AdVJU08wMjQwMDAwNzcwMjEwMzIzQTg0MDAyRTgxODA1MjE4MDAwMDAwMDAwMDA1MTIwMDA1MzAxNTE2MDY4NTgyODQxMDE2MDYwNTMwMDUzMDA1MzAwMTIyMTQ3NzIxMzUwMDAwMDM1ODQ9MjAwNTE4MDUzMDEwMTYwNjU3NTAzNjAwMDAwMENQMDEgICAgICAgIDAyNzU0NjI3NDIgICAgICAgICAgICAwMDAwMDAwMDQ4NDEzOTAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMTAwMDAwMDAyNjk1MDAwMDAwMDI2OTUwMDAwMDAwMjY5NTAgICAgICAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDIwMTAwMDAwMDAwMDAwMTUyMDAwMDAwMjY5NTQ4NDAwMDAxNDAmIDAwMDA3MDAxNDAhIFExMDAwMDIgMDAhIFEyMDAwMDIgMDkhIFE2MDAwMDYgMDAwNjAzISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICBZICAgICAgICEgQzAwMDAyNiAzNDAgIDAwMSAgICAgICAgICA3ICAxIDAgMA==',
			// Prueba 12 repetida
            'AdVJU08wMjQwMDAwNzcwMjEwMzIzQTg0MDAyRTgxODA1MjE4MDAwMDAwMDAwMDA1MTIwMDA1MzAxNTE5MTEyNjEwODUxMDE5MTEwNTMwMDUzMDA1MzAwMTIyMTQ3NzIxMzUwMDAwMDM1ODQ9MjAwNTE4MDUzMDEwMTkxMTU3NTAzODAwMDAwMENQMDEgICAgICAgIDAyNzU0NjI3NDIgICAgICAgICAgICAwMDAwMDAwMDQ4NDEzOTAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMTAwMDAwMDAyNjk1MDAwMDAwMDI2OTUwMDAwMDAwMjY5NTAgICAgICAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDIwMTAwMDAwMDAwMDAwMTUyMDAwMDAwMjY5NTQ4NDAwMDAxNDAmIDAwMDA3MDAxNDAhIFExMDAwMDIgMDAhIFEyMDAwMDIgMDkhIFE2MDAwMDYgMDAwNjAzISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICBZICAgICAgICEgQzAwMDAyNiAzNDAgIDAwMSAgICAgICAgICA3ICAxIDAgMA==',
			// Prueba 13
            'AdVJU08wMjQwMDAwNzcwMjEwMzIzQTg0MDAyRTgxODA1MjE4MDAwMDAwMDAwMDA4Mzg4MDA1MzAxNTIxNTc2NDU5NzUxMDIxNTcwNTMwMDUzMDA1MzAwMTIyMTQ3NzIxMzUwMDAwMDM1ODQ9MjAwNTE4MDUzMDEwMjE1NzU3NTA0MjAwMDAwMENQMDEgICAgICAgIDAyNzU0NjI3NDIgICAgICAgICAgICAwMDAwMDAwMDQ4NDEzOTAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMTAwMDAwMDAyNjk1MDAwMDAwMDI2OTUwMDAwMDAwMjY5NTAgICAgICAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDIwMTAwMDAwMDAwMDAwMTUyMDAwMDAwMjY5NTQ4NDAwMDAxNDAmIDAwMDA3MDAxNDAhIFExMDAwMDIgMDAhIFEyMDAwMDIgMDkhIFE2MDAwMDYgMDAxMjAzISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICBZICAgICAgICEgQzAwMDAyNiAzNDAgIDAwMSAgICAgICAgICA3ICAxIDAgMA==',
			// Prueba 14
            'AdVJU08wMjQwMDAwNzcwMjEwMzIzQTg0MDAyRTgxODA1MjE4MDAwMDAwMDAwMDA5MzkwMDA1MzAxNTIzMjMzMDI4ODgxMDIzMjMwNTMwMDUzMDA1MzAwMTIyMTQ3NzIxMzUwMDAwMDM1ODQ9MjAwNTE4MDUzMDEwMjMyMzU3NTA0NDAwMDAwMENQMDEgICAgICAgIDAyNzU0NjI3NDIgICAgICAgICAgICAwMDAwMDAwMDQ4NDEzOTAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMTAwMDAwMDAyNjk1MDAwMDAwMDI2OTUwMDAwMDAwMjY5NTAgICAgICAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDIwMTAwMDAwMDAwMDAwMTUyMDAwMDAwMjY5NTQ4NDAwMDAxNDAmIDAwMDA3MDAxNDAhIFExMDAwMDIgMDAhIFEyMDAwMDIgMDkhIFE2MDAwMDYgMDAxODAzISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICBZICAgICAgICEgQzAwMDAyNiAzNDAgIDAwMSAgICAgICAgICA3ICAxIDAgMA==',
			// Prueba 17
            'APdJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjIwMDAwMDAwMDAwMDAyMjYwMDA1MzAxNTI4MDk2NzU3NjkxMDI4MDkwNTMwMDUzMDA1MzAwMTI1OTIxNDE1MjMxNTAwMDEyMjY5Nz0yMDA1MTgwNTMwMTAyODA5MjMwMzQzMDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMDU4JiAwMDAwNDAwMDU4ISBRMTAwMDAyIDkgISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMA==',
			// Prueba 18
            'APdJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjIwMDAwMDAwMDAwMDEzNjY1NTA1MzAxNTI5MzIzMTYwMTExMDI5MzIwNTMwMDUzMDA1MzAwMTI1OTIxNDc3MjEzNTAwMDAwMzU4ND0yMDA1MTgwNTMwMTAyOTMyMjMwMzQ0MDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMDU4JiAwMDAwNDAwMDU4ISBRMTAwMDAyIDkgISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMA==',
			// Prueba 19
            'APdJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjIwMDAwMDAwMDAwMDA1MTIwMDA1MzAxNTMwMzk3MjEyODYxMDMwMzkwNTMwMDUzMDA1MzAwMTI1OTIxNDc3MjEzNTAwMDAwMzU4ND0yMDA1MTgwNTMwMTAzMDM5MjMwMzQ1MDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMDU4JiAwMDAwNDAwMDU4ISBRMTAwMDAyIDkgISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMA==',
			// Prueba 1m
            'ATlJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjAwMDAwMDAwMDAwMDAyMzE1MDA1MzAxNDU5MDQ0MjE4OTkwOTU5MDQwNTMwMDUzMDA1MzAwMTI1OTIxNTQxMzMzMDA4OTAyMDAxMT0yNTEyMTgwNTMwMDk1OTA0MDA1NzY5MDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMTI0JiAwMDAwNjAwMTI0ISBRMTAwMDAyIDkgISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICAgICAgICAgICEgQzAwMDAyNiA2MDEgIDAwMSAgICAgICAgICA3ICAxIDAgIA==',
			// Prueba 2m
            'ATlJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjAwMDAwMDAwMDAwMDAzMDYwMDA1MzAxNTAwMTk1Njk2ODcxMDAwMTkwNTMwMDUzMDA1MzAwMTI1OTIxNTQxMzMzMDA4OTAyMDA3OD0yNTEyMTgwNTMwMTAwMDE5MDA1NzcwMDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMTI0JiAwMDAwNjAwMTI0ISBRMTAwMDAyIDkgISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICAgICAgICAgICEgQzAwMDAyNiAyMDEgIDAwMSAgICAgICAgICA3ICAxIDAgIA==',
			// Prueba 3m
            'ATlJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjAwMDAwMDAwMDAwMDAxNjAwMDA1MzAxNTAyMDUzODQ1MDMxMDAyMDUwNTMwMDUzMDA1MzAwMTI1OTIxNTQxMzMzMDA4OTAyMDA4Nj0yNTEyMTgwNTMwMTAwMjA1MDA1NzcxMDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMTI0JiAwMDAwNjAwMTI0ISBRMTAwMDAyIDkgISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICAgICAgICAgICEgQzAwMDAyNiAyMDEgIDAwMSAgICAgICAgICA3ICAxIDAgIA==',
			// Prueba 4m
            'ATlJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjAwMDAwMDAwMDAwMDEzMTAwMDA1MzAxNTAzNDI5MzY2NDcxMDAzNDIwNTMwMDUzMDA1MzAwMTI1OTIxNTQxMzMzMDA4OTAxMDQ4Mz0yNTEyMTgwNTMwMTAwMzQyMDA1NzcyMDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMTI0JiAwMDAwNjAwMTI0ISBRMTAwMDAyIDkgISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICAgICAgICAgICEgQzAwMDAyNiAyMDEgIDAwMSAgICAgICAgICA3ICAxIDAgIA==',
			// Prueba 5m
            'ATlJU08wMjQwMDAwNzcwMjEwMzIzQTg0ODAyRTgxODAxMjAwMDAwMDAwMDAwMDA0NTUwMDA1MzAxNTA0NTMwMzI1NzUxMDA0NTMwNTMwMDUzMDA1MzAwMTI1OTIxNTQxMzMzMDA4OTAxMDQ0Mj0yNTEyMTgwNTMwMTAwNDUzMDA1NzczMDAwMDAwQ1AwMSAgICAgICAgMDI3NTQ2Mjc0MiAgICAgICAgICAgIDAwMDAwMDAwNDg0MDAwMTI0JiAwMDAwNjAwMTI0ISBRMTAwMDAyIDkgISBRMjAwMDAyIDA5ISBDNDAwMDEyIDEwMjUxMDAwMzYwMCEgMDQwMDAyMCAgICAgICAgICAgICAgICAgICAgICEgQzAwMDAyNiAyMDEgIDAwMSAgICAgICAgICA3ICAxIDAgIA==',
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
                    sprintf("% 25s", 'CLARO PAGOS'), // [25] Nombre del comercio para cuando la interred es agregador o integrador Nombre de agregador seguido de un asterisco (*), seguido del nombre del Sub Agregado (7*17) ejemplo (Aaaaaaa*Bbbbbbbbbbbbbb) En caso de no ser agregador debe de llegar informado con espacios en blanco (si aplica) ID
                    sprintf("%- 8s", substr($res[60], 0, 4) . substr($res[60], 12, 4)), // [8] Id del dispositivo o punto de venta informado de derecha a izquierda
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
