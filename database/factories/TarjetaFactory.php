<?php

use Faker\Generator as Faker;
use App\Models\Medios\Tarjeta;
use App\Classes\Pagos\Base\Direccion;
use App\Classes\Pagos\Base\Telefono;

$factory->define(Tarjeta::class, function (Faker $faker) {
    // Obtiene datos para no generar objetos extra

    // Cliente
    $aClienteIds = \App\Models\Cliente::select('uuid')->get()->toArray();
    if (empty($aClienteIds)) {
        $aClienteId = factory(App\Models\Cliente::class)->create()->uuid;
    } else {
        $aClienteId = $faker->randomElement($aClienteIds)["uuid"];
    }

    return [
        'uuid' => $faker->uuid,
        'iin' =>  $faker->word,
        'marca' => $faker->creditCardType,
        'pan' => $faker->isbn10,
        'terminacion' => $faker->numberBetween($min = 1000, $max = 9000),
        'nombre' => $faker->streetName,
        'expiracion_mes' => $faker->numberBetween($min = 10, $max = 99),
        'expiracion_anio' =>$faker->numberBetween($min = 10, $max = 99),
        'comercio_uuid' => $faker->uuid,
        'cliente_uuid'  => $aClienteId,
        'inicio_mes' => $faker->numberBetween($min = 10, $max = 99),
        'inicio_anio' => $faker->numberBetween($min = 10, $max = 99),
        'pan_hash' => $faker->numberBetween($min = 10, $max = 99) ,
        'token' => $faker->ean13,
        'default' => $faker->boolean,
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
        'created_at' => $faker->dateTimeBetween('-10 days', '-5 days')->format('Y-m-d H:i:s'),
        'updated_at' => $faker->dateTimeBetween('-5 days')->format('Y-m-d H:i:s'),
    ];

});
