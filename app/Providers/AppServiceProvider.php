<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Gate::define(
            'criar-obra',
            fn($user) =>
            in_array($user->perfil, ['admin', 'tecnico'])
        );

        Gate::define(
            'admin',
            fn($user) =>
            $user->perfil === 'admin'
        );
    }
}
