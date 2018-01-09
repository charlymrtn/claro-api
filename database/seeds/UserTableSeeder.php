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
            'comercio_uuid' => '176f76a8-2670-4288-9800-1dd5f031a57e',
            'name' => 'Claro Pagos',
            'descripcion' => 'Claro Pagos API User',
            'email' => 'superadmin@claropagos.com',
            'password' => '$2y$10$wxS/Nr5p8B./LXTlXbot.u7CVXIT4JA4EKW/unxfg2Lk7e1h/fb8a',
            'remember_token' => 'ttWOtfqmekYfsaGyyXtIDc2iQ0wWzMOslHP9xAeEXjsHfoV9py7nl8NFPTz8',
        ]);
    }
}
