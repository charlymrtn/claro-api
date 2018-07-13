<?php
use App\Classes\Pagos\Base\Direccion;
use App\Classes\Pagos\Base\Telefono;

// Cliente Model Factory
$factory->define(App\Models\Cliente::class, function (Faker\Generator $faker) {

    $genero = $faker->randomElement(['male', 'female']);
    $telefono = [
        "tipo" => $faker->randomElement(['Móvil', 'Casa', 'Oficina']),
        "codigo_pais" => $faker->areaCode,
        "prefijo" => null,
        "codigo_area" => $faker->areaCode,
        "numero" => $faker->phoneNumber,
        "extension" => null,
    ];
    $direccion = [
        "pais" => strtoupper($faker->lexify('???')),
        "estado" => strtoupper($faker->lexify('???')),
        "ciudad" => $faker->city,
        "municipio" => $faker->randomElement(['"Delegación', 'Municipio']),
        "cp" => $faker->postcode,
        "linea1" => $faker->streetAddress,
        "linea2" => $faker->secondaryAddress,
        "linea3" => $faker->secondaryAddress,
        "longitud" => $faker->longitude,
        "latitud" => $faker->latitude,
    ];

    return [
        'uuid' => $faker->uuid,
        'comercio_uuid' => $faker->uuid,
        "id_externo" => $faker->randomNumber,
        "creacion_externa" => $faker->dateTimeBetween('-10 days', '-5 days')->format('Y-m-d H:i:s'),
        "nombre" => $faker->firstName($genero),
        "apellido_paterno" => $faker->lastName,
        "apellido_materno" => $faker->lastName,
        "sexo" => $genero == "male" ? "masculino" : "femenino",
        "email" => $faker->email,
        "nacimiento" => $faker->dateTimeBetween('-30 years', '-20 years')->format('Y-m-d'),
        "estado" => $faker->randomElement(['activo', 'suspendido', 'inactivo']),
        "telefono" => new Telefono($telefono),
        "direccion" => new Direccion($direccion),
        'created_at' => $faker->dateTimeBetween('-10 days', '-5 days')->format('Y-m-d H:i:s'),
        'updated_at' => $faker->dateTimeBetween('-5 days')->format('Y-m-d H:i:s'),
    ];
});