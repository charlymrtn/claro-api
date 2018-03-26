<?php

namespace BbvaSocket;

require_once('b_iso8583_1987.php');
require_once('b_iso8583_bbva.php');

class pruebaBBVA
{
	protected $client;
	protected $client2;
	protected $config = [
		//'ip' => '172.26.202.4',
		//'port' => 8315,
		'ip' => '127.0.0.1',
		'port' => 8300,
		'timeout' => 3,
		'afiliacion' => '5462742',
	];
	
	public function conecta($bReconnect = false)
	{
		// Conecta
		$this->client = false;
		try {
			$this->client = @stream_socket_client('tcp://' . $this->config['ip'] . ':' . $this->config['port'], $aError['number'], $aError['error'], $this->config['timeout'], STREAM_CLIENT_CONNECT);
			if ($this->client === false) {
				throw new \Exception("Error al crear el socket: " . socket_strerror(socket_last_error()));
			}
			echo "<br>Socket creado OK.\n";
			// Define timeout
			stream_set_timeout($this->client, $this->config['timeout']);
			// Autentica
			$respuesta = $this->escucha();
		} catch (\Exception $e) {
			echo "\n<br>Error al crear el socket: " . $e->getMessage();
			return false;
		}
		return true;
	}
	
	public function desconecta()
	{
		if (fclose($this->client) === false) {
			echo "<br>Error al cerrar el socket: " . socket_strerror(socket_last_error()) . "\n";
			throw new \Exception("Error al cerrar el socket: " . socket_strerror(socket_last_error()));
		} else {
			$this->client = false;
			echo "<br>Socket cerrado OK.\n";
		}
	}
	
	public function envia($sMensaje, $bEcho = true, $bValida = true): bool
	{
		// Envía datos
		if ($bEcho) {
			echo "<br>Enviando mensaje: '" . $sMensaje . "'";
		}
		$size = strlen($sMensaje);
		$bytes = fwrite($this->client, $sMensaje, $size);
		if ($bytes === false || $bytes < $size) {
			if ($bEcho) {
				echo "<br>Error al escribir en el socket!\n";
			}
			return false;
		} else {
			if ($bEcho) {
				echo "<br>Mensaje enviado OK ({$bytes}).\n";
			}
			return true;
		}
	}
	
	public function escucha()
	{
		usleep(500);
		// Recibe datos 1
		echo "<br>Escuchando socket...";
		$data = stream_get_contents($this->client);
		echo "<br>Respuesta: " . $data;
		return $data;
	}
	
	public function valida_conexion()
	{
		// Valida conexión enviando mensaje al socket resource
		echo "<br>Validando conexión.";
		$bConexion = $this->envia($this->mensajeEcho(false), false, false);
		if (!$bConexion) {
			$this->conecta(true);
		}
	}
	
	// ********************************************************************************************************************
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

	private function isoTipoRed($sNMICode)
	{
		// Define campos
		$oMensaje = new Mensaje();
		$oMensaje->setMTI('0800');
		$oMensaje->setData(7, gmdate('mdhis')); // Date & time
		$oMensaje->setData(11, $oMensaje->generateSystemsTraceAuditNumber()); // Systems Trace Audit Number
		$oMensaje->setData(15, date('md')); // Date & time
		if ($sNMICode != '301') {
			$oMensaje->setData(48, '50NNNY2010000   '); // Additional DataRetailer Data - Define la afiliación del Establecimiento
		}
		$oMensaje->setData(70, $sNMICode); // Network Management Information Code
		return $oMensaje->getISO(false);
	}

	private function isoTipoCompra(array $aData = [])
	{
		// Define campos
		$oMensaje = new Mensaje();
		$oMensaje->setMTI('0200');
		$oMensaje->setData(3, $oMensaje->formateaCampo3($aData)); // Processing Code
		$oMensaje->setData(4, $aData['monto_x_100']); // Transaction Amount - Monto de la transacción con centavos
		$oMensaje->setData(7, gmdate('mdhis')); // Date & time
		$oMensaje->setData(11, $oMensaje->generateSystemsTraceAuditNumber()); // Systems Trace Audit Number
		$oMensaje->setData(12, date('his')); // Hora local de la transacción
		$oMensaje->setData(13, date('md')); // Date & time - Día local de la transacción 
		$oMensaje->setData(17, date('md')); // Date & time - Día en el cual la transacción es registrada por el Adquirente
		$oMensaje->setData(22, '012'); // PoS Entry Mode 
		#$oMensaje->setData(23, ''); //
		$oMensaje->setData(25, '59'); // Point of Service Condition Code - 59 = Comercio Electrónico
		$oMensaje->setData(32, '12'); // Acquiring Institution Identification Code
		$oMensaje->setData(35, '477213******3584=2005'); // Track 2 Data
		$oMensaje->setData(37, '000000123401'); // Retrieval Reference Number 
		$oMensaje->setData(41, '0000CP01        '); // Card Acceptor Terminal Identification 
		//$oMensaje->setData(43, 'Radiomovil DIPSA SA CVCMXCMXMX'); //  Card Acceptor Name/Location 
		$oMensaje->setData(48, '5462742            00000000'); // Additional DataRetailer Data - Define la afiliación del Establecimiento
		$oMensaje->setData(49, '484'); // Transaction Currency Code. 
		//$oMensaje->setData(54, '000000000000')); // Additional Amounts - Monto del cash advance/back con centavos
		#$oMensaje->setData(55, ''); //
		#$oMensaje->setData(58, ''); //
		#$oMensaje->setData(59, ''); //
		$oMensaje->setData(60, 'CLPGTES1+0000000'); // POS Terminal Data
		$oMensaje->setData(63, $oMensaje->formateaCampo63(['mti' => '0200'])); // POS Additional Data
		#$oMensaje->setData(103, ''); //
		echo "<pre>" . print_r($oMensaje->getDataArray(), true) . "</pre>";
		return $oMensaje->getISO(false);
	}

	// ********************************************************************************************************************

	public function mensajeEcho($bEcho = true)
	{
		if ($bEcho) {
			echo "<br>Preparando mensaje Echo...";
		}
		$sIso = $this->isoTipoRed('301');
		return $this->preparaMensaje($sIso, $bEcho);
	}

	public function mensajeSignOn($bEcho = true)
	{
		if ($bEcho) {
			echo "<br>Preparando mensaje Sign On...";
		}
		$sIso = $this->isoTipoRed('001');
		return $this->preparaMensaje($sIso, $bEcho);
	}
	
	public function mensajeSignOff($bEcho = true)
	{
		if ($bEcho) {
			echo "<br>Preparando mensaje Sign Off...";
		}
		$sIso = $this->isoTipoRed('002');
		return $this->preparaMensaje($sIso, $bEcho);
	}

	public function mensajeCutoff($bEcho = true)
	{
		if ($bEcho) {
			echo "<br>Preparando mensaje Cutoff...";
		}
		$sIso = $this->isoTipoRed('201');
		return $this->preparaMensaje($sIso, $bEcho);
	}
	
	public function mensajeVenta(array $aData, $bEcho = true)
	{
		if ($bEcho) {
			echo "<br>Preparando mensaje de Venta...";
		}
		// Define campos
		$sIso = $this->isoTipoCompra($aData);
		return $this->preparaMensaje($sIso, $bEcho);
	}

	// ********************************************************************************************************************
	
	public function mensajePrueba1()
	{
		echo "<br>Preparando mensaje de prueba...";
		// Define campos
		$oMensaje = new Mensaje();
		$oMensaje->setMTI('0200');
		$oMensaje->setData(3, '000000'); // Processing Code
		$oMensaje->setData(4, '000000183000'); // Transaction Amount - Monto de la transacción con centavos
		$oMensaje->setData(7, date('mdhis')); // Date & time
		$oMensaje->setData(11, $oMensaje->generateSystemsTraceAuditNumber()); // Systems Trace Audit Number
		$oMensaje->setData(12, date('his')); // Hora local de la transacción
		$oMensaje->setData(13, date('md')); // Date & time - Día local de la transacción 
		$oMensaje->setData(17, date('md')); // Date & time - Día en el cual la transacción es registrada por el Adquirente
		$oMensaje->setData(22, '012'); // PoS Entry Mode 
		#$oMensaje->setData(23, ''); //
		$oMensaje->setData(25, '59'); // Point of Service Condition Code - 59 = Comercio Electrónico
		$oMensaje->setData(32, '12'); // Acquiring Institution Identification Code
		$oMensaje->setData(35, '477213******3584=2005'); // Track 2 Data
		$oMensaje->setData(37, '000000123401'); // Retrieval Reference Number 
		$oMensaje->setData(41, '0000CP01        '); // Card Acceptor Terminal Identification 
		//$oMensaje->setData(43, 'Radiomovil DIPSA SA CVCMXCMXMX'); //  Card Acceptor Name/Location 
		$oMensaje->setData(48, '5462742            00000000'); // Additional DataRetailer Data - Define la afiliación del Establecimiento
		$oMensaje->setData(49, '484'); // Transaction Currency Code. 
		//$oMensaje->setData(54, '000000000000')); // Additional Amounts - Monto del cash advance/back con centavos
		#$oMensaje->setData(55, ''); //
		#$oMensaje->setData(58, ''); //
		#$oMensaje->setData(59, ''); //
		$oMensaje->setData(60, 'CLPGTES1+0000000'); // POS Terminal Data
		$oMensaje->setData(63, $oMensaje->formateaCampo63(['mti' => '0200'])); // POS Additional Data
		#$oMensaje->setData(103, ''); //
		$sIso = $oMensaje->getISO(false);
		echo "<pre>" . print_r($oMensaje->getDataArray(), true) . "</pre>";
		return $this->preparaMensaje($sIso);
	}

	public function ascii2hex($ascii) {
	  $hex = '';
	  for ($i = 0; $i < strlen($ascii); $i++) {
		$byte = strtoupper(dechex(ord($ascii{$i})));
		$byte = str_repeat('0', 2 - strlen($byte)).$byte;
		$hex.=$byte." ";
	  }
	  return $hex;
	}
}

// Variables
$respuesta1 = null; $respuesta2 = null; $respuesta3 = null;
$respuesta4 = null; $respuesta5 = null; $respuesta6 = null;

$oPruebaBBVA = new pruebaBBVA();

/**
echo "<h2>Prueba</h2>\n";

	echo "\n<br>" . $oPruebaBBVA->mensajePrueba1();
	echo "\n<br>" . $oPruebaBBVA->mensajeVenta([
		'tipo' => 'compra',
		'monto' => '1830.00',
		'monto_x_100' => '183000',
	], false);
	
exit;
**/

$respuesta1 = $oPruebaBBVA->conecta();

if ($respuesta1) {
	// ----------------------------------------------------------------------------------------------------------------------------------
	// Envía mensaje Sign On
	$mensaje = $oPruebaBBVA->mensajeSignOn();
	echo "\n<br>Enviando: " . $oPruebaBBVA->ascii2hex($mensaje);
	$oPruebaBBVA->envia($mensaje);
	#sleep(1);
	$respuesta2 = $oPruebaBBVA->escucha();
	#flush(); sleep(1);
	// ----------------------------------------------------------------------------------------------------------------------------------
}



/**

if (!empty($respuesta2)) {
	// ----------------------------------------------------------------------------------------------------------------------------------
	// Envía mensaje Echo
	$oPruebaBBVA->envia($oPruebaBBVA->mensajeEcho(), true, false);
	$respuesta3 = $oPruebaBBVA->escucha();
	flush(); sleep(2);
	// ----------------------------------------------------------------------------------------------------------------------------------
}

if (!empty($respuesta3)) {
	// ----------------------------------------------------------------------------------------------------------------------------------
	// Envía mensaje Venta normal Caso 1
	$oPruebaBBVA->envia($oPruebaBBVA->mensajePrueba1());
	$respuesta4 = $oPruebaBBVA->escucha();
	flush(); sleep(2);
	// ----------------------------------------------------------------------------------------------------------------------------------
}

if (!empty($respuesta4)) {
	// ----------------------------------------------------------------------------------------------------------------------------------
	// Envía mensaje Sign Off
	$oPruebaBBVA->envia($oPruebaBBVA->mensajeSignOn());
	$respuesta5 = $oPruebaBBVA->escucha();
	flush(); sleep(2);
	// ----------------------------------------------------------------------------------------------------------------------------------
}

if (!empty($respuesta5)) {
	// ----------------------------------------------------------------------------------------------------------------------------------
	// Envía mensaje Cutoff
	$oPruebaBBVA->envia($oPruebaBBVA->mensajeCutoff());
	$respuesta6 = $oPruebaBBVA->escucha();
	flush(); sleep(2);
	// ----------------------------------------------------------------------------------------------------------------------------------
}
**/

if ($respuesta1) {
	$oPruebaBBVA->desconecta();
}
