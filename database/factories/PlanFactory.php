<?php

// Estado model factories
$factory->define(App\Models\Suscripciones\Plan::class, function (Faker\Generator $faker) {

    // Comercios
    $aComercioIds = \App\Models\User::distinct()->get(['comercio_uuid'])->toArray();
    if (empty($aComercioIds)) {
        $sComercioId = Webpatser\Uuid\Uuid::generate(4)->string;
    } else {
        $sComercioId = $faker->randomElement($aComercioIds)["comercio_uuid"];
    }

    // Datos fake
    $iMonto = $faker->numberBetween(10, 300) * 10;
    $sTipoPeriodo = $faker->randomElement(['dia', 'semana', 'mes', 'anio']);

    return [
        'uuid' => Webpatser\Uuid\Uuid::generate(4)->string,
        // Datos del comercio
        'comercio_uuid' => $sComercioId,
        // Datos del plan
        'nombre' => 'Plan ' . $iMonto . ' por ' . $sTipoPeriodo,
        'monto' => $iMonto,
        'frecuencia' => $faker->randomElement([1, 2, 3, 6, 9]),
        'tipo_periodo' => $sTipoPeriodo,
        'max_reintentos' => $faker->numberBetween(1, 10),
        'prueba_frecuencia' => $faker->randomElement([1, 2, 3, 6, 9]),
        'prueba_tipo_periodo' => $faker->randomElement(['dia', 'semana', 'mes', 'anio']),
        'estado' => $faker->randomElement(['inactivo', 'activo']),
        'puede_suscribir' => $faker->boolean,
        // Catálogos
        'moneda_iso_a3' => $faker->randomElement(['MXN']),
        // Traits
        'created_at' => $faker->dateTimeBetween('-10 days', '-5 days')->format('Y-m-d H:i:s'),
        'updated_at' => $faker->dateTimeBetween('-5 days')->format('Y-m-d H:i:s'),
    ];
});
