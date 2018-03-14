<?php
//Transaccion model factories

$factory->define(App\Models\Transaccion::class, function (Faker\Generator $faker){
    return [
        'uuid' => $faker->uuid,
        'comercio' => $faker->company,
        'pais' => $faker->randomElement(['MEX']),
        'prueba' => $faker->boolean,
        'operacion' => $faker->randomElement([
            'pago', 'pago', 'pago', 'pago',
            'preautorizacion', 'autorizacion', 'cancelacion'
        ]),
        'estatus' => $faker->randomElement([
            'pendiente', 'completada', 'reembolsada','reembolso-parcial','autorizada', 'cancelada',
            'rechazada-banco', 'rechazada-antifraude', 'contracargo-pendiente', 'contracargo-rechazado',
            'contracargada', 'fallida'
        ]),
        'moneda' => $faker->randomElement(['MXN']),
        'monto' => $faker->numberBetween(4,19),
        'forma_pago' => $faker->randomElement([
            'tarjeta', 'telmex-recibo', 'telcel-recibo', 'paypal', 'applepay', 'androidpay', 'visa-chekout', 'masterpass'
        ]),
        'datos_pago' => $faker->randomElement(['{}']),
        'datos_antifraude' => $faker->randomElement(['{}']),
        'comercio_orden_id' => $faker->randomNumber(),
        'datos_comercio' => $faker->randomElement(['{}']),
        'datos_claropagos' => $faker->randomElement(['{}']),
        'datos_procesador' => $faker->randomElement(['{}']),
        'datos_destino' => $faker->randomElement(['{}']),
    ];
});

$factory->state(App\Models\Transaccion::class, 'completada', function (Faker\Generator $faker) {
    return [
        'estatus' => 'completada',
    ];
});
$factory->state(App\Models\Transaccion::class, 'cancelada', function (Faker\Generator $faker) {
    return [
        'estatus' => 'cancelada',
    ];
});
$factory->state(App\Models\Transaccion::class, 'rechazada-banco', function (Faker\Generator $faker) {
    return [
        'estatus' => 'rechazada-banco',
    ];
});
$factory->state(App\Models\Transaccion::class, 'rechazada-antifraude', function (Faker\Generator $faker) {
    return [
        'estatus' => 'rechazada-antifraude',
    ];
});
$factory->state(App\Models\Transaccion::class, 'contracargada', function (Faker\Generator $faker) {
    return [
        'estatus' => 'contracargada',
    ];
});
$factory->state(App\Models\Transaccion::class, 'contracargo-pendiente', function (Faker\Generator $faker) {
    return [
        'estatus' => 'contracargo-pendiente',
    ];
});
$factory->state(App\Models\Transaccion::class, 'contracargo-rechazado', function (Faker\Generator $faker) {
    return [
        'estatus' => 'contracargo-rechazado',
    ];
});
$factory->state(App\Models\Transaccion::class, 'fallida', function (Faker\Generator $faker) {
    return [
        'estatus' => 'fallida',
    ];
});
