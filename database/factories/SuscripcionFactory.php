<?php

// Estado model factories
$factory->define(App\Models\Suscripciones\Suscripcion::class, function (Faker\Generator $faker) {

    // Plan
    $aPlanIds = \App\Models\Suscripciones\Plan::select('uuid')->get()->toArray();
    if (empty($aPlanIds)) {
        $sPlanId = Webpatser\Uuid\Uuid::generate(4)->string;
        // Comercios
        $aComercioIds = \App\Models\User::distinct()->get(['comercio_uuid'])->toArray();
        if (empty($aComercioIds)) {
            $sComercioId = Webpatser\Uuid\Uuid::generate(4)->string;
        } else {
            $sComercioId = $faker->randomElement($aComercioIds)["uuid"];
        }
    } else {
        $oPlan = $faker->randomElement($aPlanIds);
        $sPlanId = $oPlan->uuid;
        $sComercioId = $oPlan->comercio_uuid;
    }
    // Cliente
    $aClienteIds = \App\Models\Cliente::select('uuid')->where('comercio_uuid', $sComercioId)->get()->toArray();
    if (empty($aClienteIds)) {
        $sClienteId = Webpatser\Uuid\Uuid::generate(4)->string;
    } else {
        $sClienteId = $faker->randomElement($aClienteIds)["uuid"];
    }

    return [
        'uuid' => Webpatser\Uuid\Uuid::generate(4)->string,
        'comercio_uuid' => $sComercioId,
        'plan_uuid' => $sPlanId,
        'cliente_uuid' => $sClienteId,
        'metodo_pago' => $faker->randomElement(['Tarjeta']),
        'metodo_pago_uuid' => Webpatser\Uuid\Uuid::generate(4)->string,
        'estado' => $faker->randomElement(['prueba', 'activa', 'pendiente', 'suspendida', 'cancelada']),
        'inicio' => $faker->dateTime(),
        'fin' => $faker->dateTime('+2 month')->format('Y-m-d H:i:s'),
        'prueba_inicio' => $faker->dateTime(),
        'prueba_fin' => $faker->dateTime('+1 month')->format('Y-m-d H:i:s'),
        'periodo_fecha_inicio' => $faker->dateTime('+1 month')->format('Y-m-d H:i:s'),
        'periodo_fecha_fin' => $faker->dateTime('+2 month')->format('Y-m-d H:i:s'),
        'fecha_proximo_cargo' => $faker->dateTime('+2 month')->format('Y-m-d H:i:s'),
        // Traits
        'created_at' => $faker->dateTimeBetween('-10 days', '-5 days')->format('Y-m-d H:i:s'),
        'updated_at' => $faker->dateTimeBetween('-5 days')->format('Y-m-d H:i:s'),
    ];
});
