<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\StatusObra;
use App\Models\Convenio;
use App\Models\DemandaProposta;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ObraController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────
    public function index(Request $request): View
    {
        $obras = Obra::with(['status', 'convenios', 'contratos'])
            ->when($request->status_id, fn($q) => $q->where('status_obra_id', $request->status_id))
            ->when($request->busca,     fn($q) => $q->where('descricao', 'like', "%{$request->busca}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statuses = StatusObra::ordenados()->get();

        return view('obras.index', compact('obras', 'statuses'));
    }

    // ── Show ───────────────────────────────────────────────────────
    public function show(Obra $obra): View
    {
        $obra->load([
            'status',
            // Convênios com órgão e categoria para a aba Convênios
            'convenios.orgaoFinanciador',
            'convenios.categoria',
            // Contratos com empresa e execuções para as abas Contratos e Execuções
            'contratos.empresa',
            'contratos.execucoes.documentos.usuario',
            // Documentos diretos da obra (com usuário para exibir quem enviou)
            'documentos.usuario',
        ]);

        return view('obras.show', compact('obra'));
    }

    // ── Create ─────────────────────────────────────────────────────
    public function create(): View
    {
        $statuses        = StatusObra::ordenados()->get();
        $convenios       = Convenio::orderBy('descricao')->get();
        $demandasAbertas = DemandaProposta::aprovadas()->orderBy('numero_demanda')->get();

        return view('obras.create', compact('statuses', 'convenios', 'demandasAbertas'));
    }

    // ── Store ──────────────────────────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'descricao'           => 'required|string|max:500',
            'status_obra_id'      => 'required|exists:status_obras,id',
            'endereco'            => 'nullable|string|max:300',
            'processo_execucao'   => 'nullable|string|max:100',
            'demanda_proposta_id' => 'nullable|exists:demandas_propostas,id',
            'convenios'           => 'nullable|array',
            'convenios.*'         => 'exists:convenios,id',
            'observacoes'         => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $convenios = $dados['convenios'] ?? [];
            unset($dados['convenios']);

            $obra = Obra::create($dados);

            if ($convenios) {
                $obra->convenios()->sync($convenios);
            }

            DB::commit();

            return redirect()
                ->route('obras.show', $obra)
                ->with('sucesso', 'Obra cadastrada com sucesso!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao criar obra', ['erro' => $e->getMessage(), 'dados' => $dados]);

            return back()
                ->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao salvar a obra. Tente novamente.']);
        }
    }

    // ── Edit ───────────────────────────────────────────────────────
    public function edit(Obra $obra): View
    {
        $statuses             = StatusObra::ordenados()->get();
        $convenios            = Convenio::orderBy('descricao')->get();
        $demandasAbertas      = DemandaProposta::aprovadas()->orderBy('numero_demanda')->get();
        $conveniosSelecionados = $obra->convenios->pluck('id')->toArray();

        return view('obras.edit', compact(
            'obra',
            'statuses',
            'convenios',
            'demandasAbertas',
            'conveniosSelecionados'
        ));
    }

    // ── Update ─────────────────────────────────────────────────────
    public function update(Request $request, Obra $obra): RedirectResponse
    {
        $dados = $request->validate([
            'descricao'           => 'required|string|max:500',
            'status_obra_id'      => 'required|exists:status_obras,id',
            'endereco'            => 'nullable|string|max:300',
            'processo_execucao'   => 'nullable|string|max:100',
            'demanda_proposta_id' => 'nullable|exists:demandas_propostas,id',
            'convenios'           => 'nullable|array',
            'convenios.*'         => 'exists:convenios,id',
            'observacoes'         => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $convenios = $dados['convenios'] ?? [];
            unset($dados['convenios']);

            $obra->update($dados);
            $obra->convenios()->sync($convenios);

            DB::commit();

            return redirect()
                ->route('obras.show', $obra)
                ->with('sucesso', 'Obra atualizada com sucesso!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar obra', ['id' => $obra->id, 'erro' => $e->getMessage()]);

            return back()
                ->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao atualizar a obra. Tente novamente.']);
        }
    }

    // ── Destroy ────────────────────────────────────────────────────
    public function destroy(Obra $obra): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Desvincula convênios (pivot) antes de deletar
            $obra->convenios()->detach();
            $obra->delete();

            DB::commit();

            return redirect()
                ->route('obras.index')
                ->with('sucesso', 'Obra excluída com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao excluir obra', ['id' => $obra->id, 'erro' => $e->getMessage()]);

            return back()->withErrors(['geral' => 'Não foi possível excluir a obra. Verifique se há dados vinculados.']);
        }
    }
}
