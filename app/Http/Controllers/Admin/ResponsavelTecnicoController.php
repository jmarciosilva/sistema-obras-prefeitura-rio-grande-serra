<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResponsavelTecnico;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResponsavelTecnicoController extends Controller
{
    public function index(Request $request): View
    {
        $responsaveis = ResponsavelTecnico::withCount('processos')
            ->when(
                $request->busca,
                fn($q) => $q->where('nome', 'like', "%{$request->busca}%")
                    ->orWhere('registro', 'like', "%{$request->busca}%")
            )
            ->orderBy('nome')
            ->paginate(15)
            ->withQueryString();

        return view('admin.responsaveis-tecnicos.index', compact('responsaveis'));
    }

    public function create(): View
    {
        return view('admin.responsaveis-tecnicos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome'      => 'required|string|max:200',
            'registro'  => 'nullable|string|max:50',
            'telefone'  => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $responsavel = ResponsavelTecnico::create($dados);

            DB::commit();

            // Suporte ao redirecionamento personalizado (ex: volta ao create/edit de processo),
            // já com o novo responsável pré-selecionado no formulário de destino.
            $redirectTo = $request->input('_redirect_back', route('admin.responsaveis-tecnicos.index'));
            $separador  = str_contains($redirectTo, '?') ? '&' : '?';

            return redirect($redirectTo . $separador . 'responsavel_tecnico_id=' . $responsavel->id)
                ->with('responsavel_criado', "Responsável técnico \"{$dados['nome']}\" cadastrado com sucesso!");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao criar responsável técnico', ['erro' => $e->getMessage(), 'dados' => $dados]);

            return back()
                ->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao salvar o responsável técnico. Tente novamente.']);
        }
    }

    public function edit(ResponsavelTecnico $responsavel): View
    {
        return view('admin.responsaveis-tecnicos.edit', compact('responsavel'));
    }

    public function update(Request $request, ResponsavelTecnico $responsavel): RedirectResponse
    {
        $dados = $request->validate([
            'nome'      => 'required|string|max:200',
            'registro'  => 'nullable|string|max:50',
            'telefone'  => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $responsavel->update($dados);

            DB::commit();

            return redirect()
                ->route('admin.responsaveis-tecnicos.index')
                ->with('sucesso', "Responsável técnico \"{$dados['nome']}\" atualizado com sucesso!");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar responsável técnico', ['id' => $responsavel->id, 'erro' => $e->getMessage()]);

            return back()
                ->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao atualizar o responsável técnico. Tente novamente.']);
        }
    }

    public function destroy(ResponsavelTecnico $responsavel): RedirectResponse
    {
        if ($responsavel->processos()->exists()) {
            return back()->withErrors([
                'geral' => "Não é possível excluir \"{$responsavel->nome}\" pois há processos vinculados a ele.",
            ]);
        }

        try {
            DB::beginTransaction();

            $responsavel->delete();

            DB::commit();

            return redirect()
                ->route('admin.responsaveis-tecnicos.index')
                ->with('sucesso', "Responsável técnico \"{$responsavel->nome}\" excluído com sucesso.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao excluir responsável técnico', ['id' => $responsavel->id, 'erro' => $e->getMessage()]);

            return back()->withErrors(['geral' => 'Ocorreu um erro ao excluir. Tente novamente.']);
        }
    }
}
