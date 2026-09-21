<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php', // API mobile (Fase 8) — prefixo /api
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'perfil'     => \App\Http\Middleware\CheckPerfil::class,
            'api.ativo'  => \App\Http\Middleware\EnsureUsuarioAtivoApi::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API mobile: sempre JSON (401/404/422/429), mesmo sem header Accept.
        // Rotas web continuam com o comportamento padrão (redirect/HTML).
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*') || $request->expectsJson()
        );

        // 404 da API sem expor nome de Model/classe interna.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Recurso não encontrado.'], 404);
            }
        });
    })->create();
