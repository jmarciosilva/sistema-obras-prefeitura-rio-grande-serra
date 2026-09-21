<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContratoController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ObraController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Mobile — Fase 8 (MOB-01)
|--------------------------------------------------------------------------
| Somente leitura. Autenticação por token Bearer (Laravel Sanctum).
| Prefixo final: /api/v1 (o /api é aplicado pelo bootstrap/app.php).
*/

Route::prefix('v1')->name('api.v1.')->group(function () {

    // Público — limitado a 5 tentativas/minuto por IP
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login');

    Route::middleware(['auth:sanctum', 'api.ativo', 'throttle:60,1'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');

        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::get('/obras', [ObraController::class, 'index'])->name('obras.index');
        Route::get('/obras/{obra}', [ObraController::class, 'show'])
            ->whereNumber('obra')
            ->name('obras.show');

        Route::get('/contratos', [ContratoController::class, 'index'])->name('contratos.index');
        Route::get('/contratos/{contrato}', [ContratoController::class, 'show'])
            ->whereNumber('contrato')
            ->name('contratos.show');
    });
});
