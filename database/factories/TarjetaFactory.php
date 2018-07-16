<?php
use App\Classes\Pagos\Base\Direccion;
use App\Classes\Pagos\Base\Telefono;
// Estado model factories
$factory->define(App\Models\Medios\Tarjeta::class, function (Faker\Generator $faker) {

    // Comercios
    $aComercioIds = \App\Models\User::distinct()->get(['comercio_uuid'])->toArray();
    if (empty($aComercioIds)) {
        $sComercioId = Webpatser\Uuid\Uuid::generate(4)->string;
    } else {
        $sComercioId = $faker->uuid;
    }
    // Cliente
    $aClienteIds = \App\Models\Cliente::select('uuid')->get()->toArray();
    if (empty($aClienteIds)) {
        $sClienteId = Webpatser\Uuid\Uuid::generate(4)->string;
    } else {
        $sClienteId = $faker->randomElement($aClienteIds)["uuid"];
    }

    // Tarjeta
    $oTarjeta = new \App\Classes\Pagos\Medios\TarjetaCredito([
        'pan' => $faker->creditCardNumber,
        'nombres' => $faker->firstName,
        'apellido_paterno' => $faker->lastName,
        'apellido_materno' => $faker->lastName,
        'expiracion_mes' => $faker->month(),
        'expiracion_anio' => $faker->dateTimeBetween('-5 years')->format('Y'),
        'inicio_mes' => $faker->month(),
        'inicio_anio' => $faker->dateTimeBetween('-5 years')->format('Y'),
    ]);
    return [
        'uuid' => $faker->uuid,
        'comercio_uuid' => $sComercioId,
        'cliente_uuid' => $sClienteId,
        // Datos de la tarjeta
        'iin' => $oTarjeta->iin,
        'marca' => $oTarjeta->marca,
        'pan' => $oTarjeta->pan,
        'terminacion' => $oTarjeta->terminacion,
        'nombre' => $oTarjeta->nombres . ' ' . $oTarjeta->apellido_paterno . ' ' . $oTarjeta->apellido_materno,
        'expiracion_mes' => $oTarjeta->expiracion_mes,
        'expiracion_anio' => $oTarjeta->expiracion_anio,
        'inicio_mes' => $oTarjeta->inicio_mes,
        'inicio_anio' => $oTarjeta->inicio_anio,
        // Otros datos
        'pan_hash' => $oTarjeta->pan_hash,
        'token' => Webpatser\Uuid\Uuid::generate(4)->string,
        'default' => $faker->boolean,
        // Objetos JSON
        'direccion' => new Direccion([
            'pais' => 'MEX',
            'estado' => 'MEX',
            'ciudad' => $faker->citySuffix,
            'municipio' => $faker->city,
            'linea1' => $faker->phoneNumber,
            'linea2' => $faker->phoneNumber,
            'linea3' => $faker->phoneNumber,
            'cp' => $faker->postcode,
            'telefono' => new Telefono([
                'tipo' => $faker->randomElement([
                    'casa', 'oficina'
                ]),
                'codigo_pais' => 52,
                'codigo_area' => 55,
                'numero' => $faker->phoneNumber,
                'extension' => $faker->numberBetween($min = 10, $max = 99),
            ]),
        ]),
        // Traits
        'created_at' => $faker->dateTimeBetween('-10 days', '-5 days')->format('Y-m-d H:i:s'),
        'updated_at' => $faker->dateTimeBetween('-5 days')->format('Y-m-d H:i:s'),
    ];
});
