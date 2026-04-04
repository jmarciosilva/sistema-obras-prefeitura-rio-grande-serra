<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaConvenio;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoriaConvenioController extends Controller
{
    public function index(): View
    {
        $categorias = CategoriaConvenio::withCount('convenios')
            ->ordenadas()
            ->get();

        return view('admin.categorias-convenio.index', compact('categorias'));
    }

    public function show(CategoriaConvenio $categoria): View
    {
        $categoria->loadCount('convenios');

        return view('admin.categorias-convenio.show', compact('categoria'));
    }

    public function create(): View
    {
        return view('admin.categorias-convenio.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome'      => 'required|string|max:100|unique:categoria_convenios,nome',
            'descricao' => 'nullable|string|max:500',
        ], [
            'nome.unique' => 'Já existe uma categoria com este nome.',
        ]);

        try {
            DB::beginTransaction();
            CategoriaConvenio::create($dados);
            DB::commit();

            return redirect()
                ->route('admin.categorias-convenio.index')
                ->with('sucesso', "Categoria \"{$dados['nome']}\" criada com sucesso!");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao criar categoria de convênio', ['erro' => $e->getMessage()]);

            return back()->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao salvar. Tente novamente.']);
        }
    }

    public function edit(CategoriaConvenio $categoria): View
    {
        return view('admin.categorias-convenio.edit', compact('categoria'));
    }

    public function update(Request $request, CategoriaConvenio $categoria): RedirectResponse
    {
        $dados = $request->validate([
            'nome'      => "required|string|max:100|unique:categoria_convenios,nome,{$categoria->id}",
            'descricao' => 'nullable|string|max:500',
        ], [
            'nome.unique' => 'Já existe uma categoria com este nome.',
        ]);

        try {
            DB::beginTransaction();
            $categoria->update($dados);
            DB::commit();

            return redirect()
                ->route('admin.categorias-convenio.index')
                ->with('sucesso', "Categoria \"{$dados['nome']}\" atualizada com sucesso!");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar categoria de convênio', ['id' => $categoria->id, 'erro' => $e->getMessage()]);

            return back()->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao salvar. Tente novamente.']);
        }
    }

    public function destroy(CategoriaConvenio $categoria): RedirectResponse
    {
        if ($categoria->convenios()->exists()) {
            return back()->withErrors([
                'geral' => "Não é possível excluir \"{$categoria->nome}\" pois há convênios vinculados a esta categoria.",
            ]);
        }

        try {
            DB::beginTransaction();
            $categoria->delete();
            DB::commit();

            return redirect()
                ->route('admin.categorias-convenio.index')
                ->with('sucesso', "Categoria \"{$categoria->nome}\" excluída com sucesso.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao excluir categoria de convênio', ['id' => $categoria->id, 'erro' => $e->getMessage()]);

            return back()->withErrors(['geral' => 'Ocorreu um erro ao excluir. Tente novamente.']);
        }
    }
}
