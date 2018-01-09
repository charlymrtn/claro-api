<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Inserta valores iniciales

        // Permisos
        Permission::create(['id' => 1, 'name' => 'accesar api', 'guard_name' => 'web']);

        Permission::create(['id' => 10, 'name' => 'listar usuarios', 'guard_name' => 'web']);
        Permission::create(['id' => 11, 'name' => 'editar usuario', 'guard_name' => 'web']);
        Permission::create(['id' => 12, 'name' => 'crear usuario', 'guard_name' => 'web']);
        Permission::create(['id' => 13, 'name' => 'borrar usuario', 'guard_name' => 'web']);
        Permission::create(['id' => 14, 'name' => 'recuperar usuario', 'guard_name' => 'web']);
        Permission::create(['id' => 15, 'name' => 'eliminar usuario', 'guard_name' => 'web']);

        Permission::create(['id' => 20, 'name' => 'listar permisos', 'guard_name' => 'web']);
        Permission::create(['id' => 21, 'name' => 'editar permiso', 'guard_name' => 'web']);
        Permission::create(['id' => 22, 'name' => 'crear permiso', 'guard_name' => 'web']);
        Permission::create(['id' => 23, 'name' => 'borrar permiso', 'guard_name' => 'web']);

        Permission::create(['id' => 30, 'name' => 'listar tokens', 'guard_name' => 'web']);
        Permission::create(['id' => 31, 'name' => 'editar tokens', 'guard_name' => 'web']);
        Permission::create(['id' => 32, 'name' => 'crear tokens', 'guard_name' => 'web']);
        Permission::create(['id' => 33, 'name' => 'borrar tokens', 'guard_name' => 'web']);
        Permission::create(['id' => 34, 'name' => 'recuperar tokens', 'guard_name' => 'web']);
        Permission::create(['id' => 35, 'name' => 'eliminar tokens', 'guard_name' => 'web']);

        Permission::create(['id' => 40, 'name' => 'listar procesadores', 'guard_name' => 'web']);
        Permission::create(['id' => 41, 'name' => 'editar procesadores', 'guard_name' => 'web']);
        Permission::create(['id' => 42, 'name' => 'habilitar procesadores', 'guard_name' => 'web']);
        Permission::create(['id' => 43, 'name' => 'deshabilitar procesadores', 'guard_name' => 'web']);


        // Tarjetas en bóveda
        Permission::create(['id' => 110, 'name' => 'listar tarjetas', 'guard_name' => 'web']);
        Permission::create(['id' => 111, 'name' => 'editar tarjetas', 'guard_name' => 'web']);
        Permission::create(['id' => 112, 'name' => 'crear tarjetas', 'guard_name' => 'web']);
        Permission::create(['id' => 113, 'name' => 'borrar tarjetas', 'guard_name' => 'web']);
        // Cargos
        Permission::create(['id' => 120, 'name' => 'listar cargos', 'guard_name' => 'web']);
        Permission::create(['id' => 121, 'name' => 'realizar cargos', 'guard_name' => 'web']);
        Permission::create(['id' => 122, 'name' => 'cancelar cargos', 'guard_name' => 'web']);
        // Autorizaciones
        Permission::create(['id' => 130, 'name' => 'listar autorizaciones', 'guard_name' => 'web']);
        Permission::create(['id' => 131, 'name' => 'realizar autorizaciones', 'guard_name' => 'web']);
        Permission::create(['id' => 132, 'name' => 'cancelar autorizaciones', 'guard_name' => 'web']);
        Permission::create(['id' => 133, 'name' => 'confirmar autorizaciones', 'guard_name' => 'web']);
        // Reembolsos
        Permission::create(['id' => 140, 'name' => 'listar reembolsos', 'guard_name' => 'web']);
        Permission::create(['id' => 141, 'name' => 'realizar reembolsos', 'guard_name' => 'web']);
        Permission::create(['id' => 142, 'name' => 'cancelar reembolsos', 'guard_name' => 'web']);

        // Roles

        // Superadmin
        $rol = Role::create(['id' => 11, 'name' => 'superadmin', 'guard_name' => 'web']);
        $rol->syncPermissions(Permission::all());
        // Cliente para tarjetas
        $rol = Role::create(['id' => 100, 'name' => 'cliente tarjetas', 'guard_name' => 'web']);
        $rol->permissions()->sync([112]);
        // Cliente para transacciones
        $rol = Role::create(['id' => 1, 'name' => 'cliente transacciones', 'guard_name' => 'web']);
        $rol->permissions()->sync([120, 121, 122, 130, 131, 132, 133, 140, 141, 142]);

        // Usuarios <-> Roles
        DB::table('model_has_roles')->insert([
            ['role_id' => 11, 'model_id' => 1, 'model_type' => 'App\Models\User'],
        ]);
    }
}
