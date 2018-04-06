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
            'monto' =>  0,
            'puntos' => 0,
            'descripcion' => 'Prueba EGlobal BBVA',
            'parcialidades' => 0,
            'diferido' => 0,
            'comercio_uuid' => '176f76a8-2670-4288-9800-1dd5f031a57e',
        ]);
    }

    public function prueba1()
    {
        $iPrueba = 1;
        echo "Prueba {$iPrueba}";
        // Datos de prueba
        $this->oPeticionCargo->monto = 1830.00;
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, true);
        // Regresa resultados
        return $oResultado;
    }

    public function prueba2()
    {
        $iPrueba = 2;
        echo "Prueba {$iPrueba}";
        // Datos de prueba
        $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1; // Debito
        $this->oPeticionCargo->monto = 223.00;
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, ['tipo' => 'compra'], true);
        // Regresa resultados
        return $oResultado;
    }

    public function prueba3()
    {
        $iPrueba = 3;
        echo "Prueba {$iPrueba}";
        // Datos de prueba
        $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1; // Debito
        $this->oPeticionCargo->monto = 532.00;
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, ['tipo' => 'compra'], true);
        // Regresa resultados
        return $oResultado;
    }

    public function prueba4()
    {
        $iPrueba = 4;
        echo "Prueba {$iPrueba}";
        // Datos de prueba
        $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1;
        $this->oPeticionCargo->monto = 728.00;
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, ['tipo' => 'puntos_compra'], true);
        // Regresa resultados
        return $oResultado;
    }

    // VALIDACIÓN DE CVV2

    public function prueba5()
    {
        $iPrueba = 5;
        echo "Prueba {$iPrueba}: VALIDACIÓN DE CVV2";
        // Datos de prueba
        $this->oTarjetaCredito1->cvv2 = '123';
        $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1;
        $this->oPeticionCargo->monto = 412.00;
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, ['tipo' => 'puntos_compra'], true);
        // Regresa resultados
        return $oResultado;
    }

    public function prueba6()
    {
        $iPrueba = 6;
        echo "Prueba {$iPrueba}: VALIDACIÓN DE CVV2";
        // Datos de prueba
        $this->oTarjetaDebito1->cvv2 = '';
        $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1; // Debito
        $this->oPeticionCargo->monto = 378.70;
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, ['tipo' => 'compra'], true);
        // Regresa resultados
        return $oResultado;
    }

    public function prueba7()
    {
        $iPrueba = 7;
        echo "Prueba {$iPrueba}: VALIDACIÓN DE CVV2";
        // Datos de prueba
        $this->oTarjetaDebito1->cvv2 = '456';
        $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1; // Debito
        $this->oPeticionCargo->monto = 812.00;
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, ['tipo' => 'compra'], true);
        // Regresa resultados
        return $oResultado;
    }

    public function prueba8()
    {
        $iPrueba = 8;
        echo "Prueba {$iPrueba}: VALIDACIÓN DE CVV2";
        // Datos de prueba
        $this->oTarjetaCredito1->cvv2 = '';
        $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
        $this->oPeticionCargo->monto = 830.00;
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, ['tipo' => 'puntos_compra'], true);
        // Regresa resultados
        return $oResultado;
    }

    // VENTAS CON PUNTOS

    public function prueba9()
    {
        $iPrueba = 9;
        echo "Prueba {$iPrueba}: VENTAS CON PUNTOS - Consulta de Puntos";
        // Datos de prueba
        $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
        $this->oPeticionCargo->monto = 0.00;
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA: VENTAS CON PUNTOS - Consulta de Puntos';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, ['tipo' => 'puntos_consulta'], true);
        // Regresa resultados
        return $oResultado;
    }

    public function prueba10()
    {
        $iPrueba = 10;
        echo "Prueba {$iPrueba}: VENTAS CON PUNTOS - Venta con Puntos";
        // Datos de prueba
        $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
        $this->oPeticionCargo->monto = 360;
        $this->oPeticionCargo->puntos = 360;
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA: VENTAS CON PUNTOS - Consulta de Puntos';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, ['tipo' => 'puntos_compra'], true);
        // Regresa resultados
        return $oResultado;
    }

    public function prueba11()
    {
        $iPrueba = 11;
        echo "\n<br><h3>Prueba {$iPrueba}: VENTAS CON PUNTOS - Venta Con Puntos Mixta</h3>";
        // Datos de prueba
        $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
        $this->oPeticionCargo->monto = 1362.55;
        $this->oPeticionCargo->puntos = 1362.50;
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA: VENTAS CON PUNTOS - Venta Con Puntos Mixta';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, ['tipo' => 'puntos_compra'], true);
        // Regresa resultados
        return $oResultado;
    }

    public function prueba12()
    {
        $iPrueba = 12;
        echo "\n<br><h3>Prueba {$iPrueba}: VENTAS CON PUNTOS - Venta Con Puntos</h3>";
        // Datos de prueba
        $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1; // Débito
        $this->oPeticionCargo->monto = 872.00;
        $this->oPeticionCargo->puntos = 0;
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA: VENTAS CON PUNTOS - Venta Con Puntos';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, ['tipo' => 'compra'], true);
        // Regresa resultados
        return $oResultado;
    }

    // VENTA CON PROMOCIÓN

    public function prueba13()
    {
        $iPrueba = 13;
        echo "\n<br><h3>Prueba {$iPrueba}: VENTA CON PROMOCIÓN - Venta 3 MSI</h3>";
        // Datos de prueba
        $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
        $this->oPeticionCargo->monto = 4100.00;
        $this->oPeticionCargo->parcialidades = 3;
        $this->oPeticionCargo->plan = 'msi';
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA: VENTA CON PROMOCIÓN - Venta 3 MSI';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, ['tipo' => 'puntos_compra'], true);
        // Regresa resultados
        return $oResultado;
    }

    public function prueba14()
    {
        $iPrueba = 14;
        echo "\n<br><h3>Prueba {$iPrueba}: VENTA CON PROMOCIÓN - Venta 6 MSI</h3>";
        // Datos de prueba
        $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
        $this->oPeticionCargo->monto = 508.00;
        $this->oPeticionCargo->parcialidades = 6;
        $this->oPeticionCargo->plan = 'msi';
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA: VENTA CON PROMOCIÓN - Venta 6 MSI';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, ['tipo' => 'puntos_compra'], true);
        // Regresa resultados
        return $oResultado;
    }

    public function prueba15()
    {
        $iPrueba = 15;
        echo "\n<br><h3>Prueba {$iPrueba}: VENTA CON PROMOCIÓN - Venta 9 MSI</h3>";
        // Datos de prueba
        $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
        $this->oPeticionCargo->monto = 1154.00;
        $this->oPeticionCargo->parcialidades = 9;
        $this->oPeticionCargo->plan = 'msi';
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA: VENTA CON PROMOCIÓN - Venta 9 MSI';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, ['tipo' => 'puntos_compra'], true);
        // Regresa resultados
        return $oResultado;
    }

    public function prueba16()
    {
        $iPrueba = 16;
        echo "\n<br><h3>Prueba {$iPrueba}: VENTA CON PROMOCIÓN - Venta 12 MSI</h3>";
        // Datos de prueba
        $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
        $this->oPeticionCargo->monto = 834.80;
        $this->oPeticionCargo->parcialidades = 12;
        $this->oPeticionCargo->plan = 'msi';
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA: VENTA CON PROMOCIÓN - Venta 12 MSI';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, ['tipo' => 'puntos_compra'], true);
        // Regresa resultados
        return $oResultado;
    }

    public function prueba17()
    {
        $iPrueba = 17;
        echo "\n<br><h3>Prueba {$iPrueba}: VENTA CON PROMOCIÓN - Venta 15 MSI</h3>";
        // Datos de prueba
        $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
        $this->oPeticionCargo->monto = 2500.00;
        $this->oPeticionCargo->parcialidades = 15;
        $this->oPeticionCargo->plan = 'msi';
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA: VENTA CON PROMOCIÓN - Venta 15 MSI';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, ['tipo' => 'puntos_compra'], true);
        // Regresa resultados
        return $oResultado;
    }

    public function prueba18()
    {
        $iPrueba = 18;
        echo "\n<br><h3>Prueba {$iPrueba}: VENTA CON PROMOCIÓN - Venta 18 MSI</h3>";
        // Datos de prueba
        $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
        $this->oPeticionCargo->monto = 935.00;
        $this->oPeticionCargo->parcialidades = 18;
        $this->oPeticionCargo->plan = 'msi';
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA: VENTA CON PROMOCIÓN - Venta 18 MSI';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, ['tipo' => 'puntos_compra'], true);
        // Regresa resultados
        return $oResultado;
    }

    public function prueba19()
    {
        $iPrueba = 19;
        echo "\n<br><h3>Prueba {$iPrueba}: VENTA CON PROMOCIÓN - Venta 6 MSI - Declinada</h3>";
        // Datos de prueba
        $this->oPeticionCargo->tarjeta = $this->oTarjetaCredito1; // Credito
        $this->oPeticionCargo->monto = 82.00;
        $this->oPeticionCargo->parcialidades = 6;
        $this->oPeticionCargo->plan = 'msi';
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA: VENTA CON PROMOCIÓN - Venta 6 MSI - Declinada';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, ['tipo' => 'puntos_compra'], true);
        // Regresa resultados
        return $oResultado;
    }

    public function prueba20()
    {
        $iPrueba = 20;
        echo "\n<br><h3>Prueba {$iPrueba}: VENTA CON PROMOCIÓN - Venta 6 MSI - Declinada Débito</h3>";
        // Datos de prueba
        $this->oPeticionCargo->tarjeta = $this->oTarjetaDebito1; // Débito
        $this->oPeticionCargo->monto = 2542.00;
        $this->oPeticionCargo->parcialidades = 6;
        $this->oPeticionCargo->plan = 'msi';
        $this->oPeticionCargo->descripcion = 'Prueba ' . $iPrueba . ' EGlobal BBVA: VENTA CON PROMOCIÓN - Venta 6 MSI - Declinada Débito';
        // Prepara mensaje
        $oInterredProxy = new InterredProxy();
        $oResultado = $oInterredProxy->mensajeVenta($this->oPeticionCargo, ['tipo' => 'compra'], true);
        // Regresa resultados
        return $oResultado;
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

	private function preparaMensaje($sIso, $bEcho = true)
	{
		$sMensaje = 'ISO023400070' . $sIso;
		return $this->isoLength(strlen('00' . $sMensaje)) . $sMensaje;
	}

	private function isoLength(int $size)
	{
		$part = str_split(sprintf("%04s", dechex($size)), 2);
		return chr(hexdec($part[0])) . chr(hexdec($part[1]));
	}

	private function isoTipoCompra(PeticionCargo $oPeticionCargo, array $aTipo = [])
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
        // @todo: Cambiar por número único. Puede ser consecutivo o random
		$oMensaje->setData(41, '0000CP01        '); // Card Acceptor Terminal Identification
		//$oMensaje->setData(43, 'Radiomovil DIPSA SA CVCMXCMXMX'); //  Card Acceptor Name/Location
		$oMensaje->setData(48, '5462742            00000000'); // Additional DataRetailer Data - Define la afiliación del Establecimiento
		$oMensaje->setData(49, '484'); // Transaction Currency Code.
        if (!empty($aTipo['tipo']) && $aTipo['tipo'] == 'puntos_compra') {
            if (in_array($oPeticionCargo->plan, ['msi', 'mci', 'diferido'])) {
                $oMensaje->setData(58, $oMensaje->formateaCampo58(['importe_total' => 0, 'importe_puntos' => 0]));
            } else {
                $oMensaje->setData(58, $oMensaje->formateaCampo58([
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
		$oMensaje->setData(63, $oMensaje->formateaCampo63([
            'mti' => '0200',
            // C0
            'cvv2' => $oPeticionCargo->tarjeta->cvv2,
            'indicador_cvv2' => $oPeticionCargo->tarjeta->cvv2 ? 'presente' : 'no_presente',
            // Q6
            'parcialidades' => $oPeticionCargo->parcialidades,
            'diferimiento' => $oPeticionCargo->diferido,
            'plan' => $oPeticionCargo->plan,
        ])); // POS Additional Data
		#$oMensaje->setData(103, ''); //
		echo "<pre>" . print_r($oMensaje->getIsoValidation(), true) . "</pre>";
		echo "<pre>" . print_r($oMensaje->getDataArray(), true) . "</pre>";
		return $oMensaje->getISO(false);
	}

	// ********************************************************************************************************************

	public function mensajeVenta(PeticionCargo $oPeticionCargo, array $aOpciones = [], $bEnvia = false, $bEcho = true)
	{
		if ($bEcho) {
			echo "<br>Preparando mensaje de Venta...";
		}
        // Actualiza opciones con default
        $aOpciones = array_merge(['tipo' => 'puntos_compra'], $aOpciones);
		// Define campos
		$sIso = $this->isoTipoCompra($oPeticionCargo, $aOpciones);
		$sMensaje = $this->preparaMensaje($sIso, $bEcho);
        // Evalua retorno
        if ($bEnvia) {
            return $this->enviaMensaje($oPeticionCargo->id, $sMensaje, $bEcho);
        } else {
            return $sMensaje;
        }
	}

    public function enviaMensaje(string $sId, string $sMensaje, $bEcho = true)
    {
        // Prepara resultado
        $aResponseResult = [
            'status' => 'fail',
            'status_message' => 'Unknown error.',
            'status_code' => '520',
            'response' => null,
        ];

		if ($bEcho) {
			echo "<br>Enviando mensaje...";
		}

		// Conecta
		try {
			$this->oEglobalProxyCliente = stream_socket_client('tcp://' . $this->config['proxy']['ip'] . ':' . $this->config['proxy']['port'], $aResponseResult['status_code'], $aResponseResult['status_message'], $this->config['proxy']['timeout'], STREAM_CLIENT_CONNECT);
			if ($this->oEglobalProxyCliente === false) {
                $aResponseResult = [
                    'status' => 'fail',
                    'status_message' => socket_strerror(socket_last_error()),
                    //'status_code' => '500',
                ];
			}
			// Define timeout
			stream_set_timeout($this->oEglobalProxyCliente, $this->config['proxy']['timeout']);
			stream_set_blocking($this->oEglobalProxyCliente, 0);
            stream_set_read_buffer($this->oEglobalProxyCliente, 0);
			// Espera mensaje de conexión
            $oData = $this->recibeEglobalProxy($bEcho);
			if ($oData->conexion != 'success') {
                $aResponseResult = [
                    'status' => 'fail',
                    'status_message' => 'Error al conectarse a eglobalProxyServer',
                    'status_code' => '502',
                ];
                return false;
			}
			// Prepara mensaje
			$aRequest = [
				'accion' => 'send',
				'id' => $sId,
				'mensaje' => $sMensaje,
			];
			// Envía mensaje
			$jRequest = json_encode($aRequest);
			$size = strlen($jRequest);
			$bytes = fwrite($this->oEglobalProxyCliente, $jRequest, $size);
			if ($bytes === false || $bytes < $size) {
                if ($bEcho) {
                    echo "<br>Error al escribir en el socket!\n";
                }
			} else {
                if ($bEcho) {
    				echo "<br>Mensaje enviado OK ({$bytes}).\n";
                }
			}
			// Espera respuesta
            $oData = $this->recibeEglobalProxy($bEcho);
            // Procesa mensaje
            if (!empty($oData->respuesta)) {
                try {
                    if ($oData->encoding == 'base64') {
                        $sRespuesta = base64_decode($oData->respuesta);
                    } else {
                        $sRespuesta = $oData->respuesta;
                    }
                    dump($sRespuesta);
                    $oInterred = new BBVAInterred();
                    $aMensajeISO = $oInterred->procesaMensaje($sRespuesta);
                    echo "<br>Respuesta recibida (iso): <pre>" . print_r($aMensajeISO['iso_parsed'], true) . "</pre>";
                    #dump($aMensajeISO);
                    $jMensajeISO = json_encode($aMensajeISO['iso_parsed']);
                    #dump($jMensajeISO);
                    #echo "<br>Respuesta recibida (iso): {$jMensajeISO} \n";
                    // Evalua resultado campo 39
                    if (isset($aMensajeISO['iso_parsed'][39]) && isset($this->aCatalogoRespuestas[$aMensajeISO['iso_parsed'][39]])) {
                        echo "<br><h3>Resutado: " . $this->aCatalogoRespuestas[$aMensajeISO['iso_parsed'][39]] . " (" . $aMensajeISO['iso_parsed'][39] . ") </h3>\n";
                    }
                } catch (\Exception $e) {
                    $jMensajeISO = "{}";
                }
            }
            // Prepara respuesta
            $aResponseResult = [
                'status' => 'success',
                'status_message' => 'Mensaje enviado y respuesta recibida',
                'status_code' => '200',
                'respuesta' => $oData,
                'respuesta_iso' => $aMensajeISO,
            ];
		} catch (\Exception $e) {
			echo "\n<br>Error al crear el socket: " . $e->getMessage();
			return false;
		}
		if (fclose($this->oEglobalProxyCliente) === false) {
			echo "<br>Error al cerrar el socket: " . socket_strerror(socket_last_error()) . "\n";
			throw new \Exception("Error al cerrar el socket: " . socket_strerror(socket_last_error()));
		} else {
			$this->oEglobalProxyCliente = false;
			echo "<br>Socket cerrado OK.\n";
		}
        return (object) $aResponseResult;
    }

	public function recibeEglobalProxy($bEcho = true)
	{
        // Prepara variables
        $sMensaje = null;
        $oTime = Carbon::now();
		// Recibe datos
        if ($bEcho) {
            echo "\n<br>Esperando respuesta de eglobal proxy...";
        }
        while(empty($sMensaje)) {
            usleep(10000);
            $sMensaje = stream_get_contents($this->oEglobalProxyCliente, 1024);
            if ($oTime->diffInSeconds() > 15) {
                break;
            }
        }
        // Mensaje raw
        if ($bEcho) {
            echo "\n<br>Respuesta recibida (str): " . $sMensaje;
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

	public function ascii2hex($ascii, $bSeprator = true) {
        $hex = '';
        for ($i = 0; $i < strlen($ascii); $i++) {
            $byte = strtoupper(dechex(ord($ascii{$i})));
            $byte = str_repeat('0', 2 - strlen($byte)).$byte;
            if ($bSeprator) {
                $hex.=$byte." ";
            }
        }
        return $hex;
	}
}


