<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemandaProposta;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DemandaPropostaController extends Controller
{
    public function index(Request $request): View
    {
        $demandas = DemandaProposta::withCount('obras')
            ->when($request->situacao, fn($q) => $q->where('situacao', $request->situacao))
            ->when($request->origem,   fn($q) => $q->where('origem', $request->origem))
            ->when($request->busca,    fn($q) => $q->where('numero_demanda', 'like', "%{$request->busca}%")
                ->orWhere('descricao', 'like', "%{$request->busca}%"))
            ->orderByDesc('data_solicitacao')
            ->orderByDesc('id')
            ->get();

        $situacoes = DemandaProposta::$situacoes;
        $origens   = DemandaProposta::$origens;

        return view('admin.demandas-propostas.index', compact('demandas', 'situacoes', 'origens'));
    }

    public function show(DemandaProposta $demanda): View
    {
        $demanda->loadCount('obras');

        return view('admin.demandas-propostas.show', compact('demanda'));
    }

    public function create(): View
    {
        $situacoes = DemandaProposta::$situacoes;
        $origens   = DemandaProposta::$origens;

        return view('admin.demandas-propostas.create', compact('situacoes', 'origens'));
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'numero_demanda'   => 'required|string|max:100|unique:demandas_propostas,numero_demanda',
            'descricao'        => 'required|string|max:500',
            'origem'           => 'required|in:secretaria,vereador,estado,federal,outros',
            'solicitante'      => 'nullable|string|max:150',
            'data_solicitacao' => 'nullable|date',
            'situacao'         => 'required|in:pendente,aprovada,rejeitada,em_andamento',
            'observacoes'      => 'nullable|string|max:2000',
        ], [
            'numero_demanda.unique' => 'Já existe uma demanda com este número.',
        ]);

        try {
            DB::beginTransaction();
            DemandaProposta::create($dados);
            DB::commit();

            return redirect()
                ->route('admin.demandas-propostas.index')
                ->with('sucesso', "Demanda \"{$dados['numero_demanda']}\" criada com sucesso!");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao criar demanda', ['erro' => $e->getMessage()]);

            return back()->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao salvar. Tente novamente.']);
        }
    }

    public function edit(DemandaProposta $demanda): View
    {
        $situacoes = DemandaProposta::$situacoes;
        $origens   = DemandaProposta::$origens;

        return view('admin.demandas-propostas.edit', compact('demanda', 'situacoes', 'origens'));
    }

    public function update(Request $request, DemandaProposta $demanda): RedirectResponse
    {
        $dados = $request->validate([
            'numero_demanda'   => "required|string|max:100|unique:demandas_propostas,numero_demanda,{$demanda->id}",
            'descricao'        => 'required|string|max:500',
            'origem'           => 'required|in:secretaria,vereador,estado,federal,outros',
            'solicitante'      => 'nullable|string|max:150',
            'data_solicitacao' => 'nullable|date',
            'situacao'         => 'required|in:pendente,aprovada,rejeitada,em_andamento',
            'observacoes'      => 'nullable|string|max:2000',
        ], [
            'numero_demanda.unique' => 'Já existe uma demanda com este número.',
        ]);

        try {
            DB::beginTransaction();
            $demanda->update($dados);
            DB::commit();

            return redirect()
                ->route('admin.demandas-propostas.index')
                ->with('sucesso', "Demanda \"{$dados['numero_demanda']}\" atualizada com sucesso!");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar demanda', ['id' => $demanda->id, 'erro' => $e->getMessage()]);

            return back()->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao salvar. Tente novamente.']);
        }
    }

    public function destroy(DemandaProposta $demanda): RedirectResponse
    {
        if ($demanda->obras()->exists()) {
            return back()->withErrors([
                'geral' => "Não é possível excluir a demanda \"{$demanda->numero_demanda}\" pois há obras vinculadas.",
            ]);
        }

        try {
            DB::beginTransaction();
            $demanda->delete();
            DB::commit();

            return redirect()
                ->route('admin.demandas-propostas.index')
                ->with('sucesso', "Demanda \"{$demanda->numero_demanda}\" excluída com sucesso.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao excluir demanda', ['id' => $demanda->id, 'erro' => $e->getMessage()]);

            return back()->withErrors(['geral' => 'Ocorreu um erro ao excluir. Tente novamente.']);
        }
    }
}
