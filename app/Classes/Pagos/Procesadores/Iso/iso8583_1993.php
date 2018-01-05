<?php

namespace App\Classes\Pagos\Procesadores\Iso;

use App\Classes\Pagos\Procesadores\Iso\iso8583_1987;

/*
 * Clase para manejo de mensajes ISO-8586
 *
 * Definición: Documento E-Global Anexo A - ISO8583
 * Definición: https://es.wikipedia.org/wiki/ISO_8583
 * Basado en JAK8583 v0.7 por Jimmi Kembaren 2009.
 *
 * @package Procesadores
 */

class iso8583_1993 extends iso8583_1987
{
    // {{{ properties

    /**
     * @var array Elementos de header diferentes al iso8583_1987
     */
    protected $CUSTOM_HEADER_ELEMENT = [];

    /**
     * @var array Elementos de datos diferentes al iso8583_1987
     */
    private $CUSTOM_DATA_ELEMENT = [
        12 =>  ['size' => 12],
        15 =>  ['size' => 6],
        22 =>  ['type' => 'an', 'size' => 12, 'usage' => 'Point of service data code'],
        25 =>  ['size' => 4, 'usage' => 'Message reason code'],
        26 =>  ['size' => 4, 'usage' => 'Card acceptor business code'],
        28 =>  ['type' => 'n', 'size' => 6, 'usage' => 'Date, reconciliation'],
        29 =>  ['type' => 'n', 'size' => 3, 'usage' => 'Reconciliation indicator'],
        30 =>  ['type' => 'n', 'size' => 24, 'usage' => 'Amounts, original'],
        31 =>  ['type' => 'ans', 'size' => 99, 'fixed' => false, 'sizepos' => 'LL',  'usage' => 'Acquirer reference data'],
        34 =>  ['type' => 'ns'],
        37 =>  ['type' => 'anp'],
        38 =>  ['type' => 'anp'],
        39 =>  ['type' => 'n', 'size' => 3, 'usage' => 'Action code'],
        40 =>  ['type' => 'n'],
        43 =>  ['size' => 99, 'fixed' => false, 'sizepos' => 'LL'],
        44 =>  ['size' => 99],
        45 =>  ['size' => 76],
        46 =>  ['size' => 204, 'usage' => 'Amounts, fees'],
        49 =>  ['type' => 'ans', 'size' => 3,   'fixed' => true,  'usage' => 'Currency code, transaction'],
        50 =>  ['type' => 'ans', 'size' => 3,   'fixed' => true,  'usage' => 'Currency code, reconciliation'],
        51 =>  ['type' => 'ans', 'size' => 3,   'fixed' => true,  'usage' => 'Currency code, cardholder billing'],
        53 =>  ['type' => 'b',   'size' => 48,  'fixed' => false, 'sizepos' => 'LL', 'usage' => 'Security related control information'],
        54 =>  ['type' => 'ans', 'size' => 120, 'fixed' => false, 'usage' => 'Additional amounts'],
        55 =>  ['type' => 'b',   'size' => 255, 'fixed' => false, 'usage' => 'Integrated Circuit Card System Related Data'],
        56 =>  ['type' => 'n',   'size' => 35, 'sizepos' => 'LL', 'usage' => 'Original Data elements'],
        57 =>  ['type' => 'n',   'size' => 3,   'fixed' => true,  'usage' => 'Authorization Life Cycle Code'],
        58 =>  ['type' => 'n',   'size' => 11, 'sizepos' => 'LL', 'usage' => 'Authorizing Agent Institution Identification Code'],
        59 =>  ['usage' => 'Transport Data'],
        60 =>  ['size' => 999, 'sizepos' => 'LLL', 'usage' => 'Reserved For National Use'],
        66 =>  ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'usage' => 'Amounts, Original Fees'],
        71 =>  ['size' => 8,   'fixed' => true],
        72 =>  ['type' => 'ans', 'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'usage' => 'Data record (ISO 8583:1993)'],
        82 =>  ['type' => 'n',   'size' => 10,  'fixed' => true,  'usage' => 'Inquiries, reversal number'],
        83 =>  ['type' => 'n',   'size' => 10,  'fixed' => true,  'usage' => 'Payments number'],
        84 =>  ['type' => 'n',   'size' => 10,  'fixed' => true,  'usage' => 'Payments, reversal number'],
        85 =>  ['type' => 'n',   'size' => 10,  'fixed' => true,  'usage' => 'Fee Collections, number'],
        90 =>  ['type' => 'n',   'size' => 10,  'fixed' => true,  'usage' => 'Authorizations, Reversal Number'],
        91 =>  ['type' => 'n',   'size' => 3,   'fixed' => true,  'usage' => 'COUNTRY Code, Transaction Destination Institution'],
        92 =>  ['type' => 'n',   'size' => 3,   'fixed' => true,  'usage' => 'COUNTRY Code, Transaction Originator Institution'],
        93 =>  ['type' => 'n',   'size' => 11,  'fixed' => false, 'sizepos' => 'LL', 'usage' => 'Transaction Destination Institution Identification Code'],
        94 =>  ['type' => 'n',   'size' => 11,  'fixed' => false, 'sizepos' => 'LL', 'usage' => 'Transaction Originator Institution Identification Code'],
        95 =>  ['type' => 'ans', 'size' => 99,  'fixed' => false, 'sizepos' => 'LL', 'usage' => 'CARD ISSUER REFERENCE DATA'],
        96 =>  ['type' => 'b',   'size' => 999, 'fixed' => false, 'sizepos' => 'LLL', 'usage' => 'KEY MANAGEMENT DATA'],
        97 =>  ['type' => 'an',  'size' => 17,  'fixed' => true,  'usage' => 'Amount, net settlement'],
        99 =>  ['type' => 'an'],
        102 => ['type' => 'ans'],
        103 => ['type' => 'ans'],
        105 => ['type' => 'n', 'size' => 16,  'fixed' => true,  'usage' => 'Credits Chargeback Amount'],
        106 => ['type' => 'n', 'size' => 16,  'fixed' => true,  'usage' => 'Debits Chargeback Amount'],
        107 => ['type' => 'n', 'size' => 10,  'fixed' => true,  'usage' => 'Credits Chargeback Number'],
        108 => ['type' => 'n', 'size' => 10,  'fixed' => true,  'usage' => 'Debits Chargeback Number'],
        109 => ['size' => 84,  'fixed' => false, 'sizepos' => 'LL', 'usage' => 'Reserved for ISO use'],
        110 => ['size' => 84,  'fixed' => false, 'sizepos' => 'LL', 'usage' => 'Reserved for ISO use'],
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

    // }}}
}