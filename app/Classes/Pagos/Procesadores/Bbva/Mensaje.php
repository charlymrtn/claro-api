<?php

namespace App\Classes\Pagos\Procesadores\Bbva;

use App\Classes\Pagos\Procesadores\Iso\iso8583_1993;

class Mensaje extends iso8583_1993
{

    // {{{ properties

    /**
     * @var array Tamaño máximo permitido por campo en bytes.
     */
    const MAX_DATA_ELEMENT_SIZE = 290;

    /**
     * @var array Elementos de header diferentes al iso8583_1993
     */
    protected $CUSTOM_HEADER_ELEMENT = [
        'MTI' =>    ['encoding' => 'hex', 'charset' => 'EBCDIC'],
        'BITMAP' => ['encoding' => 'hex'],
    ];

    /**
     * @var array Elementos de datos diferentes al iso8583_1993
     */
    private $CUSTOM_DATA_ELEMENT = [
        //1 =>   ['usage' => 'Bit Map Secondary', 'mandatory' => true],
        2 =>   ['type' => 'n',   'size' => 21,  'fixed' => false, 'usage' => 'Primary account number (PAN)', 'encoding' => 'hex', 'charset' => 'EBCDIC'],
        3 =>   ['encoding' => 'hex', 'charset' => 'EBCDIC'],
        11 =>  ['type' => 'ans', 'size' => 6,   'fixed' => true,  'usage' => 'Systems trace audit number', 'text' => 'uppercase'],
        22 =>  ['text' => 'uppercase'],
        31 =>  ['type' => 'ans', 'size' => 50,  'fixed' => false, 'usage' => 'Acquirer reference data'],
        32 =>  ['type' => 'n',   'size' => 13,  'fixed' => false, 'usage' => 'Acquiring institution identification code'],
        33 =>  ['type' => 'n',   'size' => 13,  'fixed' => false, 'usage' => 'Forwarding institution identification code'],
        34 =>  ['type' => 'n',   'size' => 30,  'fixed' => false, 'usage' => 'Primary account number, extended'],
        35 =>  ['type' => 'ans', 'size' => 39,  'fixed' => false, 'usage' => 'Track 2 data'],
        36 =>  ['type' => 'ns',  'size' => 107, 'fixed' => false, 'usage' => 'Track 3 data'],
        37 =>  ['type' => 'ans', 'size' => 12,  'fixed' => true,  'usage' => 'Retrieval reference number'],
        38 =>  ['type' => 'anp', 'size' => 6,   'fixed' => true,  'usage' => 'Approval code'],
        40 =>  ['type' => 'n',   'size' => 3,   'fixed' => true,  'usage' => 'Service code'],
        42 =>  ['justify' => 'left'],
        43 =>  ['type' => 'ans', 'size' => 101, 'fixed' => false, 'usage' => 'Card acceptor name/location'],
        44 =>  ['type' => 'ans', 'size' => 27,  'fixed' => false, 'usage' => 'Additional response data'],
        45 =>  ['type' => 'ans', 'size' => 78,  'fixed' => false, 'usage' => 'Track 1 Data'],
        46 =>  ['type' => 'an',  'size' => 207, 'fixed' => false, 'usage' => 'Amounts, fees'],
        47 =>  ['type' => 'ans', 'size' => 304, 'fixed' => false, 'usage' => 'Additional data - National'],
        48 =>  ['type' => 'ans', 'size' => 43,  'fixed' => false, 'usage' => 'Additional data - Private'],
        49 =>  ['type' => 'n',   'size' => 3,   'fixed' => true,  'usage' => 'Currency code, transaction'],
        50 =>  ['type' => 'an',  'size' => 3,   'fixed' => true,  'usage' => 'Currency code, reconciliation'],
        51 =>  ['type' => 'an',  'size' => 3,   'fixed' => true,  'usage' => 'Currency code, cardholder billing'],
        53 =>  ['type' => 'an',  'size' => 10,  'fixed' => false, 'usage' => 'Security related control information'],
        54 =>  ['type' => 'ans', 'size' => 123, 'fixed' => false, 'usage' => 'Additional amounts'],
        55 =>  ['type' => 'ans', 'size' => 259, 'fixed' => false, 'usage' => 'Integrated Circuit Card System Related Data'],
        56 =>  ['type' => 'ans',   'size' => 37,  'fixed' => false, 'usage' => 'Original Data elements'],
        58 =>  ['type' => 'n',   'size' => 13,  'fixed' => false, 'usage' => 'Authorizing Agent Institution Identification Code'],
        59 =>  ['type' => 'ans', 'size' => 1002,'fixed' => false, 'usage' => 'Transport Data'],
        60 =>  ['type' => 'ans', 'size' => 106, 'fixed' => false, 'usage' => 'National Use Data'],
        61 =>  ['type' => 'ans', 'size' => 103, 'fixed' => false, 'usage' => 'National Use Data'],
        62 =>  ['type' => 'ans', 'size' => 63,  'fixed' => false, 'usage' => 'Reserved Private'],
        63 =>  ['type' => 'ans', 'size' => 208, 'fixed' => false, 'usage' => 'Reserved Private', 'encoding' => 'hex', 'charset' => 'EBCDIC'],
        66 =>  ['type' => 'ans', 'size' => 204, 'fixed' => false, 'usage' => 'Amounts, Original Fees'],
        97 =>  ['type' => 'an',  'size' => 16,  'fixed' => true,  'usage' => 'Amount, net settlement'],
    ];

    /**
     * @var array Lista de elementos bloqueados para mensajes GHDC
     */
    protected $BLOCKED_DATA_ELEMENT = [
        5, 6, 8, 9,
        10, 16, 17, 18,
        20, 21, 23, 29,
        36,
        40, 46,
        50, 51, 57, 58, 59,
        64, 65, 66, 67, 68, 69,
        70, 71, 72, 73, 74, 75, 76, 77, 78, 79,
        80, 81, 82, 83, 84, 85, 86, 87, 88, 89,
        90, 91, 92, 93, 94, 95, 97, 98, 99,
        100, 101, 102, 103, 104, 105, 106, 107, 108, 109,
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
     * Campo 47
     * @todo: Cambiar arreglo por objeto request de datos
     */
    public function formatAdditionalDataNational(array $aData): string
    {
        // Primary ID (AX) 'AX'
        $sResultado = 'AX';
        // Secondary Id (ITD) 'ITD'
        $sResultado .= $aData['secondary_id'];
        // 1 Customer Email 'CE '
        $sResultado .= 'CE ';
        // LLVAR Customer Email length '24'
        $sResultado .= sprintf("%02s", strlen($aData['cliente']['email']));
         // 2 Customer Email 'CFFROST@EMAILADDRESS.COM'
        $sResultado .= $aData['cliente']['email'];
        // 3 Customer Hostname 'CH '
        $sResultado .= 'CH ';
        // LLVAR Customer Hostname length '14'
        $sResultado .= sprintf("%02s", strlen($aData['cliente']['hostname']));
         // 4 Customer Hostname 'PHX.QW.AOL.COM'
        $sResultado .= $aData['cliente']['hostname'];
         // 5 HTTP Browser type id 'HBT'
        $sResultado .= 'HBT';
        // LLVAR Browser type length '46'
        $sResultado .= sprintf("%02s", strlen($aData['cliente']['browser']));
         // 6 HTTP Browser type 'MOZILLA/4.0 (COMPATIBLE; MSIE 5.0; WINDOWS 95)'
        $sResultado .= $aData['cliente']['browser'];
         // 7 Ship to country id 'STC'
        $sResultado .= 'STC';
        // LLVAR Browser type length '03'
        $sResultado .= '03';
        // 8 HTTP Browser type '840'
        $sResultado .= sprintf("%03s", $aData['direccion_envio']['pais_n3']);
        // 9 Shipping method id 'SM '
        $sResultado .= 'SM ';
        // LLVAR Browser type length '02'
        $sResultado .= '02';
         // 10 Shipping method '02'
        $sResultado .= $aData['direccion_envio']['envio_tipo'];
         // 11 merchant product sku id 'MPS'
        $sResultado .= 'MPS';
         // LLVAR sku length '08'
        $sResultado .= sprintf("%02s", strlen($aData['producto']['sku']));
         // 12 merchant product sku  'TKDC315U'
        $sResultado .= $aData['producto']['sku'];
         // 13 Costumer IP '127.142.005.056'
        $sResultado .= sprintf("%-15s", $aData['cliente']['ip']);
         // 14 Costumer ANI (Telefono) '6025551212'
        $sResultado .= sprintf("%-10s", $aData['cliente']['telefono']);
         // 15 Costumer II digits (Telefono prefijo) '00'
        $sResultado .= sprintf("%02s", $aData['cliente']['prefijo']);

        return $sResultado;
    }

    /**
     * Campo 48 - Additional Data Private (Parcialidades)
     * @todo: Cambiar arreglo por objeto request de datos
     */
    public function formatAdditionalDataPrivate(array $aData): string
    {
        // Plan Type ['03', '05']
        $sResultado = $aData['plan_pago'];
        // Num of installments
        $sResultado .= sprintf("%02s", $aData['parcialidades']);

        return $sResultado;
    }

    /**
     * Campo 63
     * @todo: Cambiar arreglo por objeto request de datos
     */
    public function formatPrivateUseData(array $aData): string
    {
        // Service ID (AX)                  "AX"
        $sResultado = 'AX';
        // ReqType Id (AD)                  "AD"
        $sResultado .= $aData['req_type_id'];

        // Direccion de pago CP             "850544500"
        $sResultado .= sprintf("%- 9s", $aData['direccion']['cp']);
        // Direccion de pago Address        "18850 N 56 ST #301  "
        $sAddress =  $aData['direccion']['linea1'];
        if (!empty($aData['direccion']['linea2'])) {
            $sAddress .= ' ' . $aData['direccion']['linea2'];
        }
        $sResultado .= sprintf("%- 20s", $sAddress);
        // Direccion de pago First Name     "JANE           "
        $sResultado .= sprintf("%- 15s", $aData['direccion']['nombre']);
        // Direccion de pago Last Name     "SMITH                         "
        $sLastName =  $aData['direccion']['apellido_paterno'];
        if (!empty($aData['direccion']['apellido_materno'])) {
            $sLastName .= ' ' . $aData['direccion']['apellido_materno'];
        }
        $sResultado .= sprintf("%- 30s", $sLastName);
        // Direccion de pago Tel        "1234567890"
        $sResultado .= $aData['direccion']['telefono'];

        // Direccion de envio CP             "850221800"
        $sResultado .= sprintf("%- 9s", $aData['direccion_envio']['cp']);
        // Direccion de envio Address        "4102 N 289 PL                                     "
        $sAddress =  $aData['direccion_envio']['linea1'];
        if (!empty($aData['direccion_envio']['linea2'])) {
            $sAddress .= ' ' . $aData['direccion_envio']['linea2'];
        }
        $sResultado .= sprintf("%- 50s", $sAddress);
        // Direccion de envio First Name     "ROBERT         "
        $sResultado .= sprintf("%- 15s", $aData['direccion_envio']['nombre']);
        // Direccion de envio Last Name     "JONES                         "
        $sLastName =  $aData['direccion_envio']['apellido_paterno'];
        if (!empty($aData['direccion_envio']['apellido_materno'])) {
            $sLastName .= ' ' . $aData['direccion_envio']['apellido_materno'];
        }
        $sResultado .= sprintf("%- 30s", $sLastName);
        // Direccion de envio Tel        "5555370000"
        $sResultado .= sprintf("%-10s", $aData['direccion_envio']['telefono']);
        // Direccion de envio Pais        "484"
        $sResultado .= $aData['direccion_envio']['pais_n3'];

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
        // No permitidos: ( -> 40, [ -> 91, | -> 124
        //$aChars = array_merge(range(32, 39), range(42, 90), range(92, 96), [123, 125, 126]);
        $aChars = array_merge(range(48, 57), range(65, 90));
        $iCharsLength = count($aChars);
        $sSTAN = '';
        for ($i = 0; $i < $iLength; $i++) {
            $sSTAN .= chr($aChars[mt_rand(0, $iCharsLength - 1)]);
        }
        return $sSTAN;
    }

    // }}}
}