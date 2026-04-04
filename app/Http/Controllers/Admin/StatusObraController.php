<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StatusObra;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatusObraController extends Controller
{
    public function index(): View
    {
        $statuses = StatusObra::withCount('obras')
            ->ordenados()
            ->get();

        return view('admin.status-obras.index', compact('statuses'));
    }

    public function show(StatusObra $status): View
    {
        $status->loadCount('obras');

        return view('admin.status-obras.show', compact('status'));
    }

    public function create(): View
    {
        // Sugere a próxima ordem disponível
        $proximaOrdem = (StatusObra::max('ordem') ?? 0) + 1;

        return view('admin.status-obras.create', compact('proximaOrdem'));
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome'  => 'required|string|max:80|unique:status_obras,nome',
            'cor'   => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'ordem' => 'required|integer|min:0|max:255',
        ], [
            'nome.unique'  => 'Já existe um status com este nome.',
            'cor.regex'    => 'A cor deve estar no formato hexadecimal (ex: #28a745).',
        ]);

        try {
            DB::beginTransaction();

            StatusObra::create($dados);

            DB::commit();

            return redirect()
                ->route('admin.status-obras.index')
                ->with('sucesso', "Status \"{$dados['nome']}\" criado com sucesso!");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao criar status de obra', ['erro' => $e->getMessage()]);

            return back()->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao salvar. Tente novamente.']);
        }
    }

    public function edit(StatusObra $status): View
    {
        return view('admin.status-obras.edit', compact('status'));
    }

    public function update(Request $request, StatusObra $status): RedirectResponse
    {
        $dados = $request->validate([
            'nome'  => "required|string|max:80|unique:status_obras,nome,{$status->id}",
            'cor'   => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'ordem' => 'required|integer|min:0|max:255',
        ], [
            'nome.unique' => 'Já existe um status com este nome.',
            'cor.regex'   => 'A cor deve estar no formato hexadecimal (ex: #28a745).',
        ]);

        try {
            DB::beginTransaction();

            $status->update($dados);

            DB::commit();

            return redirect()
                ->route('admin.status-obras.index')
                ->with('sucesso', "Status \"{$dados['nome']}\" atualizado com sucesso!");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar status de obra', ['id' => $status->id, 'erro' => $e->getMessage()]);

            return back()->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao salvar. Tente novamente.']);
        }
    }

    public function destroy(StatusObra $status): RedirectResponse
    {
        // Não permite excluir se houver obras vinculadas
        if ($status->obras()->exists()) {
            return back()->withErrors([
                'geral' => "Não é possível excluir \"{$status->nome}\" pois há obras vinculadas a este status.",
            ]);
        }

        try {
            DB::beginTransaction();

            $status->delete();

            DB::commit();

            return redirect()
                ->route('admin.status-obras.index')
                ->with('sucesso', "Status \"{$status->nome}\" excluído com sucesso.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao excluir status de obra', ['id' => $status->id, 'erro' => $e->getMessage()]);

            return back()->withErrors(['geral' => 'Ocorreu um erro ao excluir. Tente novamente.']);
        }
    }
}
