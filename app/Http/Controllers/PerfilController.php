<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Autoatendimento do usuário logado ("Meu perfil").
 *
 * Disponível para TODOS os perfis (admin, secretario, operador, tecnico).
 * A interface é uma modal no layout principal (layouts/app.blade.php);
 * cada formulário usa um error bag próprio para a modal reabrir na aba certa
 * quando a validação falhar.
 *
 * Nunca altera 'perfil' nem 'ativo' — isso é exclusivo do admin em Admin\UsuarioController.
 */
class PerfilController extends Controller
{
    /**
     * Não há página dedicada: redireciona para o dashboard já com a modal aberta.
     */
    public function edit()
    {
        return redirect()->route('dashboard')->with('abrir_perfil', 'dados');
    }

    public function update(Request $request)
    {
        $usuario = $request->user();

        $dados = $request->validateWithBag('perfilDados', [
            'name'  => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
        ], [], [
            'name'  => 'nome',
            'email' => 'e-mail',
        ]);

        $usuario->update($dados);

        return back()->with('sucesso', 'Seus dados foram atualizados!');
    }

    public function updateSenha(Request $request)
    {
        $request->validateWithBag('perfilSenha', [
            'senha_atual' => 'required|current_password',
            'password'    => ['required', 'confirmed', Password::min(8)],
        ], [
            'senha_atual.current_password' => 'A senha atual informada está incorreta.',
        ], [
            'senha_atual' => 'senha atual',
            'password'    => 'nova senha',
        ]);

        // Hash aplicado automaticamente pelo cast 'hashed' do model User
        $request->user()->update(['password' => $request->password]);

        return back()->with('sucesso', 'Senha alterada com sucesso!');
    }
}
