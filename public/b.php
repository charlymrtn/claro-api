<?php

namespace app\Prueba\Bbva;

use App;
use Log;
use Carbon\Carbon;
use App\Classes\Pagos\Procesadores\Bbva\Mensaje;
use App\Classes\Pagos\Medios\TarjetaCredito;
use App\Classes\Pagos\Parametros\PeticionCargo;
use Webpatser\Uuid\Uuid;
use App\Classes\Pagos\Procesadores\Bbva\Interred as BBVAInterred;

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
        // Construye petición de cargo
        $this->oPeticionCargo = new PeticionCargo([
            'prueba' => true,
            'id' => Uuid::generate(4)->string,
            'tarjeta' => $this->oTarjetaCredito1,
            'monto' => 0,
            'puntos' => 0,
            'descripcion' => 'Prueba EGlobal BBVA',
            'parcialidades' => 0,
            'diferido' => 0,
            'comercio_uuid' => '176f76a8-2670-4288-9800-1dd5f031a57e',
        ]);
    }

    public function pruebas(string $sPrueba, string $sTipo = 'envio_online')
    {
        // Inicializa
        // Datos de pruebas
        if ($sPrueba == '1') {
            $this->oPeticionCargo->monto = 1830.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTAS NORMALES';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '2') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1; // Debito
            $this->oPeticionCargo->monto = 223.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTAS NORMALES';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '3') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1; // Debito
            $this->oPeticionCargo->monto = 532.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTAS NORMALES';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '4') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1;
            $this->oPeticionCargo->monto = 728.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTAS NORMALES';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '5') {
            $this->oTarjetaCredito1->cvv2 = '123';
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1;
            $this->oPeticionCargo->monto = 412.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VALIDACIÓN DE CVV2';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '6') {
            $this->oTarjetaDebito1->cvv2 = '';
            $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1; // Debito
            $this->oPeticionCargo->monto = 378.70;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VALIDACIÓN DE CVV2';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '7') {
            $this->oTarjetaDebito1->cvv2 = '456';
            $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1; // Debito
            $this->oPeticionCargo->monto = 812.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VALIDACIÓN DE CVV2';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '8') {
            $this->oTarjetaCredito1->cvv2 = '';
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 830.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VALIDACIÓN DE CVV2';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '9o') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 0.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTAS CON PUNTOS - Consulta de Puntos';
            $aOpciones = ['tipo' => 'puntos_consulta'];
        } else if ($sPrueba == '9') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 360;
            $this->oPeticionCargo->puntos = 360;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTAS CON PUNTOS - Venta con Puntos';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '10') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 1362.55;
            $this->oPeticionCargo->puntos = 1362.50;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTAS CON PUNTOS - Venta Con Puntos Mixta';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '11') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1; // Débito
            $this->oPeticionCargo->monto = 872.00;
            $this->oPeticionCargo->puntos = 0;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTAS CON PUNTOS - Venta con Puntos';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '12') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 508.00;
            $this->oPeticionCargo->parcialidades = 6;
            $this->oPeticionCargo->plan = 'msi';
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTA CON PROMOCIÓN - Venta 6 MSI';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '13') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 834.80;
            $this->oPeticionCargo->parcialidades = 12;
            $this->oPeticionCargo->plan = 'msi';
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTA CON PROMOCIÓN - Venta 12 MSI';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '14') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 935.00;
            $this->oPeticionCargo->parcialidades = 18;
            $this->oPeticionCargo->plan = 'msi';
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTA CON PROMOCIÓN - Venta 18 MSI';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '15') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 82.00;
            $this->oPeticionCargo->parcialidades = 6;
            $this->oPeticionCargo->plan = 'msi';
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTA CON PROMOCIÓN - 6 MSI - Declinada';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '16') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1;
            $this->oPeticionCargo->monto = 2542.00;
            $this->oPeticionCargo->parcialidades = 6;
            $this->oPeticionCargo->plan = 'msi';
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' VENTA CON PROMOCIÓN - 6 MSI - Declinada Débito';
            $aOpciones = ['tipo' => 'compra'];
        } else if ($sPrueba == '17') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1;
            $this->oPeticionCargo->monto = 223.00;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' DEVOLUCIONES - Devolución del caso 2';
            $aOpciones = ['tipo' => 'devolucion'];
        } else if ($sPrueba == '18') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 1362.55;
            //$this->oPeticionCargo->puntos = 1362.50;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' DEVOLUCIONES - Devolución del caso 10';
            $aOpciones = ['tipo' => 'devolucion'];
        } else if ($sPrueba == '19') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 834.80;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' DEVOLUCIONES - Devolución del caso 12?';
            $aOpciones = ['tipo' => 'devolucion'];
        } else if ($sPrueba == '20') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 350;
            $this->oPeticionCargo->puntos = 350;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' CANCELACIONES - Venta Con Puntos';
            $aOpciones = ['tipo' => 'puntos_compra'];
        } else if ($sPrueba == '20c') {
            $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
            $this->oPeticionCargo->monto = 350;
            $this->oPeticionCargo->puntos = 350;
            $this->oPeticionCargo->descripcion = 'Prueba ' . $sPrueba . ' CANCELACIONES - Venta Con Puntos - Cancelaión';
            $aOpciones = [
                'tipo' => 'cancelacion',
                'referencia' => '180410114851', // CAMPO 37 de la respuesta
                'autorizacion' => '210779', // CAMPO 38 de la respuesta
                'mti_original' => '0200', // ?  Id de mensaje ISO de la transacción original
                'fecha_original' => '0410', // CAMPO 13 de la respuesta
                'hora_original' => '042307', // CAMPO 12 de la respuesta
                'fecha_captura_original' => '0410', // CAMPO 17 de la respuesta
            ];
        } else {
            throw new \Exception("Prueba no encontrada", 404);
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
        if (isset($oResultado->respuesta)) {
            $aResultado['respuesta'] = $oResultado->respuesta;
        }
        return $aResultado;
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
        $oMensaje->setData(12, date('his')); // Hora local de la transacción
        $oMensaje->setData(13, date('md')); // Date & time - Día local de la transacción
        $oMensaje->setData(17, date('md')); // Date & time - Día en el cual la transacción es registrada por el Adquirente
        $oMensaje->setData(22, '012'); // PoS Entry Mode
        #$oMensaje->setData(23, ''); //
        if (in_array($oPeticionCargo->plan, ['msi', 'mci', 'diferido'])) {
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
            if (in_array($oPeticionCargo->plan, ['msi', 'mci', 'diferido'])) {
                $oMensaje->setData(58, $oMensaje->formateaCampo58(['importe_total' => 0, 'importe_puntos' => 0]));
            } else {
                $oMensaje->setData(58,
                $oMensaje->formateaCampo58([
                    'importe_total' => $oMensaje->formateaCampo4($oPeticionCargo->monto),
                    //'importe_puntos' => $oPeticionCargo->puntos, // Cantidad de puntos debe enviarse con ceros a petición de EGlobal y BBVA
                    'importe_puntos' => 0,
                ]));
            }
        }
        #$oMensaje->setData(54, '000000000000')); // Additional Amounts - Monto del cash advance/back con centavos
        #$oMensaje->setData(55, ''); //
        #$oMensaje->setData(59, ''); //
        $oMensaje->setData(60, 'CLPGTES1+0000000'); // POS Terminal Data
        $oMensaje->setData(63,
        $oMensaje->formateaCampo63([
            'mti' => '0200',
            // C0
            'cvv2' => $oPeticionCargo->tarjeta->cvv2,
            'indicador_cvv2' => $oPeticionCargo->tarjeta->cvv2 ? 'presente' : 'no_presente',
            // Q6
            'parcialidades' => $oPeticionCargo->parcialidades,
            'diferimiento' => $oPeticionCargo->diferido,
            'plan' => $oPeticionCargo->plan,
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
        $oMensaje->setData(12, date('his')); // Hora local de la transacción
        $oMensaje->setData(13, date('md')); // Date & time - Día local de la transacción
        $oMensaje->setData(15, date('md')); // Settlement Date: MMDD - Día en el cual se esta contabilizando la transacción
        $oMensaje->setData(17, date('md')); //  Capture Date: MMDD - Día en el cual la transacción es registrada por el Adquirente
        $oMensaje->setData(22, '012'); // PoS Entry Mode
        if (in_array($oPeticionCargo->plan, ['msi', 'mci', 'diferido'])) {
            // Deaparece el campo 25... Puff! Motivo: Pos nomas.
        } else {
            $oMensaje->setData(25, '59'); // Point of Service Condition Code - 59 = Comercio Electrónico
        }
        $oMensaje->setData(32, '12'); // Acquiring Institution Identification Code
        $oMensaje->setData(35, $oMensaje->formateaCampo35($oPeticionCargo->tarjeta)); // Track 2 Data
        $oMensaje->setData(37, $aTipo['referencia']); // Retrieval Reference Number
        $oMensaje->setData(38, $aTipo['autorizacion']); // Authorization Identification Response
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
                'parcialidades' => $oPeticionCargo->parcialidades,
                'diferimiento' => $oPeticionCargo->diferido,
                'plan' => $oPeticionCargo->plan,
            ], $aTipo)); // POS Additional Data
        $oMensaje->setData(90, $oMensaje->formateaCampo90($aTipo['cancelacion_motivo'] ?? 'cancelacion')); // Motivo cancelación
        #$oMensaje->setData(103, ''); //
        if ($bEcho) {
            echo "<pre>" . print_r($oMensaje->getDataArray(), true) . "</pre>";
        }

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
            $aResultadoEnvio = $this->enviaMensaje($oPeticionCargo->id, $sIso->getValue(11), $sMensaje, $bEcho);
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
            echo "<br>Preparando mensaje de Venta...";
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
            $aResultadoEnvio = $this->enviaMensaje($oPeticionCargo->id, $sIso->getValue(11), $sMensaje, $bEcho);
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
                echo "<br>Error: " . $e->getMessage() . ". Línea " . $e->getLine();;
            }
            $aResponseResult['status_message'] = $e->getMessage() . ". Línea " . $e->getLine();
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