<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: EnsureUsuarioAtivoApi (API mobile — Fase 8)
 *
 * Um token emitido continua válido mesmo que o usuário seja desativado depois.
 * Este middleware bloqueia a requisição e revoga o token atual quando o
 * usuário está inativo, equivalente ao que o CheckPerfil faz na sessão web.
 */
class EnsureUsuarioAtivoApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if ($usuario && ! $usuario->ativo) {
            $usuario->currentAccessToken()?->delete();

            return response()->json([
                'message' => 'Sua conta está inativa. Entre em contato com o administrador.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
