<?php

namespace App\Classes\Pagos\Procesadores\Bbva;

use App\Classes\Pagos\Procesadores\Iso\iso8583_1987;

class Mensaje extends iso8583_1987
{

    // {{{ properties

    /**
     * @var array Tamaño máximo permitido por campo en bytes.
     */
    const MAX_DATA_ELEMENT_SIZE = 290;

    /**
     * @var array Elementos de header diferentes al iso8583_1987
     */
    protected $CUSTOM_HEADER_ELEMENT = [
        'MTI' =>    ['encoding' => 'string', 'charset' => 'ASCII'],
        'BITMAP' => ['encoding' => 'hex'],
    ];

    /**
     * @var array Elementos de datos diferentes al iso8583_1987
     */
    private $CUSTOM_DATA_ELEMENT = [
        1 =>   ['type' => 'an',   'size' => 16,   'fixed' => true,  'mandatory' => true, 'usage' => 'Secondary Bit Map' ],
        3 =>   [],
        4 =>   [],
        7 =>   ['mandatory' => true],
        11 =>  ['mandatory' => true],
        12 =>  [],
        13 =>  [],
        15 =>  [],
        17 =>  [],
        22 =>  [],
        23 =>  [],
        25 =>  [],
        32 =>  [],
        35 =>  ['type' => 'ans'],
        37 =>  [],
        38 =>  [],
        39 =>  [],
        41 =>  ['size' => 16],
        43 =>  [],
        48 =>  ['size' => 30],
        49 =>  [],
        54 =>  ['type' => 'ans', 'size' => 15],
        55 =>  ['type' => 'ansb', 'usage' => 'Datos de la tarjeta de circuito integrado (ICC – EMV Full Grade)'],
        58 =>  ['size' => 420,  'usage' => 'Datos de Lealtad'],
        59 =>  ['usage' => 'Datos de Campaña'],
        60 =>  ['sizepos' => 'LLL', 'size' => 19, 'usage' => 'POS Terminal Data'],
        63 =>  ['usage' => 'POS Additional Data'],
        70 =>  [],
        90 =>  ['type' => 'ans'],
        103 => ['type' => 'ans'],
    ];

    /**
     * @var array Lista de elementos bloqueados para mensajes BBVA
     */
    protected $BLOCKED_DATA_ELEMENT = [
        5, 6, 8, 9,
        10, 16, 18,
        20, 21, 24, 26, 27, 28, 29,
        30, 31, 33, 34, 36,
        40, 42, 44, 45, 46, 47,
        50, 51, 52, 53, 56, 57,
        61, 62, 64, 65, 67, 68, 69,
        71, 72, 73, 74, 75, 76, 77, 78, 79,
        80, 81, 82, 83, 84, 85, 86, 87, 88, 89,
        91, 92, 93, 94, 95, 97, 98, 99,
        100, 101, 102, 104, 105, 106, 107, 108, 109,
        110, 111, 112, 113, 114, 115, 116, 117, 118, 119,
        120, 121, 122, 123, 124, 125, 126, 127, 128,
    ];

    // }}}

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

    // }}}

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos públicos
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ public functions

    /**
     * Constructor
     */
    public function __construct()
    {
        // Ejecuta constructor padre
        parent::__construct();
        // Incorpora campos custom
        $this->updateDefinition($this->CUSTOM_HEADER_ELEMENT, $this->CUSTOM_DATA_ELEMENT);
    }

    /**
     * Campo 3 - Processing Code
     */
    public function formateaCampo3(array $aData): string
    {
        $sResultado = '';
        // 1-2 Tipo de transacción
        //     00 = Compra, Check-in, Reautorización, Compra con Pre-Propina
        //     06 = Check-out
        //     07 = Post-Propina
        //     20 = Devolución
        //     09 = Cash advance/Compra con cash back
        //     16 = Consulta de Puntos
        //     17 = Dinero Móvil
        //     18 = Compra con Puntos
        //     65 = Pago Finanzia
        //     28 = Pago de tarjeta
        //     40 = Transferencia
        //     50 = Multipago
        $sResultado .= sprintf("%02s", $aData['tipo']);
        // 3-4 Cuenta Origen
        //     00 = Cuenta por omisión
        //     99 = Multipago en efectivo
        $sResultado .= sprintf("%02s", $aData['origen']);
        // 5 Cuenta Destino – Parte 1 Mensaje de solicitud de la Interred:
        //     0   = Cuenta por omisión
        //     Mensaje de respuesta del Adquirente:
        //     0   = Cuenta por omisión. Transacción NO QPS
        //     9   = Transacción QPS (ver Anexo 6), Dinero Móvil
        $sResultado .= sprintf("%1s", $aData['destino1']);
        // 6 Cuenta Destino – Parte 2 Mensaje de solicitud de la Interred:
        //     0   = Cuenta por omisión
        //     Mensaje de respuesta del Adquirente:
        //     0   = Cuenta por omisión. Pin Pad NO requiere telecarga
        //     1   = Pin Pad requiere telecarga
        //     9   = Dinero Móvil
        $sResultado .= sprintf("%1s", $aData['destino2']);

        return $sResultado;
    }

    /**
     * Campo 22 - Point of Service Entry Mode
     */
    public function formateaCampo22(array $aData): string
    {
        $sResultado = '';
        // 1-2 Forma de lectura del número de tarjeta
        //     00 = Desconocido
        //     01 = Manual
        //     02 = Banda Magnética leída. El contenido de la misma fue editado
        //     03 = Manual
        //     05 = Manual
        //     07 = Post-Propina
        //     80 = Devolución
        //     90 = Compra con Puntos
        //     91 = Pago Finanzia
        //     95 = Multipago
        $sResultado .= sprintf("%02s", $aData['lectura']);
        // 3 - Capacidad de aceptación del NIP
        //     0  = Desconocido
        //     1 = Puede aceptar NIPs
        //     2 - No puede aceptar NIPs
        //     8 - El PIN pad está fuera de Servicio
        $sResultado .= sprintf("%01s", $aData['acepta_nip']);

        return $sResultado;
    }

    /**
     * Campo 41 - Card Acceptor Terminal Identification
     */
    public function formateaCampo41(array $aData): string
    {
        $sResultado = '';
        // 1-8 Identificación de Terminal. Justificado a la derecha con ceros a la izquierda
        $sResultado .= sprintf("%08s", $aData['terminal']);
        // 9-16 Espacios
        $sResultado .= sprintf("% 8s", $aData['espacios']);

        return $sResultado;
    }

    /**
     * Campo 43 - Card Acceptor Name/Location
     */
    public function formateaCampo43(array $aData): string
    {
        $sResultado = '';
        // 1-22 Razón Social del Comercio
        $sResultado .= sprintf("%- 22s", $aData['razon_social']);
        // 23-35 Ciudad
        $sResultado .= sprintf("%- 13s", $aData['ciudad']);
        // 36-38 Estado
        $sResultado .= sprintf("%- 3s", $aData['estado']);
        // 39-40 Pais
        $sResultado .= sprintf("%- 2s", $aData['pais']);

        return $sResultado;
    }

    /**
     * Campo 48 - Additional DataRetailer Data
     */
    public function formateaCampo48(array $aData): string
    {
        $sResultado = '';
        // 1-19 Afiliación (7 posiciones con blancos a la derecha)
        $sResultado .= sprintf("%- 19s", $aData['afiliacion']);
        // 20-23 ‘0000’ o identificador de la Cadena, si se dispone de él
        $sResultado .= sprintf("% 4s", $aData['cadena']);
        // 24-27 ‘0000’ o identificador de la Región, si se dispone de él
        $sResultado .= sprintf("% 4s", $aData['region']);
        // Mensaje de Red
        if ($aData['mensaje_red']) {
            $sResultado = '50NNNY2010000   ';
        }

        return $sResultado;
    }

    /**
     * Campo 49 - Transaction Currency Code.
     */
    public function formateaCampo49(array $aData): string
    {
        $sResultado = '484';
        // Código de moneda local usado en la transacción
        if ($aData['moneda'] == 'MXN') {
            $sResultado = '484';
        } else if ($aData['moneda'] == 'USD') {
            $sResultado = '840';
        }

        return $sResultado;
    }

    /**
     * Campo 55 - Datos de la tarjeta de circuito integrado (ICC – EMV Full Grade)
     */
    public function formateaCampo55(array $aData): string
    {
        // No implementado por ser ecommerce
    }

    /**
     * Campo 58 - Datos de Lealtad
     *
     * Datos para Redención de Puntos
     * 1. Aplica para entidades que implanten la solución de Lealtad Bancomer.
     * 2. Puede estar presente en los mensajes de respuesta de transacciones asociadas al Módulo de Campañas (proyecto Garanti)
     */
    public function formateaCampo58(array $aData): string
    {
        $sResultado = '';
        // 16 Número impredecible
        $sResultado .= $aData['numero'];
        // 12 Importe en pesos
        // Venta con Redención de Puntos Se enviará el total del importe en pesos de la venta. En la respuesta, el host enviará sólo el importe en pesos realmente redimidos.
        // Venta Normal con Bin Presente en Tabla de Lealtad y Consulta de Puntos Se enviará el importe en ceros. En la respuesta, la información en este campo no es relevante
        $sResultado .= sprintf("%01s", $aData['importe']);
        // 10 Número de puntos. Se enviará el número de puntos en cero. En la respuesta se regresará el número de puntos redimidos. Aplica para TDC y TDD
        $sResultado .= '0';
        // 02 Tipo POS. 00 -> POS No tiene la funcionalidad de VB, 01 -> POS tiene la funcionalidad de VB
        $sResultado .= $aData['pos_vb'] ?? '00';

        return $sResultado;
    }

    /**
     * Campo 59 - Datos de Campaña
     */
    public function formateaCampo59(array $aData): string
    {
        // No implementado por ser ecommerce
    }

    /**
     * Campo 60 - POS Terminal Data
     *
     * Ecommerce ejemplo: Walmart: WALMTES1+0000000
     */
    public function formateaCampo60(array $aData = []): string
    {
        $sResultado = '';
        // 1-4 Terminal Owner FIIF. Identificador del dueño de la Terminal
        $sResultado .= $aData['fiif'] ?? 'CLPG';
        // 5-8 Terminal Logical Network. Red Lógica a la que pertenece la Terminal
        $sResultado .= $aData['red'] ?? 'TES1';
        // 9-12 Terminal Time Offset. Time Offset correspondiente a la zona en que se ubica la Terminal.
        $sResultado .= $aData['offset'] ?? '+000';
        // 13-16 Pseudo Terminal ID. Identificador de la Terminal involucrada en la transacción.
        $sResultado .= $aData['offset'] ?? '0000';

        return $sResultado;
    }

    /**
     * Campo 63 - POS Additional Data
     */
    public function formateaCampo63(array $aData = []): string
    {
        $sResultado = '';
        // Q1
        $sResultado .= '! Q100002 ';
        // 4 Autorizado off-line por el negocio, archivo negativo.
        // 5 Transacción forzada o de ajuste, 0220/0221.
        // 9 Situación desconocida.
        if ($aData['mti'] == '0220') {
            $sResultado .= '5 ';
        } else {
            $sResultado .= '4 ';
        }

        // Q2
        $sResultado .= '! Q200002 ';
        // 01 Autorización Voz (Operador).
        // 02 Cargos Automáticos a través de la Interred.
        // 04 Interred Tradicional.
        // 08 Ventas por Correo/Teléfono (MO/TO) a través de la Interred.
        // 09 Internet (Comercio Electrónico) a través de la Interred.
        // 14 Audio-Respuesta (IVR).
        // 17 Comercios Multicaja.
        // 19 CAT a través de la Interred.
        // 24 TAG (IAVE) a través de la Interred
        if (empty($aData['plataforma'])) {
            // Default
            $sResultado .= '09';
        } else if ($aData['plataforma'] == 'automatico') {
            $sResultado .= '02';
        } else if ($aData['plataforma'] == 'interred') {
            $sResultado .= '04';
        } else if ($aData['plataforma'] == 'telefono') {
            $sResultado .= '08';
        } else if ($aData['plataforma'] == 'internet') {
            $sResultado .= '09';
        } else if ($aData['plataforma'] == 'cat') {
            $sResultado .= '19';
        } else if ($aData['plataforma'] == 'tag') {
            $sResultado .= '24';
        }

        // Q6
        $sResultado .= '! Q600006 ';
        $sResultado .= sprintf("%02s", $aData['diferimiento'] ?? '00');
        $sResultado .= sprintf("%02s", $aData['parcialidades'] ?? '00');
        $sResultado .= sprintf("%02s", $aData['parcialidades'] ?? '00');
        if (empty($aData['plan'])) {
            // Default
            $sResultado .= '00';
        } else if ($aData['plan'] == 'sinintereses') {
            $sResultado .= '03';
        } else if ($aData['plan'] == 'conintereses') {
            $sResultado .= '05';
        } else if ($aData['plan'] == 'diferido') {
            $sResultado .= '07';
        }

        // 04
        $sResultado .= '! 0400020                     ';

        // C0
        $sResultado .= '! C000026 ';
        // Código de validación 2: CVV2 para VISA CVC2 para MasterCard 4DBC para American Express Los datos deben de estar justificados a la izquierda. “ ” Código de validación 2 no presente
        $sResultado .= sprintf("%- 4s", $aData['cvv2'] ?? ' ');
        $sResultado .= ' 001          ';
        // Comercio Electrónico:
        // 0 No es transacción de Comercio Electrónico.
        // 1 Ventas por Correo/Teléfono (MO/TO).
        // 5 Comercio Electrónico Seguro, Titular autenticado
        // 6 Comercio Electrónico seguro, Titular no autenticado
        // 7 Comercio Electrónico Canal Seguro (SSL).
        if (empty($aData['ecommerce'])) {
            // Default
            $sResultado .= '7';
        } else if ($aData['ecommerce'] == 'no') {
            $sResultado .= '0';
        } else if ($aData['ecommerce'] == 'telefono') {
            $sResultado .= '1';
        } else if ($aData['ecommerce'] == 'autenticado') {
            $sResultado .= '5';
        } else if ($aData['ecommerce'] == 'no_autenticado') {
            $sResultado .= '6';
        } else if ($aData['ecommerce'] == 'ssl') {
            $sResultado .= '7';
        }
        $sResultado .= '  ';
        // Indicador de CV2:
        // 0 El CV2 no fue incluido deliberadamente o no proporcionado
        // 1 El CV2 está presente
        // 2 El CV2 está impreso en la tarjeta pero es ilegible
        // 9 El CV2 no está impreso en la tarjeta
        if (empty($aData['indicador_cvv2'])) {
            // Default
            $sResultado .= '0';
        } else if ($aData['indicador_cvv2'] == 'no') {
            $sResultado .= '0';
        } else if ($aData['indicador_cvv2'] == 'presente') {
            $sResultado .= '1';
        } else if ($aData['indicador_cvv2'] == 'ilegible') {
            $sResultado .= '2';
        } else if ($aData['indicador_cvv2'] == 'no_presente') {
            $sResultado .= '9';
        }
        $sResultado .= ' ';
        // Authentication Collector Indicator.
        // 0 = UCAF no soportado por el Comercio
        // 1 = UCAF es soportado por el Comercio pero los datos de autenticación no fueron capturados.
        // 2 = UCAF es soportado por el Comercio y sí contiene datos de autenticación.
        // Los valores ‘1’ y ‘2’ sólo aplican a transacciones de Comercio Electrónico realizadas con tarjetas marca MasterCard. En cualquier otro caso, deberá contener el valor ‘0’.
        $sResultado .= '0';
        $sResultado .= ' ';
        // Resultado de la validación del CAVV - Resultado de la Validación CAVV (Tarjetas marca VISA) / UCAF-AAV (Tarjetas marca MasterCard).
        // Aplica a transacciones de Comercio Electrónico Seguro.
        // “ ”=No es Comercio Electrónico Seguro
        // 0 = No se realizó la Validación por error en la recepción de datos
        // 1 = Falló Validación
        // 2 = Pasó Validación
        // 3 = No se realizó la Validación pues no existe información en el EAF
        // 4 = La Validación no se realizó por error del sistema (EAF corrupto)
        // 5 = El Adquirente participa en Autenticación pero el Emisor no participa
        // 6 = El BIN Emisor participa en Autenticación pero no en Validación
        // 7 = CAVV/AAV duplicado
        $sResultado .= ' ';

        // C4
        $sResultado .= '! C400012 102';
        // Indicador de presencia del tarjetahabiente:
        // 0 El tarjetahabiente está presente
        // 1 El tarjetahabiente no está presente (no se especifica razón)
        // 2 El tarjetahabiente no está presente (transacción iniciada por correo o fax)
        // 3 El tarjetahabiente no está presente (autorización por voz, MO/TO)
        // 4 El tarjetahabiente no está presente (transacción recurrente)
        // 5 El tarjetahabiente no está presente (orden electrónica desde una PC o internet)
        if (empty($aData['tarjetahabiente'])) {
            // Default
            $sResultado .= '5';
        } else if ($aData['tarjetahabiente'] == 'presente') {
            $sResultado .= '0';
        } else if ($aData['tarjetahabiente'] == 'no_presente') {
            $sResultado .= '2';
        } else if ($aData['tarjetahabiente'] == 'voz') {
            $sResultado .= '3';
        } else if ($aData['tarjetahabiente'] == 'recurrente') {
            $sResultado .= '4';
        } else if ($aData['tarjetahabiente'] == 'internet') {
            $sResultado .= '5';
        }
        // Indicador de presencia de tarjeta
        if (empty($aData['tarjeta'])) {
            // Default
            $sResultado .= '1';
        } else if ($aData['tarjeta'] == 'presente') {
            $sResultado .= '0';
        } else if ($aData['tarjeta'] == 'no_presente') {
            $sResultado .= '1';
        }
        // Indicador de capacidad de captura de tarjetas
        $sResultado .= '0';
        // Indicador de status
        if (empty($aData['status'])) {
            // Default
            $sResultado .= '0';
        } else if ($aData['status'] == 'normal') {
            $sResultado .= '0';
        } else if ($aData['status'] == 'preautorizado') {
            $sResultado .= '4';
        }
        // Nivel de seguridad del adquiriente + Routing indicator
        $sResultado .= '03';
        // Activación de la terminal por el tarjetahabiente
        $sResultado .= '6';
        // Indicador de capacidad para transferir datos de la tarjeta a la terminal
        $sResultado .= '6';
        // Método de Identificación del Tarjetahabiente
        $sResultado .= '0';

        // C5 - Multipagos
        if (!empty($aData['multipagos'])) {
            if ($aData['multipagos']['tipo'] == 'cie') {
                // CIE
                $sResultado .= '! C500078 01';
                $sResultado .= sprintf("%-09s", $aData['multipagos']['convenio_cie'] ?? '0');
                $sResultado .= sprintf("% 20s", $aData['multipagos']['referencia'] ?? '0');
                $sResultado .= sprintf("%-07s", $aData['multipagos']['guia_cie'] ?? '0');
                $sResultado .= sprintf("% 40s", $aData['multipagos']['referencia'] ?? ' ');
            } else if ($aData['multipagos'] == 'hipoteca') {
                // Pago de Crédito Hipotecario
                $sResultado .= '! C500090 02009999901';
                // Número de Crédito Hipotecario
                $sResultado .= sprintf("% 20s", $aData['multipagos']['credito_hipotecario'] ?? ' ');
                $sResultado .= sprintf("%-07s", $aData['multipagos']['folio'] ?? '0');
                $sResultado .= sprintf("% 40s", $aData['multipagos']['titular'] ?? ' ');
                $sResultado .= sprintf("%-012s", $aData['multipagos']['importe'] ?? '0');
            } else if ($aData['multipagos'] == 'express') {
                // Depósito a Cuenta Express
                $sResultado .= '! C500078 ';
                // Dataset Id
                if ($aData['multipagos']['deposito'] == 'celular') {
                    $sResultado .= '03';
                } else if ($aData['multipagos']['deposito'] == 'cuenta') {
                    $sResultado .= '04';
                }
                $sResultado .= sprintf("%-09s", $aData['multipagos']['convenio_cie'] ?? '009999903');
                $sResultado .= sprintf("% 20s", $aData['multipagos']['cuenta'] ?? '0000000000');
                $sResultado .= '       ';
                $sResultado .= sprintf("% 40s", $aData['multipagos']['referencia'] ?? ' ');
            } else if ($aData['multipagos'] == 'dinero_movil') {
                // Dinero móvil
                $sResultado .= '! C500018 05';
                $sResultado .= sprintf("%04s", $aData['multipagos']['codigo'] ?? '0');
                $sResultado .= sprintf("%012s", $aData['multipagos']['confirmacion'] ?? '0');
            }
        }

        // ER, ES, ET, EW, EX, EY, EZ - No implmentado por ser ecommerce

        // R7 - Indicador Bonus Merchant y Número de Referencia de Campañas
        $sResultado .= '! R700013 N000000000000';

        // R8 - No implmentado, sólo respuesta

        // C6 - Datos de autenticación para tarjetas marca Visa (XID y CAVV). Programa 3-D Secure (Verified by Visa)
        if (!empty($aData['3dsecure'])) {
            $sResultado .= '! C600080 ';
            $sResultado .= sprintf("% 40s", $aData['3dsecure']['xid'] ?? ' ');
            $sResultado .= sprintf("% 40s", $aData['3dsecure']['cavv'] ?? ' ');
        }

        // CE - Datos de autenticación para tarjetas marca MasterCard (UCAF-AAV). Programa Secure Code
        if (!empty($aData['secure_code'])) {
            $sResultado .= '! CE00200 01';
            $sResultado .= sprintf("% 200s", $aData['secure_code']['ucaf'] ?? ' ');
        }

        // CZ - No implmentado, sólo contactless

        return $sResultado;
    }

    /**
     * Obtiene la lista de caracteres admitidos en el tipo de datos
     *
     * @param int $iLength Length of STAN (optional)
     */
    public function generateSystemsTraceAuditNumber(int $iLength = 6): string
    {
        // Define characters
        //$aChars = array_merge(range(32, 39), range(42, 90), range(92, 96), [123, 125, 126]);
        $aChars = range(48, 57);
        $iCharsLength = count($aChars);
        $sSTAN = '';
        for ($i = 0; $i < $iLength; $i++) {
            $sSTAN .= chr($aChars[mt_rand(0, $iCharsLength - 1)]);
        }
        return $sSTAN;
    }

    // }}}
}