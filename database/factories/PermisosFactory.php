<?php

// Permission Model Factories
$factory->define(Spatie\Permission\Models\Permission::class, function (Faker\Generator $faker) {

    return [
        'name' => $faker->text(30),
        'guard' => 'fake',
        // Timestamps
        'created_at' => $faker->dateTimeBetween('-10 days', '-5 days')->format('Y-m-d H:i:s'),
        'updated_at' => $faker->dateTimeBetween('-5 days')->format('Y-m-d H:i:s'),
    ];
});

// Roles Model factories
$factory->define(Spatie\Permission\Models\Role::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'guard_name' => 'web',
        'created_at' => $faker->dateTimeBetween('-10 days', '-5 days')->format('Y-m-d H:i:s'),
        'updated_at' => $faker->dateTimeBetween('-5 days')->format('Y-m-d H:i:s'),
    ];
});
