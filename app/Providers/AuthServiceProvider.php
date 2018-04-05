<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        // Passport Auth Routes
        Passport::routes();
        // Define Passport Scopes utilizando los Roles de Spatie\Permission
        if (Schema::hasTable('roles')) {
            Passport::tokensCan(Role::where('guard_name', 'api')->pluck('guard_name', 'name')->toArray());
        }
    }
}
