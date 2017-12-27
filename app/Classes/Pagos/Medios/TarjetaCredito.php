<?php

namespace App\Classes\Pagos\Medios;

use Jenssegers\Model\Model;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Clase de forma de pago de tarjeta de crédito
 *
 */
class TarjetaCredito extends Model
{

    // {{{ properties

    const CARD_MIN_LENGTH = 13;
    const CARD_MAX_LENGTH = 19;

    /*
     * @var array $fillable Atributos asignables
     */
    protected $fillable = [

        'pan', // Número de tarjeta sanitizado

        'nombre_tarjeta', // Nombre como aparece en la tarjeta

        'nombres', // Nombres del tarjetabiente
        'apellido_paterno', // Apellido Paterno del tarjetabiente
        'apellido_materno', // Apellido Materno del tarjetabiente

        'cvv', // Código de seguridad de la tarjeta
        'mes_expiracion', // Mes de expiración
        'anio_expiracion', // Año de expiracion

        'mes_inicio', // Mes de inicio de la tarjeta
        'anio_inicio', // Anio de inicio

        /**
        address	object, Billing address of cardholder.
        bank_name	string, Name of the issuing bank.
        bank_code	string, Code of the issuing bank.
        customer_id	string, Customer identifier to which the card belongs. If the card is at Merchant level this value is null.

        Address
            line1	string (required), The first line is the card owner address. It’s commonly used to indicate street address and number.
            line2	string, Second addres line, commonly use to indicate interior number, suite number or county.
            line3	string, Third address line, commonly use to to indicate the neighborhood.
            postal_code	string (required), Zip code
            state	string (required), State
            city	string (required), City
            country
            country_code	string (required), Country code, in the two character format: ISO_3166-1.

        title
         * * company
         * * phone
         * * phoneExtension
         * * fax
         * * tracks
         * * issueNumber
         * * billingTitle
         * * billingName
         * * billingFirstName
         * * billingLastName
         * * billingCompany
         * * billingAddress1
         * * billingAddress2
         * * billingCity
         * * billingPostcode
         * * billingState
         * * billingCountry
         * * billingPhone
         * * billingFax
         * * shippingTitle
         * * shippingName
         * * shippingFirstName
         * * shippingLastName
         * * shippingCompany
         * * shippingAddress1
         * * shippingAddress2
         * * shippingCity
         * * shippingPostcode
         * * shippingState
         * * shippingCountry
         * * shippingPhone
         * * shippingFax
         * * email
         * * birthday
         * * gender
         */
    ];

    /*
     * Atributos no asignables en masa
     */
    protected $guarded = [
        'iin', // Issuer Identification Number (bin)
        'marca', // Marca de la tarjeta: visa, mastercard, carnet or american express.
        'pan_hash', // Identificador de la tarjeta
        'created_at', // Fecha de creación del objeto tipo Carbon
    ];

    /*
     * Atributos escondidos
     */
    protected $hidden = [
        '_pan', // Número de tarjeta (16-19)
    ];

    // }}}}

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

    /**
     * Obtiene la marca de la tarjeta de crédito ingresada
     *
     * @param int $iPan
     *
     * @return string
     */
    private function defineMarca(int $iPan): string
    {
        // Define marcas y parametros para distinguirla
        $aCardBinRegex = array(
            'visa' => '/^4\d{12}(\d{3})?$/',
            'mastercard' => '/^(5[1-5]\d{4}|677189)\d{10}$|^2(?:2(?:2[1-9]|[3-9]\d)|[3-6]\d\d|7(?:[01]\d|20))\d{12}$/',
            'discover' => '/^(6011|65\d{2}|64[4-9]\d)\d{12}|(62\d{14})$/',
            'amex' => '/^3[47]\d{13}$/',
            'diners_club' => '/^3(0[0-5]|[68]\d)\d{11}$/',
            'jcb' => '/^35(28|29|[3-8]\d)\d{12}$/',
            'switch' => '/^6759\d{12}(\d{2,3})?$/',
            'solo' => '/^6767\d{12}(\d{2,3})?$/',
            'dankort' => '/^5019\d{12}$/',
            'maestro' => '/^(5[06-8]|6\d)\d{10,17}$/',
            'forbrugsforeningen' => '/^600722\d{10}$/',
            'laser' => '/^(6304|6706|6709|6771(?!89))\d{8}(\d{4}|\d{6,7})?$/',
        );
        foreach ($aCardBinRegex as $sMarca => $sRegex) {
            if (preg_match($sRegex, $iPan)) {
                return $sMarca;
            }
        }
    }


    /**
     * Valida la tarjeta contra el algoritmo Luhn
     *
     * @param int $iPan
     *
     * @return bool
     */
    private function esLuhnValido(int $iPan): bool
    {
        $sChecksum = '';

        foreach (str_split(strrev((string) $iPan)) as $i => $d) {
            $sChecksum .= $i % 2 !== 0 ? $d * 2 : $d;
        }

        return array_sum(str_split($sChecksum)) % 10 === 0;
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
    public function __construct($aAttributes)
    {
        // Define fecha de creación
        $this->attributes['created_at'] = Carbon::now();
        // Ejecuta constructor padre
        parent::__construct($aAttributes);
    }

    /**
     * Define el pan (numero) de tarjeta
     *
     * @param string $sPan
     *
     * @return void
     */
    public function setPanAttribute($sPan) {
        // Prepara PAN
        $iPan = preg_replace('/\D/', '', $sPan);
        // Valida tarjeta
        $iPanLength = strlen($iPan);
        if ($iPanLength < self::CARD_MIN_LENGTH || $iPanLength > self::CARD_MAX_LENGTH) {
            throw new Exception('Número de tarjeta inválida.');
        }
        if (!$this->esLuhnValido($iPan)) {
            throw new Exception('Número de tarjeta inválida.');
        }
        // Asigna Issuer Identification Number
        $this->attributes['iin'] = substr($iPan, 0, 6);
        // Asigna versión sanitizada
        $this->attributes['pan'] = $this->attributes['iin'] . str_repeat('*', ($iPanLength - 10)) . substr($iPan, -4);
        // Asigna hash/fingerprint
        $this->attributes['pan_hash'] = hash('sha256', $iPan);
        // Asigna pan
        $this->attributes['_pan'] = $iPan;
        // Define marca
        $this->attributes['marca'] = $this->defineMarca($iPan);
    }

    // }}}
}