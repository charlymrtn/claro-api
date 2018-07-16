<?php

// Estado model factories
$factory->define(App\Models\Cliente::class, function (Faker\Generator $faker) {

    // Comercios
    $aComercioIds = \App\Models\User::distinct()->get(['comercio_uuid'])->toArray();
    if (empty($aComercioIds)) {
        $sComercioId = Webpatser\Uuid\Uuid::generate(4)->string;
    } else {
        $sComercioId = $faker->randomElement($aComercioIds)["uuid"];
    }

    return [
        'uuid' => Webpatser\Uuid\Uuid::generate(4)->string,
        // Datos del comercio
        'comercio_uuid' => $sComercioId,
        'id_externo' => $faker->str_random(6),
        'creacion_externa' => $faker->dateTimeBetween('-10 days')->format('Y-m-d H:i:s'),
        // Datos del cliente
        'nombre' => $faker->firstName,
        'apellido_paterno' => $faker->lastName,
        'apellido_materno' => $faker->lastName,
        'sexo' => $faker->randomElement(['masculino', 'femenino']),
        'email' => $faker->freeEmail,
        'nacimiento' => $faker->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d H:i:s'),
        'estado' => $faker->randomElement(['activo', 'suspendido', 'inactivo']),
        // Objetos JSON
        'telefono' => $faker->randomElement(['{}']),
        'direccion' => $faker->randomElement(['{}']),
        // Traits
        'created_at' => $faker->dateTimeBetween('-10 days', '-5 days')->format('Y-m-d H:i:s'),
        'updated_at' => $faker->dateTimeBetween('-5 days')->format('Y-m-d H:i:s'),
    ];
});
