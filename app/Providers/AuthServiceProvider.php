<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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

        Gate::define('create-module', function($user){
            return $user->hasRole('Admin');
        });

        Gate::define('assign-module-tutor', function($user){
            return $user->hasRole('Admin');
        });

        Gate::define('assign-students', function($user){
            return $user->hasAnyRoles(['Admin', 'Module Tutor']);
        });
    }
}
