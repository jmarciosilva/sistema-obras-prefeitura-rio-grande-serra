<?php

namespace App\Providers;

use App\Models\Contrato;
use App\Models\ExecucaoObra;
use App\Models\Obra;
use App\Observers\ContratoObserver;
use App\Observers\ExecucaoObraObserver;
use App\Observers\ObraObserver;
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

        // Auditoria (histórico de atividades): criação, alteração e exclusão
        Obra::observe(ObraObserver::class);
        Contrato::observe(ContratoObserver::class);
        ExecucaoObra::observe(ExecucaoObraObserver::class);

        // Histórico de Atividades: somente Administrador e Secretário
        Gate::define(
            'ver-auditoria',
            fn($user) => in_array($user->perfil, ['admin', 'secretario'], true)
        );
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
