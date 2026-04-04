<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UsuarioController extends Controller
{
    /**
     * 📋 Lista de usuários com filtros
     */
    public function index(Request $request)
    {
        $query = User::query();

        // 🔎 Filtro por nome ou e-mail
        if ($request->filled('busca')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->busca}%")
                    ->orWhere('email', 'like', "%{$request->busca}%");
            });
        }

        // 🎯 Filtro por perfil
        if ($request->filled('perfil')) {
            $query->where('perfil', $request->perfil);
        }

        $usuarios = $query->latest()->paginate(10);

        return view('admin.usuarios.index', compact('usuarios'));
    }

    /**
     * ➕ Tela de criação
     */
    public function create()
    {
        return view('admin.usuarios.create');
    }

    /**
     * 💾 Salvar novo usuário
     */
    public function store(Request $request)
    {
        // ✅ Validação
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'perfil'   => 'required|in:admin,secretario,operador,tecnico',
            'password' => 'required|min:6|confirmed',
        ]);

        DB::beginTransaction();

        try {

            User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'perfil'   => $request->perfil,
                'password' => $request->password, // ⚠️ hash via cast no model
                'ativo'    => true,
                'telefone'  => $request->telefone,
                'ramal'     => $request->ramal,
                'whatsapp'  => $request->whatsapp,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.usuarios.index')
                ->with('sucesso', 'Usuário criado com sucesso!');
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Erro ao criar usuário', [
                'erro' => $e->getMessage(),
                'usuario_logado' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->with('erro', 'Erro ao criar usuário. Tente novamente.');
        }
    }

    /**
     * 👁️ Visualizar usuário
     */
    public function show(User $usuario)
    {
        return view('admin.usuarios.show', compact('usuario'));
    }

    /**
     * ✏️ Tela de edição
     */
    public function edit(User $usuario)
    {
        return view('admin.usuarios.edit', compact('usuario'));
    }

    /**
     * 🔄 Atualizar usuário
     */
    public function update(Request $request, User $usuario)
    {
        // ✅ Validação
        $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => "required|email|unique:users,email,{$usuario->id}",
            'perfil' => 'required|in:admin,secretario,operador,tecnico',
        ]);

        DB::beginTransaction();

        try {

            // 🎯 Dados base
            $data = $request->only('name', 'email', 'perfil');

            // ✅ ATIVO (checkbox)
            $data['ativo'] = $request->has('ativo');

            // 🔐 Atualizar senha (se informada)
            if ($request->filled('password')) {

                $request->validate([
                    'password' => 'min:6|confirmed'
                ]);

                $data['password'] = $request->password;
            }

            $usuario->update($data);

            DB::commit();

            return redirect()
                ->route('admin.usuarios.index')
                ->with('sucesso', 'Usuário atualizado com sucesso!');
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Erro ao atualizar usuário', [
                'usuario_id' => $usuario->id,
                'erro' => $e->getMessage(),
                'usuario_logado' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->with('erro', 'Erro ao atualizar usuário.');
        }
    }

    /**
     * 🗑️ Excluir usuário
     */
    public function destroy(User $usuario)
    {
        // 🚫 Bloqueia exclusão do próprio usuário
        if ($usuario->id === auth()->id()) {
            return back()->with('erro', 'Você não pode excluir seu próprio usuário.');
        }

        DB::beginTransaction();

        try {

            $usuario->delete();

            DB::commit();

            return redirect()
                ->route('admin.usuarios.index')
                ->with('sucesso', 'Usuário excluído com sucesso!');
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Erro ao excluir usuário', [
                'usuario_id' => $usuario->id,
                'erro' => $e->getMessage(),
                'usuario_logado' => auth()->id(),
            ]);

            return back()->with('erro', 'Erro ao excluir usuário.');
        }
    }

    /**
     * 🔄 Ativar / Desativar usuário
     */
    public function toggleAtivo(User $usuario)
    {
        // 🚫 Bloqueia alterar o próprio usuário
        if ($usuario->id === auth()->id()) {
            return back()->with('erro', 'Você não pode alterar seu próprio status.');
        }

        DB::beginTransaction();

        try {

            $usuario->update([
                'ativo' => !$usuario->ativo
            ]);

            DB::commit();

            return back()->with('sucesso', 'Status atualizado com sucesso!');
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Erro ao alterar status do usuário', [
                'usuario_id' => $usuario->id,
                'erro' => $e->getMessage(),
                'usuario_logado' => auth()->id(),
            ]);

            return back()->with('erro', 'Erro ao atualizar status.');
        }
    }
}
