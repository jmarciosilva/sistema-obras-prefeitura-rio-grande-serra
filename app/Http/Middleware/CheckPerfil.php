<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: CheckPerfil
 *
 * Verifica se o usuário autenticado possui um dos perfis (roles) permitidos
 * para acessar a rota. Aceita múltiplos perfis separados por pipe:
 *
 *   Route::middleware('perfil:admin')
 *   Route::middleware('perfil:admin|tecnico')
 *   Route::middleware('perfil:admin|tecnico|visualizador')
 *
 * Perfis disponíveis:
 *   admin        → Acesso total (CRUD completo)
 *   tecnico      → Pode criar/editar obras, contratos, execuções
 *   visualizador → Somente leitura
 */
class CheckPerfil
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     * @param  string  ...$perfis  Um ou mais perfis permitidos
     */
    public function handle(Request $request, Closure $next, string ...$perfis): Response
    {
        // Usuário não autenticado — redireciona para login
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('aviso', 'Você precisa fazer login para acessar esta página.');
        }

        $usuario = Auth::user();

        // Conta inativa
        if (! $usuario->ativo) {
            Auth::logout();

            return redirect()->route('login')
                ->with('erro', 'Sua conta está inativa. Entre em contato com o administrador.');
        }

        // Verifica se o perfil do usuário está na lista de perfis permitidos
        if (in_array($usuario->perfil, $perfis, strict: true)) {
            return $next($request);
        }

        // Sem permissão — responde adequadamente para AJAX ou browser normal
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Você não tem permissão para realizar esta ação.',
            ], Response::HTTP_FORBIDDEN);
        }

        abort(403, 'Acesso restrito. Você não tem permissão para esta área.');
    }
}
