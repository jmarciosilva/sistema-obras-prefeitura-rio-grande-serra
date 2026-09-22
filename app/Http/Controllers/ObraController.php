<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\StatusObra;
use App\Models\Convenio;
use App\Models\DemandaProposta;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Auditoria;
use App\Services\AuditoriaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            'convenios.orgaoFinanciador',
            'convenios.categoria',
            'contratos.empresa',
            'contratos.execucoes.documentos.usuario',
            'documentos.usuario',
        ]);

        // Todos os convênios disponíveis para o modal de vínculos
        $todosConvenios = Convenio::with(['categoria', 'orgaoFinanciador'])
            ->orderBy('numero_convenio_ano')
            ->get();

        return view('obras.show', compact('obra', 'todosConvenios'));
    }

    // ── Sync Convênios (modal da aba Convênios) ────────────────────
    public function syncConvenios(Request $request, Obra $obra): RedirectResponse
    {
        $request->validate([
            'convenios'   => 'nullable|array',
            'convenios.*' => 'exists:convenios,id',
        ]);

        try {
            DB::beginTransaction();
            $this->sincronizarConvenios($obra, $request->input('convenios', []));
            DB::commit();

            return redirect()
                ->route('obras.show', $obra)
                ->with('sucesso', 'Convênios atualizados com sucesso!')
                ->with('aba', 'convenios');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao sincronizar convênios da obra', ['obra_id' => $obra->id, 'erro' => $e->getMessage()]);

            return back()->withErrors(['geral' => 'Ocorreu um erro ao salvar os vínculos.']);
        }
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
                $this->sincronizarConvenios($obra, $convenios);
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
            $this->sincronizarConvenios($obra, $convenios);

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

    // ── Gráfico de evolução (Fase 4) ───────────────────────────
    public function grafico(Obra $obra): \Illuminate\Http\JsonResponse
    {
        $obra->load([
            'contratos.execucoes',
            'contratos',
        ]);

        $valorContratado = (float) $obra->valor_contratado;

        // ── 1. Série temporal: todas as medições ordenadas por data ──
        $medicoes = $obra->contratos
            ->flatMap(fn($c) => $c->execucoes->map(fn($e) => [
                'data'                => $e->data_medicao->format('d/m/Y'),
                'data_iso'            => $e->data_medicao->format('Y-m-d'),
                'valor_medido'        => (float) $e->valor_medido,
                'percentual'          => (float) $e->percentual_executado,
                'saldo'               => (float) ($e->saldo_contratual ?? 0),
                'contrato_label'      => $c->numero_contrato_ano ?? 'Contrato #'.$c->id,
            ]))
            ->sortBy('data_iso')
            ->values();

        // ── 2. Acumulado por data ─────────────────────────────────
        $acumulado        = 0;
        $serieAcumulada   = [];

        foreach ($medicoes as $m) {
            $acumulado += $m['valor_medido'];
            $serieAcumulada[] = [
                'data'       => $m['data'],
                'acumulado'  => round($acumulado, 2),
                'percentual' => $valorContratado > 0
                    ? round(min(($acumulado / $valorContratado) * 100, 100), 2)
                    : 0,
            ];
        }

        // ── 3. Projeção de conclusão ──────────────────────────────
        // Usa as últimas 3 medições para calcular o ritmo médio (R$/dia)
        $projecao = null;

        if ($medicoes->count() >= 2) {
           $ultimas = $medicoes->slice(-3)->values();

            // Diferença de datas entre primeira e última das "últimas"
            $dataInicio  = \Carbon\Carbon::parse($ultimas->first()['data_iso']);
            $dataFim     = \Carbon\Carbon::parse($ultimas->last()['data_iso']);
            $diasPeriodo = max($dataInicio->diffInDays($dataFim), 1);

            $valorPeriodo = $ultimas->sum('valor_medido');
            $ritmoDiario  = $valorPeriodo / $diasPeriodo; // R$/dia

            $saldoAtual = (float) $obra->saldo_contratual;

            if ($ritmoDiario > 0 && $saldoAtual > 0) {
                $diasRestantes     = (int) ceil($saldoAtual / $ritmoDiario);
                $dataConclusao     = now()->addDays($diasRestantes);
                $projecao = [
                    'ritmo_diario'      => round($ritmoDiario, 2),
                    'ritmo_mensal'      => round($ritmoDiario * 30, 2),
                    'dias_restantes'    => $diasRestantes,
                    'data_conclusao'    => $dataConclusao->format('d/m/Y'),
                    'data_conclusao_iso'=> $dataConclusao->format('Y-m-d'),
                    'confianca'         => $ultimas->count() >= 3 ? 'alta' : 'media',
                    'base_medicoes'     => $ultimas->count(),
                ];
            } elseif ($saldoAtual <= 0) {
                $projecao = ['concluida' => true];
            }
        }

        // ── 4. Dados para o gráfico de barras (contratado × acumulado × saldo) ──
        $barras = $obra->contratos->map(fn($c) => [
            'label'      => $c->numero_contrato_ano ?? 'Contrato #'.$c->id,
            'empresa'    => $c->empresa->nome_fantasia ?? $c->empresa->razao_social ?? '—',
            'contratado' => (float) $c->valor_contrato,
            'medido'     => (float) $c->execucoes->sum('valor_medido'),
            'saldo'      => max((float)$c->valor_contrato - (float)$c->execucoes->sum('valor_medido'), 0),
        ])->values();

        return response()->json([
            'obra'             => [
                'id'              => $obra->id,
                'descricao'       => $obra->descricao,
                'valor_contratado'=> $valorContratado,
                'valor_medido'    => (float) $obra->valor_medido,
                'saldo'           => (float) $obra->saldo_contratual,
                'percentual'      => (float) $obra->percentual_executado,
            ],
            'medicoes'         => $medicoes,
            'serie_acumulada'  => $serieAcumulada,
            'barras'           => $barras,
            'projecao'         => $projecao,
        ]);
    }

    // ── Destroy ────────────────────────────────────────────────────
    public function destroy(Obra $obra): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Desvincula convênios (pivot) antes de deletar — com auditoria
            $this->sincronizarConvenios($obra, []);
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

    /**
     * Sincroniza os convênios da obra e registra a mudança na auditoria.
     * O sync() da pivot não dispara o Observer da Obra; a auditoria só é
     * gravada após o commit da transação em andamento.
     */
    private function sincronizarConvenios(Obra $obra, array $convenios): void
    {
        $antes     = $obra->convenios()->pluck('convenios.id')->map(fn($id) => (int) $id)->sort()->values()->all();
        $resultado = $obra->convenios()->sync($convenios);

        if ($resultado['attached'] === [] && $resultado['detached'] === []) {
            return;
        }

        $depois = collect($convenios)->map(fn($id) => (int) $id)->unique()->sort()->values()->all();

        app(AuditoriaService::class)->registrar(
            Auditoria::ALTEROU,
            $obra,
            ['convenios' => $antes],
            ['convenios' => $depois],
            'Convênios da obra atualizados: ' . Str::limit((string) $obra->descricao, 80),
        );
    }
}
