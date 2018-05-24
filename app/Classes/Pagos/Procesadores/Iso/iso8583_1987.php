<?php

namespace App\Classes\Pagos\Procesadores\Iso;

/*
 * Clase para manejo de mensajes ISO-8586
 *
 * Definición: Documento E-Global Anexo A - ISO8583
 * Definición: https://es.wikipedia.org/wiki/ISO_8583
 *
 * @package Procesadores
 */

class iso8583_1987
{
    // {{{ properties

    /*
     * @var array Tabla de caracteres especiales en ASCII.
     */
    protected $SPECIAL_CHARS_S = [];

    /*
     * @var array Tabla de caracteres especiales y alfabéticos en ASCII.
     */
    protected $SPECIAL_CHARS_AS = [];

    /*
     * @var array Tabla de caracteres especiales y alfanuméricos en ASCII.
     */
    protected $SPECIAL_CHARS_ANS = [];

    /**
     * Elementos de datos - Son los campos individuales que llevan la información sustancial acerca de la transacción.
     * Hay 128 campos definidos en el estándar ISO8583:1987, y 192 en posteriores releases.
     *
     * TIPO (type)
     * n	Caracteres numéricos
     * a	Caracteres alfabéticos
     * an	Caracteres alfabéticos y/o numéricos
     * anp	Caracteres alfabéticos y/o numéricos y espacios
     * as	Caracteres alfabéticos y/o especiales
     * ans	Caracteres alfabéticos, numéricos y/o especiales
     * ansb	Caracteres alfabéticos, numéricos y/o especiales, binarios
     * s	Caracteres especiales ASCII character set 32 - 126
     * ns	Caracteres numéricos y/o especiales
     * b	Binario
     * z	Tracks 2 y 3 code set como se define en la ISO 4909 y en ISO 7813.
     *
     * FIXED
     * true
     * false
     *
     * SIZE - Max size in bytes
     * llvar  Variable length data field specification. LL: 01 a 99 positions; VAR: Variable length data
     * lllvar Variable length data field specification. LL: 001 a 999 positions; VAR: Variable length data
     *
     * USAGE - Field description
     *
     * MANDATORY
     * true
     * false
     *
     * ENCODING
     * bin
     * hex
     * string
     *
     * CHARSET
     * ascii
     * ebcdic
     *
     * @var array Mapa de elementos de datos
     */
    protected $HEADER_ELEMENT = [
        'MTI' =>    ['type' => 'n',   'size' => 4,  'fixed' => true,  'usage' => 'MTI', 'mandatory' => true],
        'BITMAP' => ['type' => 'b',   'size' => 8,  'fixed' => true,  'usage' => 'Bit Map Primary', 'mandatory' => true],
    ];

    protected $DATA_ELEMENT = [
        1 =>   ['type' => 'b',   'size' => 8,   'fixed' => true,  'mandatory' => false, 'usage' => 'Bit Map Extended'],
        2 =>   ['type' => 'n',   'size' => 19,  'fixed' => false, 'sizepos' => 'LL', 'mandatory' => false, 'usage' => 'Primary account number (PAN)'],
        3 =>   ['type' => 'n',   'size' => 6,   'fixed' => true,  'mandatory' => false, 'usage' => 'Processing code'],
        4 =>   ['type' => 'n',   'size' => 12,  'fixed' => true,  'mandatory' => false, 'usage' => 'Amount, transaction'],
        5 =>   ['type' => 'n',   'size' => 12,  'fixed' => true,  'mandatory' => false, 'usage' => 'Amount, Settlement'],
        6 =>   ['type' => 'n',   'size' => 12,  'fixed' => true,  'mandatory' => false, 'usage' => 'Amount, cardholder billing'],
        7 =>   ['type' => 'n',   'size' => 10,  'fixed' => true,  'mandatory' => false, 'usage' => 'Transmission date & time'],
        8 =>   ['type' => 'n',   'size' => 8,   'fixed' => true,  'mandatory' => false, 'usage' => 'Amount, Cardholder billing fee'],
        9 =>   ['type' => 'n',   'size' => 8,   'fixed' => true,  'mandatory' => false, 'usage' => 'Conversion rate, Settlement'],
        10 =>  ['type' => 'n',   'size' => 8,   'fixed' => true,  'mandatory' => false, 'usage' => 'Conversion rate, cardholder billing'],
        11 =>  ['type' => 'n',   'size' => 6,   'fixed' => true,  'mandatory' => false, 'usage' => 'Systems trace audit number'],
        12 =>  ['type' => 'n',   'size' => 6,   'fixed' => true,  'mandatory' => false, 'usage' => 'Time, Local transaction'],
        13 =>  ['type' => 'n',   'size' => 4,   'fixed' => true,  'mandatory' => false, 'usage' => 'Date, Local transaction (MMdd)'],
        14 =>  ['type' => 'n',   'size' => 4,   'fixed' => true,  'mandatory' => false, 'usage' => 'Date, Expiration'],
        15 =>  ['type' => 'n',   'size' => 4,   'fixed' => true,  'mandatory' => false, 'usage' => 'Date, Settlement'],
        16 =>  ['type' => 'n',   'size' => 4,   'fixed' => true,  'mandatory' => false, 'usage' => 'Date, conversion'],
        17 =>  ['type' => 'n',   'size' => 4,   'fixed' => true,  'mandatory' => false, 'usage' => 'Date, capture'],
        18 =>  ['type' => 'n',   'size' => 4,   'fixed' => true,  'mandatory' => false, 'usage' => 'Merchant type'],
        19 =>  ['type' => 'n',   'size' => 3,   'fixed' => true,  'mandatory' => false, 'usage' => 'Acquiring institution country code'],
        20 =>  ['type' => 'n',   'size' => 3,   'fixed' => true,  'mandatory' => false, 'usage' => 'PAN Extended, country code'],
        21 =>  ['type' => 'n',   'size' => 3,   'fixed' => true,  'mandatory' => false, 'usage' => 'Forwarding institution. country code'],
        22 =>  ['type' => 'n',   'size' => 3,   'fixed' => true,  'mandatory' => false, 'usage' => 'Point of service entry mode'],
        23 =>  ['type' => 'n',   'size' => 3,   'fixed' => true,  'mandatory' => false, 'usage' => 'Application PAN number'],
        24 =>  ['type' => 'n',   'size' => 3,   'fixed' => true,  'mandatory' => false, 'usage' => 'Function code(ISO 8583:1993)/Network International identifier'],
        25 =>  ['type' => 'n',   'size' => 2,   'fixed' => true,  'mandatory' => false, 'usage' => 'Point of service condition code'],
        26 =>  ['type' => 'n',   'size' => 2,   'fixed' => true,  'mandatory' => false, 'usage' => 'Point of service capture code'],
        27 =>  ['type' => 'n',   'size' => 1,   'fixed' => true,  'mandatory' => false, 'usage' => 'Authorizing identification response length'],
        28 =>  ['type' => 'an',  'size' => 8,   'fixed' => true,  'mandatory' => false, 'usage' => 'Amount, transaction fee'],
        29 =>  ['type' => 'an',  'size' => 8,   'fixed' => true,  'mandatory' => false, 'usage' => 'Amount. settlement fee'],
        30 =>  ['type' => 'an',  'size' => 8,   'fixed' => true,  'mandatory' => false, 'usage' => 'Amount, transaction processing fee'],
        31 =>  ['type' => 'an',  'size' => 8,   'fixed' => true,  'mandatory' => false, 'usage' => 'Amount, settlement processing fee'],
        32 =>  ['type' => 'n',   'size' => 11,  'fixed' => false, 'sizepos' => 'LL',  'mandatory' => false, 'usage' => 'Acquiring institution identification code'],
        33 =>  ['type' => 'n',   'size' => 11,  'fixed' => false, 'sizepos' => 'LL',  'mandatory' => false, 'usage' => 'Forwarding institution identification code'],
        34 =>  ['type' => 'n',   'size' => 28,  'fixed' => false, 'sizepos' => 'LL',  'mandatory' => false, 'usage' => 'Primary account number, extended'],
        35 =>  ['type' => 'z',   'size' => 37,  'fixed' => false, 'sizepos' => 'LL',  'mandatory' => false, 'usage' => 'Track 2 data'],
        36 =>  ['type' => 'z',   'size' => 104, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Track 3 data'],
        37 =>  ['type' => 'an',  'size' => 12,  'fixed' => true,  'mandatory' => false, 'usage' => 'Retrieval reference number'],
        38 =>  ['type' => 'an',  'size' => 6,   'fixed' => true,  'mandatory' => false, 'usage' => 'Authorization identification response'],
        39 =>  ['type' => 'an',  'size' => 2,   'fixed' => true,  'mandatory' => false, 'usage' => 'Response code'],
        40 =>  ['type' => 'an',  'size' => 3,   'fixed' => true,  'mandatory' => false, 'usage' => 'Service restriction code'],
        41 =>  ['type' => 'ans', 'size' => 8,   'fixed' => true,  'mandatory' => false, 'usage' => 'Card acceptor terminal identification'],
        42 =>  ['type' => 'ans', 'size' => 15,  'fixed' => true,  'mandatory' => false, 'usage' => 'Card acceptor identification code'],
        43 =>  ['type' => 'ans', 'size' => 40,  'fixed' => true,  'mandatory' => false, 'usage' => 'Card acceptor name/location'],
        44 =>  ['type' => 'ans', 'size' => 25,  'fixed' => false, 'sizepos' => 'LL',  'mandatory' => false, 'usage' => 'Additional response data'],
        45 =>  ['type' => 'ans', 'size' => 79,  'fixed' => false, 'sizepos' => 'LL',  'mandatory' => false, 'usage' => 'Track 1 Data'],
        46 =>  ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Additional data - ISO'],
        47 =>  ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Additional data - National'],
        48 =>  ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Additional data - Private'],
        49 =>  ['type' => 'n',   'size' => 3,   'fixed' => true,  'mandatory' => false, 'usage' => 'Currency code, transaction'],
        50 =>  ['type' => 'n',   'size' => 3,   'fixed' => true,  'mandatory' => false, 'usage' => 'Currency code, settlement'],
        51 =>  ['type' => 'n',   'size' => 3,   'fixed' => true,  'mandatory' => false, 'usage' => 'Currency code, cardholder billing'],
        52 =>  ['type' => 'b',   'size' => 8,   'fixed' => true,  'mandatory' => false, 'usage' => 'Personal Identification number data'],
        53 =>  ['type' => 'n',   'size' => 16,  'fixed' => true,  'mandatory' => false, 'usage' => 'Security related control information'],
        54 =>  ['type' => 'an',  'size' => 120, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Additional amounts'],
        55 =>  ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved ISO'],
        56 =>  ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved ISO'],
        57 =>  ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved National'],
        58 =>  ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved National'],
        59 =>  ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for national use'],
        60 =>  ['type' => 'ans', 'size' => 60,  'fixed' => false, 'sizepos' => 'LL',  'mandatory' => false, 'usage' => 'Advice Reason Code'],
        61 =>  ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved Private'],
        62 =>  ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved Private'],
        63 =>  ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved Private'],
        64 =>  ['type' => 'b',   'size' => 8,   'fixed' => true,  'mandatory' => false, 'usage' => 'Message authentication code (MAC)'],
        65 =>  ['type' => 'b',   'size' => 8,   'fixed' => true,  'mandatory' => false, 'usage' => 'Bit map, extended, tertiary'],
        66 =>  ['type' => 'n',   'size' => 1,   'fixed' => true,  'mandatory' => false, 'usage' => 'Settlement code'],
        67 =>  ['type' => 'n',   'size' => 2,   'fixed' => true,  'mandatory' => false, 'usage' => 'Extended payment code'],
        68 =>  ['type' => 'n',   'size' => 3,   'fixed' => true,  'mandatory' => false, 'usage' => 'Receiving institution country code'],
        69 =>  ['type' => 'n',   'size' => 3,   'fixed' => true,  'mandatory' => false, 'usage' => 'Settlement institution county code'],
        70 =>  ['type' => 'n',   'size' => 3,   'fixed' => true,  'mandatory' => false, 'usage' => 'Network management Information code'],
        71 =>  ['type' => 'n',   'size' => 4,   'fixed' => true,  'mandatory' => false, 'usage' => 'Message number'],
        72 =>  ['type' => 'n',   'size' => 4,   'fixed' => false, 'sizepos' => 'L',   'mandatory' => false, 'usage' => 'Message number, last'],
        73 =>  ['type' => 'n',   'size' => 6,   'fixed' => true,  'mandatory' => false, 'usage' => 'Date, Action'],
        74 =>  ['type' => 'n',   'size' => 10,  'fixed' => true,  'mandatory' => false, 'usage' => 'Credits, number'],
        75 =>  ['type' => 'n',   'size' => 10,  'fixed' => true,  'mandatory' => false, 'usage' => 'Credits, reversal number'],
        76 =>  ['type' => 'n',   'size' => 10,  'fixed' => true,  'mandatory' => false, 'usage' => 'Debits, number'],
        77 =>  ['type' => 'n',   'size' => 10,  'fixed' => true,  'mandatory' => false, 'usage' => 'Debits, reversal number'],
        78 =>  ['type' => 'n',   'size' => 10,  'fixed' => true,  'mandatory' => false, 'usage' => 'Transfer number'],
        79 =>  ['type' => 'n',   'size' => 10,  'fixed' => true,  'mandatory' => false, 'usage' => 'Transfer, reversal number'],
        80 =>  ['type' => 'n',   'size' => 10,  'fixed' => true,  'mandatory' => false, 'usage' => 'Inquiries number'],
        81 =>  ['type' => 'n',   'size' => 10,  'fixed' => true,  'mandatory' => false, 'usage' => 'Authorizations, number'],
        82 =>  ['type' => 'n',   'size' => 12,  'fixed' => true,  'mandatory' => false, 'usage' => 'Credits, processing fee amount'],
        83 =>  ['type' => 'n',   'size' => 12,  'fixed' => true,  'mandatory' => false, 'usage' => 'Credits, transaction fee amount'],
        84 =>  ['type' => 'n',   'size' => 12,  'fixed' => true,  'mandatory' => false, 'usage' => 'Debits, processing fee amount'],
        85 =>  ['type' => 'n',   'size' => 12,  'fixed' => true,  'mandatory' => false, 'usage' => 'Debits, transaction fee amount'],
        86 =>  ['type' => 'n',   'size' => 16,  'fixed' => true,  'mandatory' => false, 'usage' => 'Credits, amount'],
        87 =>  ['type' => 'n',   'size' => 16,  'fixed' => true,  'mandatory' => false, 'usage' => 'Credits, reversal amount'],
        88 =>  ['type' => 'n',   'size' => 16,  'fixed' => true,  'mandatory' => false, 'usage' => 'Debits, amount'],
        89 =>  ['type' => 'n',   'size' => 16,  'fixed' => true,  'mandatory' => false, 'usage' => 'Debits, reversal amount'],
        90 =>  ['type' => 'n',   'size' => 42,  'fixed' => true,  'mandatory' => false, 'usage' => 'Original data elements'],
        91 =>  ['type' => 'an',  'size' => 1,   'fixed' => true,  'mandatory' => false, 'usage' => 'File update code'],
        92 =>  ['type' => 'n',   'size' => 2,   'fixed' => true,  'mandatory' => false, 'usage' => 'File security code'],
        93 =>  ['type' => 'an',  'size' => 5,   'fixed' => true,  'mandatory' => false, 'usage' => 'Response indicator'],
        94 =>  ['type' => 'an',  'size' => 7,   'fixed' => true,  'mandatory' => false, 'usage' => 'Service indicator'],
        95 =>  ['type' => 'an',  'size' => 42,  'fixed' => true,  'mandatory' => false, 'usage' => 'Replacement amounts'],
        96 =>  ['type' => 'b',   'size' => 8,   'fixed' => true,  'mandatory' => false, 'usage' => 'Message security code'],
        97 =>  ['type' => 'n',   'size' => 16,  'fixed' => true,  'mandatory' => false, 'usage' => 'Amount, net settlement'],
        98 =>  ['type' => 'ans', 'size' => 25,  'fixed' => true,  'mandatory' => false, 'usage' => 'Payee'],
        99 =>  ['type' => 'n',   'size' => 11,  'fixed' => false, 'sizepos' => 'LL',  'mandatory' => false, 'usage' => 'Settlement institution identification code'],
        100 => ['type' => 'n',   'size' => 11,  'fixed' => false, 'sizepos' => 'LL',  'mandatory' => false, 'usage' => 'Receiving institution identification code'],
        101 => ['type' => 'ans', 'size' => 17,  'fixed' => false, 'sizepos' => 'LL',  'mandatory' => false, 'usage' => 'File name'],
        102 => ['type' => 'n',   'size' => 28,  'fixed' => false, 'sizepos' => 'LL',  'mandatory' => false, 'usage' => 'Account identification 1'],
        103 => ['type' => 'n',   'size' => 28,  'fixed' => false, 'sizepos' => 'LL',  'mandatory' => false, 'usage' => 'Account identification 2'],
        104 => ['type' => 'ans', 'size' => 100, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Transaction description'],
        105 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for ISO use'],
        106 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for ISO use'],
        107 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for ISO use'],
        108 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for ISO use'],
        109 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for ISO use'],
        110 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for ISO use'],
        111 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for ISO use'],
        112 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for national use'],
        113 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for national use'],
        114 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for national use'],
        115 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for national use'],
        116 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for national use'],
        117 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for national use'],
        118 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for national use'],
        119 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for national use'],
        120 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for private use'],
        121 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for private use'],
        122 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for private use'],
        123 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for private use'],
        124 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for private use'],
        125 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for private use'],
        126 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for private use'],
        127 => ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'mandatory' => false, 'usage' => 'Reserved for private use'],
        128 => ['type' => 'b',   'size' => 8,   'fixed' => true,  'mandatory' => false, 'usage' => 'Message Authentication code'],
    ];
    protected $BLOCKED_DATA_ELEMENT = [];

    private $_mti = '';
    private $_bitmap = '';
    private $_data = [];
    private $_data_encoded = [];
    private $_iso = '';
    private $_iso_encoded = '';

    // }}}

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos privados
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ private functions

    /**
     * Construye tabla de caracteres especiales
     */
    private function _caracteresEspeciales(): void
    {
        // Construye arreglo inicial
        $aSpecialChars = array_merge(
            range(32, 47), // ! ... /
            range(58, 64), // : ... @
            range(91, 96), // [ ... ´
            //
            range(123, 126), // { ... ~
            range(192, 197), // À ... Å
            range(199, 207), // Ç, È ... Ë, Ì ... Ï
            range(209, 214), // Ñ, Ò ... Ö
            range(218, 220), // Ú ... Ü
            range(223, 229), // ß, à ... å
            range(231, 239), // ç, è ... ë, ì ... ï
            range(241, 246), // ñ, ò ... ö, ì ... ï
            range(249, 252) // ù ... ü
        );
        $aAlphaChars = array_merge(
            range(65, 90), // A-Z
            range(97, 122) // a-z
        );
        $aNumChars = array_merge(
            range(48, 57) // 0-9
        );

        // Asigna número y ascii a SPECIAL_CHARS
        foreach($aSpecialChars as $cChar) {
            $this->SPECIAL_CHARS_S[$cChar] = chr($cChar);
            $this->SPECIAL_CHARS_AS[$cChar] = chr($cChar);
            $this->SPECIAL_CHARS_ANS[$cChar] = chr($cChar);
            $this->SPECIAL_CHARS_ANSB[$cChar] = chr($cChar);
            $this->SPECIAL_CHARS_NS[$cChar] = chr($cChar);
        }
        // Asigna caracteres alpha
        foreach($aAlphaChars as $cChar) {
            $this->SPECIAL_CHARS_AS[$cChar] = chr($cChar);
            $this->SPECIAL_CHARS_ANS[$cChar] = chr($cChar);
            $this->SPECIAL_CHARS_ANSB[$cChar] = chr($cChar);
        }
        // Asigna caracteres num
        foreach($aNumChars as $cChar) {
            $this->SPECIAL_CHARS_ANS[$cChar] = chr($cChar);
            $this->SPECIAL_CHARS_ANSB[$cChar] = chr($cChar);
            $this->SPECIAL_CHARS_NS[$cChar] = chr($cChar);
        }
    }

    /**
     * Empaca valor en el formato del elemento definido
     *
     * @param int $bit Index de dato.
     * @param string $data Valor del dato.
     * @param bool $bRaw Determina si el valor se debe poner sin procesado
     *
     * @return void
     */
    private function _packElement(int $bit, string $data, bool $bRaw = false): void
    {
        // Asigna valor si es raw
        if ($bRaw) {
            $this->_data[$bit] = $data;
            $this->_data_encoded[$bit] = $data;
            return;
        }
        // Por tipo de dato
        switch($this->DATA_ELEMENT[$bit]['type']) {
            case "n": // Caracteres numéricos
                $data = str_replace(".", "", $data);
                // Tamaño
                if ($this->DATA_ELEMENT[$bit]['fixed']) {
                    // Fijo
                    $this->_data[$bit] = sprintf("%0" . $this->DATA_ELEMENT[$bit]['size'] . "s", $data);
                } else {
                    // Variable
                    if (strlen($data) <= $this->DATA_ELEMENT[$bit]['size']) {
                        $this->_data[$bit] = sprintf("%0" . strlen($this->DATA_ELEMENT[$bit]['sizepos']) . "d", strlen($data)) . $data;
                    }
                }
                break;
            case "an": // Caracteres numéricos
            case "anp": // Caracteres alfanuméricos y espacios
            case "as": // Caracteres alfabéticos y especiales
            case "ansb": // Caracteres alfanuméricos y especiales
            case "ans": // Caracteres alfanuméricos y especiales
            case "s": // Caracteres alfanuméricos y especiales
            case "ns": // Caracteres alfanuméricos y especiales
            case "z": //
                // Tamaño
                if ($this->DATA_ELEMENT[$bit]['fixed']) {
                    // Fijo
                    $sPadType = $this->DATA_ELEMENT[$bit]['format']['pad_type'] ?? 'left';
                    $this->_data[$bit] = sprintf("%"
                        . ($sPadType == 'right' ? '-' : '')
                        . ($this->DATA_ELEMENT[$bit]['format']['pad_string'] ?? ' ')
                        . $this->DATA_ELEMENT[$bit]['size']
                        . "s", $data);
                } else {
                    // Variable
                    if (strlen($data) <= $this->DATA_ELEMENT[$bit]['size']) {
                        $this->_data[$bit] = sprintf("%0" . strlen($this->DATA_ELEMENT[$bit]['sizepos']) . "s", strlen($data)) . $data;
                    }
                }
                break;
            case "b": // Caracteres alfanuméricos y especiales
                // Tamaño
                if ($this->DATA_ELEMENT[$bit]['fixed']) {
                    // Fijo
                    $tmp = sprintf("%0" . $this->DATA_ELEMENT[$bit]['size'] . "d", $data);
                    while ($tmp != '') {
                        $this->_data[$bit] .= base_convert(substr($tmp, 0, 4), 2, 16);
                        $tmp = substr($tmp, 4, strlen($tmp) - 4);
                    }
                }
                break;
        }

        // Codifica en formato requerido
        if (!isset($this->DATA_ELEMENT[$bit]['encoding'])) {
            $this->DATA_ELEMENT[$bit]['encoding'] = 'hex';
        }
        if (!isset($this->DATA_ELEMENT[$bit]['charset'])) {
            $this->DATA_ELEMENT[$bit]['charset'] = 'EBCDIC';
        }
        switch($this->DATA_ELEMENT[$bit]['encoding']) {
            case "hex": // En hexadecimal
                if ($this->DATA_ELEMENT[$bit]['charset'] == 'EBCDIC') {
                    $this->_data_encoded[$bit] = $this->ascii2ebcdic_hex($this->_data[$bit]);
                } else if ($this->DATA_ELEMENT[$bit]['charset'] == 'ASCII') {
                    $this->_data_encoded[$bit] = unpack('H*', $this->_data[$bit])[1];
                }
                break;
            case "string": // En hexadecimal
                if ($this->DATA_ELEMENT[$bit]['charset'] == 'EBCDIC') {
                    $this->_data_encoded[$bit] = $this->_data[$bit];
                }
                break;
            case "bin": // En hexadecimal
                $this->_data_encoded[$bit] = base_convert(unpack('H*', $this->_data[$bit])[1], 16, 2);
                break;
        }

    }

    /**
     * Calcula bitmap del data element
     */
    private function _calculateBitmaps()
    {
        // Ordena datos
        ksort($this->_data);
        ksort($this->_data_encoded);
        $tmp = sprintf("%064d", 0);
        $tmp2 = sprintf("%064d", 0);
        foreach ($this->_data as $key => $val) {
            if ($key < 65) {
                $tmp[$key - 1] = 1;
            } else {
                $tmp[0] = 1;
                $tmp2[$key - 65] = 1;
            }
        }
        // Bitmap secundario
        if ($tmp[0] == 1 || !empty($this->DATA_ELEMENT[1]['mandatory'])) {
            $this->_data[1] = "";
            while ($tmp2 != '') {
                $this->_data[1] .= base_convert(substr($tmp2, 0, 4), 2, 16);
                $tmp2 = substr($tmp2, 4, strlen($tmp2) - 4);
            }
        }
        // Bitmap primario
        $this->_bitmap = "";
        while ($tmp != '') {
            $this->_bitmap .= base_convert(substr($tmp, 0, 4), 2, 16);
            $tmp = substr($tmp, 4, strlen($tmp) - 4);
        }
        // Ordena campos
        ksort($this->_data);
        ksort($this->_data_encoded);
        // Actualiza ISO
        $this->_iso = $this->_mti . $this->_bitmap . implode($this->_data);
        $this->_iso_encoded = $this->ascii2ebcdic_hex($this->_mti) . $this->_bitmap . implode($this->_data_encoded);
        // Regresa resultado
        return $this->_bitmap;
    }

    /**
     * Define el MTI del string ISO8586
     *
     * @param string $mti MTI del string ISO8586.
     *
     * @return bool Regresa si es válido el MTI
     */
    private function _setMTI(string $mti): bool
    {
        // Valida MTI
        $this->_validaData($mti, $this->HEADER_ELEMENT['MTI']);
        // Asigna
        $this->_mti = $mti;
        // Actualiza ISO
        $this->_iso = $this->_mti . $this->_bitmap . implode($this->_data);
        $this->_iso_encoded = $this->ascii2ebcdic_hex($this->_mti) . $this->_bitmap . implode($this->_data_encoded);
        // Regresa resultado
        return true;
    }

    /**
     * Formatea el dato si está definido en el elemento.
     *
     * @param mixed $data Dato a validar.
     * @param array $aDataElementDefinition Data element definition.
     *
     * @return string Regresa el dato formateado.
     */
    private function _formateaData($data, array $aDataElementDefinition): string
    {
        // Verifica definición de formato
        if(isset($aDataElementDefinition['format'])) {
            // Formato padding
            // pad_type = [right, left, center]
            // pad_length = $aDataElementDefinition['size']
            // pad_string = ' '
            if(isset($aDataElementDefinition['format']['pad_type'])) {
                if ($aDataElementDefinition['format']['pad_type'] == 'left') {
                    $cPadType = STR_PAD_LEFT;
                } else if ($aDataElementDefinition['format']['pad_type'] == 'center') {
                    $cPadType = STR_PAD_BOTH;
                } else {
                    $cPadType = STR_PAD_RIGHT;
                }
                $data = str_pad(
                    $data,
                    $aDataElementDefinition['format']['pad_length'] ?? $aDataElementDefinition['size'],
                    $aDataElementDefinition['format']['pad_string'] ?? ' ',
                    $cPadType
                );
            }
        }
        return $data;
    }

    /**
     * Valida el tipo, tamaño y mandatorio de un dato.
     *
     * @param mixed $data Dato a validar.
     * @param array $aDataElementDefinition Data element definition.
     *
     * @return bool Regresa si es válido el dato.
     */
    private function _validaData($data, array $aDataElementDefinition): bool
    {
        // Valida si es mandatorio
        if($aDataElementDefinition['mandatory'] && strlen($data) < 1) {
            throw new \Exception("Campo mandatorio no proporcionado.");
        }
        // Valida tamaño en bytes
        if ($aDataElementDefinition['fixed']) {
            if ($aDataElementDefinition['size'] > 0 && strlen($data) > $aDataElementDefinition['size']) {
                throw new \Exception("Tamaño de campo excedido: " . strlen($data) . " [Máximo permitido: {$aDataElementDefinition['size']}].");
            }
        } else {
            if ($aDataElementDefinition['size'] > 0 && strlen($aDataElementDefinition['size'] . $data) > $aDataElementDefinition['size']) {
                throw new \Exception("Tamaño de campo excedido: " . strlen($data) . " [Máximo permitido: {$aDataElementDefinition['size']}].");
            }
        }
        // Valida tipo
        switch($aDataElementDefinition['type']) {
            case "n": // Caracteres numéricos
                if (!is_numeric($data)) {
                    throw new \Exception("Tipo de campo n '{$aDataElementDefinition['type']}' inválido: '" . $data . "'");
                }
                break;
            case "a": // Caracteres alfabéticos
                if (!ctype_alpha($data)) {
                    throw new \Exception("Tipo de campo a '{$aDataElementDefinition['type']}' inválido: '" . $data . "'");
                }
                break;
            case "an": // Caracteres alfabéticos y/o numéricos
                if (!ctype_alnum($data)) {
                    throw new \Exception("Tipo de campo an '{$aDataElementDefinition['type']}' inválido: '" . $data . "'");
                }
                break;
            case "anp": // Caracteres alfabéticos, numéricos y espacios
                if(!preg_match('!^[0-9A-Za-z ]*$!', $data)) {
                    throw new \Exception("Tipo de campo anp '{$aDataElementDefinition['type']}' inválido: '" . $data . "'");
                }
                break;
            case "as": // Caracteres alfabéticos y/o especiales
                if(!$this->_validateAsciiChars($data, $this->SPECIAL_CHARS_AS)) {
                    throw new \Exception("Tipo de campo as '{$aDataElementDefinition['type']}' inválido: '" . $data . "'");
                }
                break;
            case "ansb": // Caracteres alfabéticos, numéricos y/o especiales, binarios
                if(!$this->_validateAsciiChars($data, $this->SPECIAL_CHARS_ANSB)) {
                    throw new \Exception("Tipo de campo ans '{$aDataElementDefinition['type']}' inválido: '" . $data . "'");
                }
                break;
            case "ans": // Caracteres alfabéticos, numéricos y/o especiales
                if(!$this->_validateAsciiChars($data, $this->SPECIAL_CHARS_ANS)) {
                    throw new \Exception("Tipo de campo ans '{$aDataElementDefinition['type']}' inválido: '" . $data . "'");
                }
                break;
            case "s": // Caracteres especiales (ASCII character set 32 - 126)
                if(!$this->_validateAsciiChars($data, $this->SPECIAL_CHARS_S)) {
                    throw new \Exception("Tipo de campo s '{$aDataElementDefinition['type']}' inválido: '" . $data . "'");
                }
                break;
            case "ns": // Caracteres numéricos y/o especiales
                if(!$this->_validateAsciiChars($data, $this->SPECIAL_CHARS_NS)) {
                    throw new \Exception("Tipo de campo '{$aDataElementDefinition['type']}' inválido: '" . $data . "'");
                }
                break;
            case "b": // Binario
                break;
            case "z": // Tracks 2 y 3 code set como se define en la ISO 4909 y en ISO 7813.
                break;
        }
        // Valida texto
        if (isset($aDataElementDefinition['text']) && $aDataElementDefinition['text'] == 'uppercase') {
            if(preg_match("/[a-z]/", $data)) {
                throw new \Exception("Campo inválido, contiene caracteres en minúsculas.");
            }
        }
        // Validación terminada, dato OK
        return true;
    }

    private function _validateAsciiChars(string $sData, array $aAsciiCharTable): bool
    {
        foreach(str_split($sData) as $cChar) {
            if (!in_array(ord($cChar), array_keys($aAsciiCharTable))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Parse iso string and set mti, bitmap & data
     *
     * @param string $sIso String ISO
     *
     * @return void
     */
    private function _parseIso(string $sIso): void
    {
        // Revisa si está codificado en hexadecimal
        $bIsEncoded = false;
        if (isset($sIso[0]) && $sIso[0] == 'F') {
            $bIsEncoded = true;
        }
        if ($bIsEncoded) {
            // Obtiene mti del string en ebcdic
            $this->_mti = $this->ebcdic_hex2ascii(substr($sIso, 0, 8));
            // Obtiene el bitmap
            $this->_bitmap = substr($sIso, 8, 16);
            // Divide los datos acorde al bitmap
            $this->_parseIsoData(substr($sIso, 24), $bIsEncoded);
        } else {
            // Obtiene mti del string en ebcdic
            $this->_mti = substr($sIso, 0, 4);
            // Obtiene el bitmap
            $this->_bitmap = substr($sIso, 4, 16);
            // Divide los datos acorde al bitmap
            $this->_parseIsoData(substr($sIso, 20), $bIsEncoded);
        }
    }

    /**
     * Parse decoded iso data
     *
     * @param string $sData Data
     * @param bool $bIsEncoded Está codificado
     *
     * @return void
     */
    private function _parseIsoData(string $sData, bool $bIsEncoded): void
    {
        // Bitmap primario
        $sBitmapBits = '';
        foreach(str_split($this->_bitmap) as $sBit) {
            $sBitmapBits .= sprintf("%04d", base_convert($sBit, 16, 2));
        }
        // Bitmap secundario
        if ($sBitmapBits[0] == 1) {
            foreach(str_split(substr($sData, 0, $this->DATA_ELEMENT[1]['size'])) as $sBit) {
                $sBitmapBits .= sprintf("%04d", base_convert($sBit, 16, 2));
            }
        }
        // Inserta datos
        $i = 1;
        foreach(str_split($sBitmapBits) as $sActivePos) {
            if ($sActivePos == 1) {
                $iElementSize = $this->DATA_ELEMENT[$i]['size'];
                if ($this->DATA_ELEMENT[$i]['fixed'] == false) {
                    if ($bIsEncoded) {
                        $iElementSize = intval($this->ebcdic_hex2ascii(substr($sData, 0, strlen($this->DATA_ELEMENT[$i]['sizepos']) * 2)));
                        $sData = substr($sData, strlen($this->DATA_ELEMENT[$i]['sizepos']) * 2);
                    } else {
                        $iElementSize = intval(substr($sData, 0, strlen($this->DATA_ELEMENT[$i]['sizepos'])));
                        $sData = substr($sData, strlen($this->DATA_ELEMENT[$i]['sizepos']));
                    }
                }
                if ($this->DATA_ELEMENT[$i]['type'] == 'b' || !$bIsEncoded) {
                    $sElementData = substr($sData, 0, $iElementSize);
                    $sData = substr($sData, $iElementSize);
                } else {
                    $sElementData = $this->ebcdic_hex2ascii(substr($sData, 0, ($iElementSize * 2)));
                    $sData = substr($sData, ($iElementSize * 2));
                }
                $this->_packElement($i, $sElementData);
            }
            $i++;
        }
    }

    /**
     * Borra todos los datos
     *
     * @return void
     */
    private function _clear(): void
    {
        $this->_mti = '';
        $this->_bitmap = '';
        $this->_data = [];
        $this->_data_encoded = [];
        $this->_iso = '';
        $this->_iso_encoded = '';
    }

    // }}}

    /**
     * --------------------------------------------------------------------------------------------------------
     * Métodos protegidos
     * --------------------------------------------------------------------------------------------------------
     */
    // {{{ protected functions

    /**
     * Agrega el arreglo a la definición de elementos de datos del esquema ISO8586
     *
     * @param array $aCustomHeaderElement Arreglo con definición de elementos del header a actualizar/agregar.
     * @param array $aCustomDataElement Arreglo con definición de elementos de datos a actualizar/agregar.
     *
     * @return void
     */
    protected function updateDefinition(array $aCustomHeaderElement, array $aCustomDataElement): void
    {
        // Obtiene el índice mayor
        $iMaxKey = max(max(array_keys($this->DATA_ELEMENT)), max(array_keys($aCustomDataElement)));
        // Actualiza y agrega elementos
        for($i = 0; $i <= $iMaxKey; $i++) {
            if (isset($aCustomDataElement[$i])) {
                if (isset($this->DATA_ELEMENT[$i])) {
                    $this->DATA_ELEMENT[$i] = array_merge($this->DATA_ELEMENT[$i], $aCustomDataElement[$i]);
                } else {
                    $this->DATA_ELEMENT[$i] = $aCustomDataElement[$i];
                }
            }
        }
        // Actualiza elementos del header
        foreach($aCustomHeaderElement as $sKey => $aVal) {
            if (isset($this->HEADER_ELEMENT[$sKey])) {
                $this->HEADER_ELEMENT[$sKey] = array_merge($this->HEADER_ELEMENT[$sKey], $aVal);
            } else {
                $this->HEADER_ELEMENT[$sKey] = $aVal;
            }
        }
    }

    /**
     * Convierte un string en ascii a su representacion hex ebcdic
     *
     * @param string $sString String en ascii
     *
     * @return string String en hex ebcdic
     */
    protected function ascii2ebcdic_hex(string $sString): string
    {
        // ASCII Hex to EBCDIC Hex Map
        $ascii_hex4ebcdic_hex = [
            '00' => '00', '01' => '01', '02' => '02', '03' => '03', '04' => '1a', '05' => '09', '06' => '1a', '07' => '7f', '08' => '1a', '09' => '1a', '0a' => '1a',
            '0b' => '0b', '0c' => '0c', '0d' => '0d', '0e' => '0e', '0f' => '0f', '10' => '10', '11' => '11', '12' => '12', '13' => '13', '14' => '3c', '15' => '3d',
            '16' => '32', '17' => '26', '18' => '18', '19' => '19', '1a' => '3f', '1b' => '27', '1c' => '1c', '1d' => '1d', '1e' => '1e', '1f' => '1f', '20' => '40',
            '21' => '4f', '22' => '7f', '23' => '7b', '24' => '5b', '25' => '6c', '26' => '50', '27' => '7d', '28' => '4d', '29' => '5d', '2a' => '5c', '2b' => '4e',
            '2c' => '6b', '2d' => '60', '2e' => '4b', '2f' => '61', '30' => 'f0', '31' => 'f1', '32' => 'f2', '33' => 'f3', '34' => 'f4', '35' => 'f5', '36' => 'f6',
            '37' => 'f7', '38' => 'f8', '39' => 'f9', '3a' => '7a', '3b' => '5e', '3c' => '4c', '3d' => '7e', '3e' => '6e', '3f' => '6f', '40' => '7c', '41' => 'c1',
            '42' => 'c2', '43' => 'c3', '44' => 'c4', '45' => 'c5', '46' => 'c6', '47' => 'c7', '48' => 'c8', '49' => 'c9', '4a' => 'd1', '4b' => 'd2', '4c' => 'd3',
            '4d' => 'd4', '4e' => 'd5', '4f' => 'd6', '50' => 'd7', '51' => 'd8', '52' => 'd9', '53' => 'e2', '54' => 'e3', '55' => 'e4', '56' => 'e5', '57' => 'e6',
            '58' => 'e7', '59' => 'e8', '5a' => 'e9', '5b' => '4a', '5c' => 'e0', '5d' => '5a', '5e' => '5f', '5f' => '6d', '60' => '79', '61' => '81', '62' => '82',
            '63' => '83', '64' => '84', '65' => '85', '66' => '86', '67' => '87', '68' => '88', '69' => '89', '6a' => '91', '6b' => '92', '6c' => '93', '6d' => '94',
            '6e' => '95', '6f' => '96', '70' => '97', '71' => '98', '72' => '99', '73' => 'a2', '74' => 'a3', '75' => 'a4', '76' => 'a5', '77' => 'a6', '78' => 'a7',
            '79' => 'a8', '7a' => 'a9', '7b' => 'c0', '7c' => '6a', '7d' => 'd0', '7e' => 'a1', '7f' => '07', '80' => '3f', '81' => '3f', '82' => '3f', '83' => '3f',
            '84' => '3f', '85' => '3f', '86' => '3f', '87' => '3f', '88' => '3f', '89' => '3f', '8a' => '3f', '8b' => '3f', '8c' => '3f', '8d' => '3f', '8e' => '3f',
            '8f' => '3f', '90' => '3f', '91' => '3f', '92' => '3f', '93' => '3f', '94' => '3f', '95' => '3f', '96' => '3f', '97' => '3f', '98' => '3f', '99' => '3f',
            '9a' => '3f', '9b' => '3f', '9c' => '3f', '9d' => '3f', '9e' => '3f', '9f' => '3f', 'a0' => '3f', 'a1' => '3f', 'a2' => '3f', 'a3' => '3f', 'a4' => '3f',
            'a5' => '3f', 'a6' => '3f', 'a7' => '3f', 'a8' => '3f', 'a9' => '3f', 'aa' => '3f', 'ab' => '3f', 'ac' => '3f', 'ad' => '3f', 'ae' => '3f', 'af' => '3f',
            'b0' => '3f', 'b1' => '3f', 'b2' => '3f', 'b3' => '3f', 'b4' => '3f', 'b5' => '3f', 'b6' => '3f', 'b7' => '3f', 'b8' => '3f', 'b9' => '3f', 'ba' => '3f',
            'bb' => '3f', 'bc' => '3f', 'bd' => '3f', 'be' => '3f', 'bf' => '3f', 'c0' => '3f', 'c1' => '3f', 'c2' => '3f', 'c3' => '3f', 'c4' => '3f', 'c5' => '3f',
            'c6' => '3f', 'c7' => '3f', 'c8' => '3f', 'c9' => '3f', 'ca' => '3f', 'cb' => '3f', 'cc' => '3f', 'cd' => '3f', 'ce' => '3f', 'cf' => '3f', 'd0' => '3f',
            'd1' => '3f', 'd2' => '3f', 'd3' => '3f', 'd4' => '3f', 'd5' => '3f', 'd6' => '3f', 'd7' => '3f', 'd8' => '3f', 'd9' => '3f', 'da' => '3f', 'db' => '3f',
            'dc' => '3f', 'dd' => '3f', 'de' => '3f', 'df' => '3f', 'e0' => '3f', 'e1' => '3f', 'e2' => '3f', 'e3' => '3f', 'e4' => '3f', 'e5' => '3f', 'e6' => '3f',
            'e7' => '3f', 'e8' => '3f', 'e9' => '3f', 'ea' => '3f', 'eb' => '3f', 'ec' => '3f', 'ed' => '3f', 'ee' => '3f', 'ef' => '3f', 'f0' => '3f', 'f1' => '3f',
            'f2' => '3f', 'f3' => '3f', 'f4' => '3f', 'f5' => '3f', 'f6' => '3f', 'f7' => '3f', 'f8' => '3f', 'f9' => '3f', 'fa' => '3f', 'fb' => '3f', 'fc' => '3f',
            'fd' => '3f', 'fe' => '3f', 'ff' => '3f'
        ];
        // Parse each char from ascii string and transform it to ebcdic hex
        $hString = '';
        $iLength = strlen($sString);
        for ($i = 0; $i < $iLength; $i++) {
            $hString .= $ascii_hex4ebcdic_hex[bin2hex($sString[$i])];
        }
        // Return result
        return $hString;
    }

    /**
     * Convierte un string hex ebcdic a un string ascii
     *
     * @param string $hEbcdicString String en hex ebcdic
     *
     * @return string String en ascii
     */
    protected function ebcdic_hex2ascii(string $hEbcdicString): string
    {
        // EBCDIC Hex to ASCII Hex Map
        $ebcdic_hex2ascii_hex = [
            '00' => '00', '01' => '01', '02' => '02', '03' => '03', '04' => '1a', '05' => '09', '06' => '1a', '07' => '7f', '08' => '1a', '09' => '1a', '0a' => '1a',
            '0b' => '0b', '0c' => '0c', '0d' => '0d', '0e' => '0e', '0f' => '0f', '10' => '10', '11' => '11', '12' => '12', '13' => '13', '14' => '1a', '15' => '1a',
            '16' => '08', '17' => '1a', '18' => '18', '19' => '19', '1a' => '1a', '1b' => '1a', '1c' => '1c', '1d' => '1d', '1e' => '1e', '1f' => '1f', '20' => '1a',
            '21' => '1a', '22' => '1a', '23' => '1a', '24' => '1a', '25' => '0a', '26' => '17', '27' => '1b', '28' => '1a', '29' => '1a', '2a' => '1a', '2b' => '1a',
            '2c' => '1a', '2d' => '05', '2e' => '06', '2f' => '07', '30' => '1a', '31' => '1a', '32' => '16', '33' => '1a', '34' => '1a', '35' => '1a', '36' => '1a',
            '37' => '04', '38' => '1a', '39' => '1a', '3a' => '1a', '3b' => '1a', '3c' => '14', '3d' => '15', '3e' => '1a', '3f' => '1a', '40' => '20', '41' => '1a',
            '42' => '1a', '43' => '1a', '44' => '1a', '45' => '1a', '46' => '1a', '47' => '1a', '48' => '1a', '49' => '1a', '4a' => '5b', '4b' => '2e', '4c' => '3c',
            '4d' => '28', '4e' => '2b', '4f' => '21', '50' => '26', '51' => '1a', '52' => '1a', '53' => '1a', '54' => '1a', '55' => '1a', '56' => '1a', '57' => '1a',
            '58' => '1a', '59' => '1a', '5a' => '5d', '5b' => '24', '5c' => '2a', '5d' => '29', '5e' => '3b', '5f' => '5e', '60' => '2d', '61' => '1a', '62' => '1a',
            '63' => '1a', '64' => '1a', '65' => '1a', '66' => '1a', '67' => '1a', '68' => '1a', '69' => '1a', '6a' => '7c', '6b' => '2c', '6c' => '25', '6d' => '5f',
            '6e' => '3e', '6f' => '3f', '70' => '1a', '71' => '1a', '72' => '1a', '73' => '1a', '74' => '1a', '75' => '1a', '76' => '1a', '77' => '1a', '78' => '1a',
            '79' => '60', '7a' => '3a', '7b' => '23', '7c' => '40', '7d' => '27', '7e' => '3d', '7f' => '22', '80' => '1a', '81' => '61', '82' => '62', '83' => '63',
            '84' => '64', '85' => '65', '86' => '66', '87' => '67', '88' => '68', '89' => '69', '8a' => '1a', '8b' => '1a', '8c' => '1a', '8d' => '1a', '8e' => '1a',
            '8f' => '1a', '90' => '1a', '91' => '6a', '92' => '6b', '93' => '6c', '94' => '6d', '95' => '6e', '96' => '6f', '97' => '70', '98' => '71', '99' => '72',
            '9a' => '1a', '9b' => '1a', '9c' => '1a', '9d' => '1a', '9e' => '1a', '9f' => '1a', 'a0' => '1a', 'a1' => '7e', 'a2' => '73', 'a3' => '74', 'a4' => '75',
            'a5' => '76', 'a6' => '77', 'a7' => '78', 'a8' => '79', 'a9' => '7a', 'aa' => '1a', 'ab' => '1a', 'ac' => '1a', 'ad' => '1a', 'ae' => '1a', 'af' => '1a',
            'b0' => '1a', 'b1' => '1a', 'b2' => '1a', 'b3' => '1a', 'b4' => '1a', 'b5' => '1a', 'b6' => '1a', 'b7' => '1a', 'b8' => '1a', 'b9' => '1a', 'ba' => '1a',
            'bb' => '1a', 'bc' => '1a', 'bd' => '1a', 'be' => '1a', 'bf' => '1a', 'c0' => '7b', 'c1' => '41', 'c2' => '42', 'c3' => '43', 'c4' => '44', 'c5' => '45',
            'c6' => '46', 'c7' => '47', 'c8' => '48', 'c9' => '49', 'ca' => '1a', 'cb' => '1a', 'cc' => '1a', 'cd' => '1a', 'ce' => '1a', 'cf' => '1a', 'd0' => '7d',
            'd1' => '4a', 'd2' => '4b', 'd3' => '4c', 'd4' => '4d', 'd5' => '4e', 'd6' => '4f', 'd7' => '50', 'd8' => '51', 'd9' => '52', 'da' => '1a', 'db' => '1a',
            'dc' => '1a', 'dd' => '1a', 'de' => '1a', 'df' => '1a', 'e0' => '5c', 'e1' => '1a', 'e2' => '53', 'e3' => '54', 'e4' => '55', 'e5' => '56', 'e6' => '57',
            'e7' => '58', 'e8' => '59', 'e9' => '5a', 'ea' => '1a', 'eb' => '1a', 'ec' => '1a', 'ed' => '1a', 'ee' => '1a', 'ef' => '1a', 'f0' => '30', 'f1' => '31',
            'f2' => '32', 'f3' => '33', 'f4' => '34', 'f5' => '35', 'f6' => '36', 'f7' => '37', 'f8' => '38', 'f9' => '39', 'fa' => '1a', 'fb' => '1a', 'fc' => '1a',
            'fd' => '1a', 'fe' => '1a', 'ff' => '1a'
        ];
        // Parse each hex value from ebcdic string and transform it to ascii char
        $sString = '';
        $aEbcdicChars = str_split(strtolower($hEbcdicString), 2);
        foreach($aEbcdicChars as $hEbcdicChar) {
            $sString .= hex2bin($ebcdic_hex2ascii_hex[$hEbcdicChar]);
        }
        // Return result
        return $sString;
    }

    /**
     * Evalúa el contenido del campo.
     * Nota: No se utiliza el contenido o la definición del $iBit por ser recursivo para subcampos.
     *
     * @param int $iBit Posición de elemento de datos.
     * @param string $sContenido Valor del dato.
     * @param array $aDef Arreglo de definición del campo.
     *
     * @return string String en ascii
     */
    protected function _evaluaData(int $iBit, string $sContenido, array $aDef) {
        // Variables
        $aTipos = [
            'n' => 'N - Numérico',
            'a' => 'A - Caracteres alfabéticos',
            'an' => 'AN - Alfanuméricos',
            'anp' => 'ANP - Alfanuméricos y/o espacios',
            'as' => 'AS - Caracteres alfabéticos y/o especiales',
            'ans' => 'ANS - Alfanuméricos y/o especiales',
            'ansb' => 'ANSB - Alfanuméricos y/o especiales y/o binarios',
            's' => 'S - Caracteres especiales ASCII character set 32 - 126',
            'ns' => 'NS - Caracteres numéricos y/o especiales',
            'b' => 'B - Binario',
            'z' => 'Z - Tracks 2 y 3 code set como se define en la ISO 4909 y en ISO 7813',
        ];
        $aFormato = [
            'alineado' => [
                'right' => 'Derecha',
                'left' => 'Izquierda',
            ],
        ];
        // Evalua campo
        $aResultado = [
            'tipo' => $aTipos[$aDef['type']] ?? $aDef['type'],
            'tamanio' => $aDef['fixed'] ? 'Fijo' : 'Variable',
            'tamanio_max' => $aDef['size'] ?? 'N/A',
            'tamanio_real' => strlen($sContenido),
            'tamanio_posiciones' => $aDef['fixed'] ? 0 : strlen($aDef['sizepos'] ?? ''),
            'mandatorio' => $aDef['mandatory'] ? 'Obligatorio' : 'Opcional',
            'descripcion' => $aDef['usage'] ?? 'Indeterminado',
            'encoding' => $aDef['encoding'] ?? 'hex',
            'charset' => $aDef['charset'] ?? 'EBCDIC',
            'formato' => [
                'relleno' => $aDef['format']['pad_string'] ?? ($aDef['type'] == 'n' ? '0' : ' '),
                'alineado' => $aFormato['alineado'][$aDef['format']['pad_type'] ?? 'left'],
            ],
            'contenido' => [],
        ];
        // Procesa contenido
        $aResultado['contenido']['raw'] = $sContenido;
        if ($aDef['fixed']) {
            $aResultado['contenido']['valor'] = $sContenido;
            $aResultado['contenido']['tamanio'] = $aDef['size'];
        } else {
            $aResultado['contenido']['valor'] = substr($sContenido, $aResultado['tamanio_posiciones']);
            $aResultado['contenido']['tamanio'] = (int) substr($sContenido, 0, $aResultado['tamanio_posiciones']);
        }
        $aResultado['contenido']['tamanio_real'] = strlen($aResultado['contenido']['valor']);
        if ($aDef['format']['pad_type'] ?? 'left' == 'left') {
            $aResultado['contenido']['valor_sin_formato'] = ltrim($aResultado['contenido']['valor'], $aResultado['formato']['relleno']);
        } else {
            $aResultado['contenido']['valor_sin_formato'] = rtrim($aResultado['contenido']['valor'], $aResultado['formato']['relleno']);
        }
        // Evalua validez del contenido
        if (!empty($aDef['values']) && is_array($aDef['values'])) {
            if (array_key_exists($aResultado['contenido']['valor'], $aDef['values'])) {
                $aResultado['contenido']['valido'] = true;
                $aResultado['contenido']['descripcion'] = $aDef['values'][$aResultado['contenido']['valor']]['usage'] ?? 'No determinado';
            } else {
                $aResultado['contenido']['valido'] = false;
                $aResultado['contenido']['error'] = 'ERROR: Valor no válido.';
            }
            $aResultado['contenido']['opciones'] = json_encode(array_keys($aDef['values']));
        }
        if ($aDef['mandatory'] ?? false) {
            if ($aResultado['contenido']['valor'] == '') {
                $aResultado['contenido']['valido'] = false;
                $aResultado['contenido']['error'] = 'Campo mandatorio vacío.';
            }
        }
        // Evalúa subcampos
        if (!empty($aDef['subfields'])) {
            $aResultado['subcampos'] = [];
            $sContenidoRestante = $aResultado['contenido']['valor'];
            foreach($aDef['subfields'] as $iSubBit => $aSubDef) {
                // Valores por default
                $aSubDef['type'] = $aSubDef['type'] ?? $aDef['type'];
                $aSubDef['fixed'] = $aSubDef['fixed'] ?? $aDef['fixed'];
                $aSubDef['encoding'] = $aSubDef['encoding'] ?? $aResultado['encoding'];
                $aSubDef['charset'] = $aSubDef['charset'] ?? $aResultado['charset'];
                $aSubDef['formato']['pad_string'] = $aSubDef['formato']['pad_string'] ?? $aResultado['formato']['relleno'];
                $aSubDef['formato']['pad_type'] = $aSubDef['formato']['pad_type'] ?? ($aDef['format']['pad_type'] ?? 'left');
                // Contenido
                if ($aSubDef['fixed']) {
                    $sSubContenido = substr($sContenidoRestante, 0, $aSubDef['size']);
                    $sContenidoRestante = substr($sContenidoRestante, $aSubDef['size']);
                } else {
                    if (!empty($aDef['subfields_separator'])) {
                        $iSepPos = strpos($sContenidoRestante, $aDef['subfields_separator']);
                        if ($iSepPos !== false) {
                            $sSubContenido = substr($sContenidoRestante, 0, $iSepPos);
                            $sContenidoRestante = substr($sContenidoRestante, $iSepPos + 1);
                        } else {
                            $sSubContenido = $sContenidoRestante;
                        }
                    } else {
                        $sSubContenido = $sContenidoRestante;
                    }
                }
                // Resultado de evaluación
                $aSubResultado = $this->_evaluaData($iSubBit, $sSubContenido, $aSubDef);
                // Regresa resultado
                $aResultado['subcampos'][$iSubBit] = $aSubResultado;
            }
        }
        // Regresa resultados
        return $aResultado;
    }

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
        // Construye arreglo de caracteres especiales
        $this->_caracteresEspeciales();
    }

    /**
     * Obtiene MTI del string ISO8586
     *
     * @return string
     */
    public function getMTI(): string
    {
        return $this->_mti;
    }

    /**
     * Define el MTI del string ISO8586
     *
     * @param string $mti MTI del string ISO8586.
     *
     * @return bool
     */
    public function setMTI(string $mti): bool
    {
        return $this->_setMTI($mti);
    }

    /**
     * Agrega data al elemento de datos del string ISO8586
     *
     * @param int $bit Posición de elemento de datos.
     * @param string $data Valor del dato.
     * @param bool $bRaw Determina si el valor se debe poner sin procesado
     *
     * @return bool
     */
    public function setData(int $bit, string $data, bool $bRaw = false): bool
    {
#echo "\n<br>Setting data [{$bit}]: '" . $data . "' [" . strlen($data) . "]";
        // Valida bit proporcionado
        if ($bit > 1 && array_key_exists($bit, $this->DATA_ELEMENT) && !in_array($bit, $this->BLOCKED_DATA_ELEMENT)) {
#echo "\n<br>Primera validación OK [{$bit}]";
            // Verifica si no es raw
            if (!$bRaw) {
                // Valida data
                try {
#echo "\n<br>Validando data [{$bit}]: '" . $data . "' [" . strlen($data) . "]";
                    $this->_validaData($data, $this->DATA_ELEMENT[$bit]);
                } catch(\Exception $e) {
#echo "\n<br>Error al definir el campo [{$bit}]: " . $e->getMessage();
                    throw new \Exception("Error al definir el campo [{$bit}]: " . $e->getMessage());
                }
                // Formatea data
                if (isset($this->DATA_ELEMENT[$bit]['format'])) {
                    $data = $this->_formateaData($data, $this->DATA_ELEMENT[$bit]);
                }
            }
#echo "\n<br>Codificando valor [{$bit}] => '{$data}'";
            // Codifica data
            $this->_packElement($bit, $data, $bRaw);
#echo "\n<br>calculando bitmaps";
            // Calcula bitmaps
            $this->_calculateBitmaps();
#echo "\n<br>bitmaps calculados";
#echo " --> '" . $this->_data[$bit] . "' [" . strlen($this->_data[$bit]) . "] --> {" . $this->_data_encoded[$bit] . "}";
            return true;
        }
        return false;
    }

    /**
     * Obtiene la seccion de elementos de datos del string ISO8586
     *
     * @param int $bit Posición de elemento de datos.
     * @param bool $bEncoded Return encoded data or plain data.
     *
     * @return string
     */
    public function getData(int $bit, bool $bEncoded = false): string
    {
        if ($bEncoded) {
            return $this->_data_encoded[$bit];
        } else {
            return $this->_data[$bit];
        }
    }

    /**
     * Obtiene el arreglo de elementos de datos del ISO8586.
     *
     * @param bool $bEncoded Return encoded data or plain data.
     *
     * @return array
     */
    public function getDataArray(bool $bEncoded = false): array
    {
        if ($bEncoded) {
            return $this->_data_encoded;
        } else {
            return $this->_data;
        }
    }

    /**
     * Obtiene el valor real del elementos de datos del string ISO8586
     *
     * @param int $bit Posición de elemento de datos.
     *
     * @return string
     */
    public function getValue(int $bit): string
    {
        if (isset($this->_data[$bit])) {
            if ($this->DATA_ELEMENT[$bit]['fixed']) {
                return trim($this->_data[$bit]);
            } else {
                return trim(substr($this->_data[$bit], strlen($this->DATA_ELEMENT[$bit]['sizepos'])));
            }
        }
        return '';
    }

    /**
     * Obtiene string ISO8586 completo
     *
     * @param bool $bEncoded Tipo de retorno codificado o sin codificar
     *
     * @return string
     */
    public function getISO(bool $bEncoded = false): string
    {
        if ($bEncoded) {
            return $this->_iso_encoded;
        } else {
            return $this->_iso;
        }
    }

    /**
     * Define string ISO8586 existente
     *
     * @param string $iso String ISO8586.
     *
     * @return void
     */
    public function setISO(string $iso): void
    {
        // Inicializa todos los datos
        $this->_clear();
        $this->_parseIso($iso);
    }

    /**
     * Quita bit seleccionado del elemento de datos
     *
     * @param int $bit Definición de elemento de datos.
     * @param string $data Valor del dato.
     *
     * @return void
     */
    public function removeData(int $bit): void
    {
        if ($bit > 1 && array_key_exists($bit, $this->DATA_ELEMENT) && array_key_exists($bit, $this->_data)) {
            unset($this->_data[$bit]);
            unset($this->_data_encoded[$bit]);
            $this->_calculateBitmaps();
        }
    }

    /**
     * Obtiene la definición de elementos de datos del string ISO8586
     *
     * @param string $sElement Posición de elemento de datos. (opcional)
     *
     * @return array
     */
    public function getHeaderElementDefinition(string $sElement = null): array
    {
        if (empty($sElement)) {
            return $this->HEADER_ELEMENT;
        } else {
            return $this->HEADER_ELEMENT[$sElement];
        }
    }

    /**
     * Obtiene la definición de elementos de datos del string ISO8586
     *
     * @param int $bit Posición de elemento de datos. (opcional)
     *
     * @return array
     */
    public function getDataElementDefinition(int $bit = null): array
    {
        if (is_null($bit)) {
            return $this->DATA_ELEMENT;
        } else {
            return $this->DATA_ELEMENT[$bit];
        }
    }

    /**
     * Obtiene la interpretación del contenido del campo $iBit
     *
     * @param int $bit Posición de elemento de datos.
     * @param string $sContenido (opcional) Contenido a interpretar. Si no se proporciona utiliza el asignado al campo.
     *
     * @return array
     */
    public function getDataElementValidation(int $iBit, string $sContenido = null)
    {
        // Obtiene definición
        $aDataDefinition = $this->getDataElementDefinition($iBit);
        // Obtiene contenido
        if ($sContenido === null) {
            $sContenido = $this->getValue($iBit);
        }
        // Evalua
        return $this->_evaluaData($iBit, $sContenido, $aDataDefinition);
    }

    /**
     * Obtiene la interpretación de todos los valores asignados al ISO
     *
     * @param int $bit Posición de elemento de datos.
     * @param string $sContenido (opcional) Contenido a interpretar. Si no se proporciona utiliza el asignado al campo.
     *
     * @return array
     */
    public function getIsoValidation()
    {
        // Variables
        $aResultado = [];
        // Evalua
        foreach($this->_data as $iKey => $sValue) {
            $aResultado[$iKey] = $this->getDataElementValidation($iKey, $sValue);
        }
        // Regresa resultado
        return $aResultado;
    }


    // }}}
}