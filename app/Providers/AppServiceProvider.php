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
        // O sistema usa Tailwind CSS em toda a interface (sem Bootstrap carregado) —
        // "useBootstrapFive" fazia a paginação renderizar sem estilo em qualquer tela.
        Paginator::useTailwind();
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
