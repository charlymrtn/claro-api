<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Usuarios y permisos iniciales
        $this->call(UserTableSeeder::class);
        $this->call(UserPermissionsSeeder::class);
        // Transacciones
         $this->call(TransaccionTableSeeder::class);
    }
}
