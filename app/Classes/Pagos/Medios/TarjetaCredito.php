<?php

namespace App\Classes\Pagos\Medios;

use Jenssegers\Model\Model;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Classes\Pagos\Base\Direccion;

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
        'nombre', // Nombre como aparece en la tarjeta
        'cvv2', // Código de seguridad de la tarjeta cvv2, cvc
        'expiracion_mes', // Mes de expiración
        'expiracion_anio', // Año de expiracion
        'inicio_mes', // Mes de inicio de la tarjeta
        'inicio_anio', // Anio de inicio
        'nombres', // Nombres del tarjetabiente
        'apellido_paterno', // Apellido Paterno del tarjetabiente
        'apellido_materno', // Apellido Materno del tarjetabiente
        'direccion', // Dirección registrada en el medio de pago (objeto Direccion)
    ];

    /*
     * Atributos no asignables en masa
     */
    protected $guarded = [
        'iin', // Issuer Identification Number (bin)
        'marca', // Marca de la tarjeta: visa, mastercard, carnet or american express.
        'pan_hash', // Identificador de la tarjeta
        'created_at', // Fecha de creación del objeto tipo Carbon
        'updated_at', // Fecha de actualización del objeto tipo Carbon
    ];

    /*
     * Atributos escondidos
     */
    protected $hidden = [
        '_pan', // Número de tarjeta (16-19)
        'cvv2', // CVV2 (3-4)
    ];

    /*
     * Atributos mutables a fechas
     */
    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
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

    /**
     * Valida y formatea año a dos dígitos
     *
     * @param string $sAnio
     *
     * @return void
     */
    public function formateaAnio(string $sAnio) {
        // Valida año
        $iAnioLength = strlen($sAnio);
        if ($iAnioLength < 2 || $iAnioLength > 4) {
            throw new Exception('Año inválido: ' . $sAnio, 400);
        }
        // Formatea año
        if (strlen($sAnio) > 2 ) {
            // Valida rango
            if ($sAnio < 1920 || $sAnio > 2180) {
                throw new Exception('Año inválido: ' . $sAnio, 400);
            }
            return substr($sAnio, -2);
        } else {
            return str_pad($sAnio, 2, "0", STR_PAD_LEFT);
        }
    }

    /**
     * Valida y formatea mes a dos dígitos
     *
     * @param string $sMes
     *
     * @return void
     */
    public function formateaMes(string $sMes) {
        // Valida mes
        $iMesLength = strlen($sMes);
        if ($iMesLength < 1 || $iMesLength > 2 || $sMes < 1 || $sMes > 12) {
            throw new Exception('Mes inválido.', 400);
        }
        // Formatea mes
        return str_pad($sMes, 2, "0", STR_PAD_LEFT);
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
    public function setPanAttribute(string $sPan) {
        // Prepara PAN
        $iPan = preg_replace('/\D/', '', $sPan);
        // Valida tarjeta
        $iPanLength = strlen($iPan);
        if ($iPanLength < self::CARD_MIN_LENGTH || $iPanLength > self::CARD_MAX_LENGTH) {
            throw new Exception('Número de tarjeta inválida.', 400);
        }
        if (!$this->esLuhnValido($iPan)) {
            throw new Exception('Número de tarjeta inválida.', 400);
        }
        // Asigna Issuer Identification Number
        $this->attributes['iin'] = substr($iPan, 0, 6);
        // Asigna versión sanitizada
        $this->attributes['pan'] = $this->attributes['iin'] . str_repeat('*', ($iPanLength - 10)) . substr($iPan, -4);
        // Asigna hash/fingerprint
        $this->attributes['pan_hash'] = Hash::make($iPan);
        // Asigna pan
        $this->attributes['_pan'] = $iPan;
        // Define marca
        $this->attributes['marca'] = $this->defineMarca($iPan);
    }

    /**
     * Define el año de inicio
     *
     * @param string $sAnio
     *
     * @return void
     */
    public function setInicioAnioAttribute(string $sAnio) {
        // Valida y formatea
        $this->attributes['inicio_anio'] = $this->formateaAnio($sAnio);
    }

    /**
     * Define el año de expiración
     *
     * @param string $sAnio
     *
     * @return void
     */
    public function setExpiracionAnioAttribute(string $sAnio) {
        // Valida y formatea
        $this->attributes['expiracion_anio'] = $this->formateaAnio($sAnio);
    }

    /**
     * Define el mes de inicio
     *
     * @param string $sMes
     *
     * @return void
     */
    public function setInicioMesAttribute(string $sMes) {
        // Valida y formatea
        $this->attributes['inicio_mes'] = $this->formateaMes($sMes);
    }

    /**
     * Define el mes de expiración
     *
     * @param string $sMes
     *
     * @return void
     */
    public function setExpiracionMesAttribute(string $sMes) {
        // Valida y formatea
        $this->attributes['expiracion_mes'] = $this->formateaMes($sMes);
    }

    /*
     * Atributos de clases
     */
    public function setDireccionAttribute(Direccion $oDireccion): void
    {
        $this->attributes['direccion'] = $oDireccion;
    }

    // }}}
}