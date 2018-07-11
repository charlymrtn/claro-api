<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UserTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Inserta valores iniciales
        User::create([
            'id' => 1,
            'name' => 'Claro Pagos API User',
            'descripcion' => 'Claro Pagos API User',
            'email' => 'superadmin@claropagos.com',
            'password' => '$2y$10$wxS/Nr5p8B./LXTlXbot.u7CVXIT4JA4EKW/unxfg2Lk7e1h/fb8a',
            'comercio_uuid' => '176f76a8-2670-4288-9800-1dd5f031a57e',
            'comercio_nombre' => 'Claro Pagos',
            'config' => [
                'afiliaciones' => [
                    [
                        'nombre' => 'Afiliacion Prosa',
                        'afiliacion' => '5462742',
                        'banco' => 'inbursa',
                        'procesador' => 'prosa',
                        'country_code' => '484',
                        'prosa_merchant_id' => '0000000000000012',
                        'prosa_req_iv' => 'b35d01d060a5799cf0777a084437fa16',
                        'prosa_req_key' => '7f24e5aa156cc44ae90f4dda9b3e04f1',
                        'prosa_req_sign_iv' => 'a77a225cf5b51821c709a13eb923208e',
                        'prosa_req_sign_key' => 'f334a13790a9cf8e38a3cfd7962e7b2e',
                        'prosa_rsp_iv' => '15d3e5bccbafdd42e2d4b092d198019a',
                        'prosa_rsp_key' => '95df48f17abcbdac56c9a74863eb8acf',
                        'prosa_rsp_sign_iv' => '251bbca2b91e954e133385ec2eef035d',
                        'prosa_rsp_sign_key' => '2da544cd92b462acf8f8c91ee8d5fa6a',
                        'prosa_user' => 'b35d01d060a5799cf0777a084437fa16',
                        'prosa_pass' => '7f24e5aa156cc44ae90f4dda9b3e04f1',
                    ],[
                        'nombre' => 'Afiliacion BBVA',
                        'afiliacion' => '5462742',
                        'banco' => 'bbva',
                        'procesador' => 'eglobal',
                        'country_code' => '484',
                    ],[
                        'nombre' => 'Afiliacion Amex',
                        'afiliacion' => '1354722167',
                        'banco' => 'amex',
                        'procesador' => 'amex',
                        'country_code' => '484',
                        'amex_api_url' => 'https://qwww318.americanexpress.com/IPPayments/inter/CardAuthorization.do',
                        'amex_origin' => 'AMERICAMOVIL-28705',
                        'amex_region' => 'LAC',
                        'amex_rtind' => '050',
                    ]
                ],
            ],
        ]);
    }
}
