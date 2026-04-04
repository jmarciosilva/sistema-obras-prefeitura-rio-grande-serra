<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PerfilController extends Controller
{
    public function edit()
    {
        return view('perfil.edit', ['usuario' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        auth()->user()->update(['name' => $request->name]);
        return back()->with('sucesso', 'Perfil atualizado!');
    }

    public function updateSenha(Request $request)
    {
        $request->validate([
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);
        auth()->user()->update(['password' => Hash::make($request->password)]);
        return back()->with('sucesso', 'Senha alterada com sucesso!');
    }
}
