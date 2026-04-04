<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrgaoFinanciador;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrgaoFinanciadorController extends Controller
{
    public function index(Request $request): View
    {
        $orgaos = OrgaoFinanciador::withCount('convenios')
            ->when($request->esfera, fn($q) => $q->esfera($request->esfera))
            ->ordenados()
            ->get();

        return view('admin.orgaos-financiadores.index', compact('orgaos'));
    }

    public function show(OrgaoFinanciador $orgao): View
    {
        $orgao->loadCount('convenios');

        return view('admin.orgaos-financiadores.show', compact('orgao'));
    }

    public function create(): View
    {
        $esferas = OrgaoFinanciador::$esferas;

        return view('admin.orgaos-financiadores.create', compact('esferas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome'   => 'required|string|max:150|unique:orgaos_financiadores,nome',
            'sigla'  => 'nullable|string|max:30',
            'esfera' => 'required|in:federal,estadual,municipal',
        ], [
            'nome.unique' => 'Já existe um órgão com este nome.',
        ]);

        try {
            DB::beginTransaction();
            OrgaoFinanciador::create($dados);
            DB::commit();

            return redirect()
                ->route('admin.orgaos-financiadores.index')
                ->with('sucesso', "Órgão \"{$dados['nome']}\" criado com sucesso!");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao criar órgão financiador', ['erro' => $e->getMessage()]);

            return back()->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao salvar. Tente novamente.']);
        }
    }

    public function edit(OrgaoFinanciador $orgao): View
    {
        $esferas = OrgaoFinanciador::$esferas;

        return view('admin.orgaos-financiadores.edit', compact('orgao', 'esferas'));
    }

    public function update(Request $request, OrgaoFinanciador $orgao): RedirectResponse
    {
        $dados = $request->validate([
            'nome'   => "required|string|max:150|unique:orgaos_financiadores,nome,{$orgao->id}",
            'sigla'  => 'nullable|string|max:30',
            'esfera' => 'required|in:federal,estadual,municipal',
        ], [
            'nome.unique' => 'Já existe um órgão com este nome.',
        ]);

        try {
            DB::beginTransaction();
            $orgao->update($dados);
            DB::commit();

            return redirect()
                ->route('admin.orgaos-financiadores.index')
                ->with('sucesso', "Órgão \"{$dados['nome']}\" atualizado com sucesso!");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar órgão financiador', ['id' => $orgao->id, 'erro' => $e->getMessage()]);

            return back()->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao salvar. Tente novamente.']);
        }
    }

    public function destroy(OrgaoFinanciador $orgao): RedirectResponse
    {
        if ($orgao->convenios()->exists()) {
            return back()->withErrors([
                'geral' => "Não é possível excluir \"{$orgao->nome}\" pois há convênios vinculados a este órgão.",
            ]);
        }

        try {
            DB::beginTransaction();
            $orgao->delete();
            DB::commit();

            return redirect()
                ->route('admin.orgaos-financiadores.index')
                ->with('sucesso', "Órgão \"{$orgao->nome}\" excluído com sucesso.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao excluir órgão financiador', ['id' => $orgao->id, 'erro' => $e->getMessage()]);

            return back()->withErrors(['geral' => 'Ocorreu um erro ao excluir. Tente novamente.']);
        }
    }
}
