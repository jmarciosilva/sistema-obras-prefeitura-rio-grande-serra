<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Obra;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContratoController extends Controller
{
    public function index(Request $request): View
    {
        $contratos = Contrato::with(['obra', 'empresa'])
            ->when(
                $request->busca,
                fn($q) => $q->where('numero_contrato_ano', 'like', "%{$request->busca}%")
                    ->orWhereHas('obra', fn($q) => $q->where('descricao', 'like', "%{$request->busca}%"))
                    ->orWhereHas('empresa', fn($q) => $q->where('razao_social', 'like', "%{$request->busca}%")
                        ->orWhere('nome_fantasia', 'like', "%{$request->busca}%"))
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('contratos.index', compact('contratos'));
    }

    public function show(Contrato $contrato): View
    {
        $contrato->load([
            'obra.status',
            'empresa',
            'execucoes' => fn($q) => $q->orderByDesc('data_medicao')->orderByDesc('id'),
        ]);

        return view('contratos.show', compact('contrato'));
    }

    public function create(Request $request): View
    {
        $obras           = Obra::with('status')->orderBy('descricao')->get();
        $empresas        = Empresa::orderBy('razao_social')->get();
        $obraSelecionada = $request->obra_id;

        return view('contratos.create', compact('obras', 'empresas', 'obraSelecionada'));
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'obra_id'             => 'required|exists:obras,id',
            'empresa_id'          => 'required|exists:empresas,id',
            'processo_licitacao'  => 'nullable|string|max:100',
            'numero_contrato_ano' => 'nullable|string|max:50',
            'data_assinatura'     => 'nullable|date',
            'ordem_inicio'        => 'nullable|date',
            'vigencia_contrato'   => 'nullable|date',
            'valor_contrato'      => 'nullable|numeric|min:0',
        ]);

        // Normaliza valor monetário vindo com formatação pt-BR (1.234,56 → 1234.56)
        if (isset($dados['valor_contrato'])) {
            $dados['valor_contrato'] = $this->normalizarMoeda($dados['valor_contrato']);
        }

        try {
            DB::beginTransaction();

            $contrato = Contrato::create($dados);

            DB::commit();

            return redirect()
                ->route('contratos.show', $contrato)
                ->with('sucesso', 'Contrato cadastrado com sucesso!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao criar contrato', ['erro' => $e->getMessage(), 'dados' => $dados]);

            return back()
                ->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao salvar o contrato. Tente novamente.']);
        }
    }

    public function edit(Contrato $contrato): View
    {
        $obras    = Obra::orderBy('descricao')->get();
        $empresas = Empresa::orderBy('razao_social')->get();

        return view('contratos.edit', compact('contrato', 'obras', 'empresas'));
    }

    public function update(Request $request, Contrato $contrato): RedirectResponse
    {
        $dados = $request->validate([
            'obra_id'             => 'required|exists:obras,id',
            'empresa_id'          => 'required|exists:empresas,id',
            'processo_licitacao'  => 'nullable|string|max:100',
            'numero_contrato_ano' => 'nullable|string|max:50',
            'data_assinatura'     => 'nullable|date',
            'ordem_inicio'        => 'nullable|date',
            'vigencia_contrato'   => 'nullable|date',
            'valor_contrato'      => 'nullable|numeric|min:0',
        ]);

        if (isset($dados['valor_contrato'])) {
            $dados['valor_contrato'] = $this->normalizarMoeda($dados['valor_contrato']);
        }

        try {
            DB::beginTransaction();

            $contrato->update($dados);

            DB::commit();

            return redirect()
                ->route('contratos.show', $contrato)
                ->with('sucesso', 'Contrato atualizado com sucesso!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar contrato', ['id' => $contrato->id, 'erro' => $e->getMessage()]);

            return back()
                ->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao atualizar o contrato. Tente novamente.']);
        }
    }

    public function destroy(Contrato $contrato): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $contrato->delete();

            DB::commit();

            return redirect()
                ->route('contratos.index')
                ->with('sucesso', 'Contrato excluído com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao excluir contrato', ['id' => $contrato->id, 'erro' => $e->getMessage()]);

            return back()->withErrors(['geral' => 'Não foi possível excluir o contrato. Verifique se há dados vinculados.']);
        }
    }

    // ─────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────

    /**
     * Normaliza valor monetário para decimal puro.
     * O campo hidden já envia 123456789.99, mas esta função
     * garante compatibilidade caso o formato pt-BR seja recebido por engano.
     */
    private function normalizarMoeda(mixed $valor): float
    {
        if (is_string($valor) && str_contains($valor, ',')) {
            // Formato pt-BR: remove pontos de milhar, troca vírgula por ponto
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return (float) $valor;
    }
}
