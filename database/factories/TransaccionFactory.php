<?php
//Transaccion model factories

$factory->define(App\Models\Transaccion::class, function (Faker\Generator $faker){
    return [
        'uuid' => $faker->uuid,
        'comercio' => $faker->company,
        'pais_id' => $faker->countryISOAlpha3,
        'prueba' => $faker->boolean,
        'operacion' => $faker->randomElement(['pago', 'preautorizacion', 'autorizacion', 'cancelacion']),
        'estatus' => $faker->randomElement(['pendiente', 'completada', 'reembolsada','reembolso parcial','autorizada',
            'cancelada', 'rechazada-banco', 'rechazada-antifraude', 'contracargo-pendiente', 'contracargo-rechazado',
            'contracargada', 'fallida']),
        'moneda' => $faker->countryISOAlpha3,
        'monto' => $faker->numberBetween(4,19),
        'forma_pago' => $faker->randomElement(['tarjeta', 'telmex-recibo', 'telcel-recibo', 'paypal', 'applepay',
            'androidpay', 'visa-chekout', 'masterpass']),
        'datos_pago' => $faker->randomElement(['{}']),
        'datos_antifraude' => $faker->randomElement(['{}']),
        'comercio_orden_id' => $faker->randomNumber(),
        'datos_comercio' => $faker->randomElement(['{}']),
        'datos_claropagos' => $faker->randomElement(['{}']),
        'datos_procesador' => $faker->randomElement(['{}']),
        'datos_destino' => $faker->randomElement(['{}']),
    ];
});
