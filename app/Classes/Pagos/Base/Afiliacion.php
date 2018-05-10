<?php

namespace App\Classes\Pagos\Base;

use Jenssegers\Model\Model;
use Exception;
use Carbon\Carbon;

/**
 * Clase para direcciones
 *
 */
class Afiliacion extends Model
{

    // {{{ properties

    /*
     * @var array $fillable Atributos asignables
     */
    protected $fillable = [
        'nombre', // Nombre descriptivo de la afiliación
        'afiliacion', // Id de afiliación
        'banco', // Identificador de banco
        'procesador', // Procesador de pagos
        // Datos para procesadores en comun
        'country_code', // "484"
        // Datos para procesador BBVA
        // Datos para procesador Prosa
        'prosa_merchant_id', // "0000000000000012";
        'prosa_req_iv', // "b35d01d060a5799cf0777a084437fa16";
        'prosa_req_key', // "7f24e5aa156cc44ae90f4dda9b3e04f1";
        'prosa_req_sign_iv', // "a77a225cf5b51821c709a13eb923208e";
        'prosa_req_sign_key', // "f334a13790a9cf8e38a3cfd7962e7b2e";
        'prosa_rsp_iv', // "15d3e5bccbafdd42e2d4b092d198019a";
        'prosa_rsp_key', // "95df48f17abcbdac56c9a74863eb8acf";
        'prosa_rsp_sign_iv', // "251bbca2b91e954e133385ec2eef035d";
        'prosa_rsp_sign_key', // "2da544cd92b462acf8f8c91ee8d5fa6a";
        'prosa_user', // "b35d01d060a5799cf0777a084437fa16";
        'prosa_pass', // "7f24e5aa156cc44ae90f4dda9b3e04f1";
        // Datos para procesador Amex
        'amex_api_url', // "https://qwww318.americanexpress.com/IPPayments/inter/CardAuthorization.do"
        'amex_origin', // "AMERICAMOVIL-28705"
        'amex_region', // "LAC";
        'amex_rtind', // "050";
    ];

    /*
     * Atributos no asignables en masa
     */
    protected $guarded = [
        'created_at', // Fecha de creación del objeto tipo Carbon
        'updated_at', // Fecha de actualización del objeto tipo Carbon
    ];

    /*
     * Atributos escondidos
     */
    //protected $hidden = [];

    /*
     * Atributos mutables
     */
    //protected $casts = [];

    /*
     * @var array $dates Atributos mutables a fechas
     */
    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    /*
     * @var array $rules Reglas de validación
     */
    protected $rules = [
    ];

    // }}}

    /*
     * Accessor & Mutators
     */


}