<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\Contrato;
use App\Models\StatusObra;
use App\Models\OrgaoFinanciador;
use App\Models\Empresa;
use App\Models\ExecucaoObra;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RelatorioExport;
use Carbon\Carbon;

class RelatorioController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // TELA PRINCIPAL — hub de relatórios
    // ──────────────────────────────────────────────────────────────
    public function index(): View
    {
        $totais = [
            'obras'             => Obra::count(),
            'contratos'         => Contrato::count(),
            'valor_contratado'  => Contrato::sum('valor_contrato'),
            'valor_medido'      => ExecucaoObra::sum('valor_medido'),
        ];

        $statuses  = StatusObra::ordenados()->get();
        $orgaos    = OrgaoFinanciador::orderBy('nome')->get();
        $empresas  = Empresa::orderBy('razao_social')->get();

        return view('relatorios.index', compact('totais', 'statuses', 'orgaos', 'empresas'));
    }

    // ──────────────────────────────────────────────────────────────
    // PRÉVIA HTML — exibe o relatório antes de exportar
    // ──────────────────────────────────────────────────────────────
    public function preview(Request $request): View
    {
        $dados = $this->coletarDados($request);

        return view('relatorios.preview', array_merge($dados, [
            'filtros' => $request->only([
                'tipo',
                'status_id',
                'orgao_id',
                'empresa_id',
                'data_inicio',
                'data_fim',
                'vencimento_dias',
            ]),
        ]));
    }

    // ──────────────────────────────────────────────────────────────
    // EXPORTAR PDF
    // ──────────────────────────────────────────────────────────────
    public function exportarPdf(Request $request)
    {
        $dados = $this->coletarDados($request);

        $pdf = Pdf::loadView('relatorios.pdf', array_merge($dados, [
            'filtros'    => $request->all(),
            'gerado_em'  => now()->format('d/m/Y H:i'),
            'gerado_por' => auth()->user()->name,
        ]))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
            ]);

        $nomeArquivo = 'relatorio-obras-' . now()->format('Y-m-d-His') . '.pdf';

        return $pdf->download($nomeArquivo);
    }

    // ──────────────────────────────────────────────────────────────
    // EXPORTAR EXCEL
    // ──────────────────────────────────────────────────────────────
    public function exportarExcel(Request $request)
    {
        $dados       = $this->coletarDados($request);
        $nomeArquivo = 'relatorio-obras-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(new RelatorioExport($dados, $request->all()), $nomeArquivo);
    }

    // ──────────────────────────────────────────────────────────────
    // MOTOR DE DADOS — centraliza os filtros
    // ──────────────────────────────────────────────────────────────
    private function coletarDados(Request $request): array
    {
        $tipo          = $request->input('tipo', 'geral');
        $statusId      = $request->input('status_id');
        $orgaoId       = $request->input('orgao_id');
        $empresaId     = $request->input('empresa_id');
        $dataInicio    = $request->input('data_inicio');
        $dataFim       = $request->input('data_fim');
        $vencimentoDias = (int) $request->input('vencimento_dias', 30);

        // ── Base de obras com eager loading ──────────────────────
        $obrasQuery = Obra::with([
            'status',
            'convenios.orgaoFinanciador',
            'contratos.empresa',
            'contratos.execucoes',
        ]);

        if ($statusId) {
            $obrasQuery->where('status_obra_id', $statusId);
        }

        if ($orgaoId) {
            $obrasQuery->whereHas(
                'convenios.orgaoFinanciador',
                fn($q) => $q->where('id', $orgaoId)
            );
        }

        if ($empresaId) {
            $obrasQuery->whereHas(
                'contratos',
                fn($q) => $q->where('empresa_id', $empresaId)
            );
        }

        $obras = $obrasQuery->latest()->get();

        // ── Filtro de período nas execuções ───────────────────────
        if ($dataInicio || $dataFim) {
            $obras = $obras->map(function ($obra) use ($dataInicio, $dataFim) {
                $obra->contratos->each(function ($c) use ($dataInicio, $dataFim) {
                    $c->setRelation(
                        'execucoes',
                        $c->execucoes->filter(function ($e) use ($dataInicio, $dataFim) {
                            $d = $e->data_medicao;
                            if ($dataInicio && $d->lt(Carbon::parse($dataInicio))) return false;
                            if ($dataFim    && $d->gt(Carbon::parse($dataFim)))    return false;
                            return true;
                        })->values()
                    );
                });
                return $obra;
            });
        }

        // ── KPIs globais ──────────────────────────────────────────
        $valorContratadoTotal = $obras->sum(fn($o) => $o->contratos->sum('valor_contrato'));
        $valorMedidoTotal     = $obras->sum(fn($o) => $o->contratos->sum(
            fn($c) => $c->execucoes->sum('valor_medido')
        ));
        $saldoTotal           = max($valorContratadoTotal - $valorMedidoTotal, 0);
        $percentualGeral      = $valorContratadoTotal > 0
            ? round(($valorMedidoTotal / $valorContratadoTotal) * 100, 1)
            : 0;

        // ── Dados por status para gráfico pizza ───────────────────
        $porStatus = StatusObra::ordenados()->get()->map(fn($s) => [
            'nome'  => $s->nome,
            'cor'   => $s->cor,
            'total' => $obras->where('status_obra_id', $s->id)->count(),
        ])->filter(fn($i) => $i['total'] > 0)->values();

        // ── Execução financeira por obra ──────────────────────────
        $execucaoFinanceira = $obras->map(fn($o) => [
            'obra'              => $o,
            'valor_contratado'  => $o->contratos->sum('valor_contrato'),
            'valor_medido'      => $o->contratos->sum(fn($c) => $c->execucoes->sum('valor_medido')),
            'percentual'        => $o->percentual_executado,
            'saldo'             => $o->saldo_contratual,
        ]);

        // ── Contratos vencendo ────────────────────────────────────
        $limite = Carbon::today()->addDays($vencimentoDias);

        $contratosVencendo = Contrato::with(['obra', 'empresa'])
            ->whereNotNull('vigencia_contrato')
            ->where('vigencia_contrato', '>=', Carbon::today())
            ->where('vigencia_contrato', '<=', $limite)
            ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
            ->orderBy('vigencia_contrato')
            ->get()
            ->map(fn($c) => [
                'contrato'          => $c,
                'dias_restantes'    => (int) Carbon::today()->diffInDays($c->vigencia_contrato),
            ]);

        $contratosVencidos = Contrato::with(['obra', 'empresa'])
            ->whereNotNull('vigencia_contrato')
            ->where('vigencia_contrato', '<', Carbon::today())
            ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
            ->orderByDesc('vigencia_contrato')
            ->get();

        // ── Evolução mensal (últimos 12 meses) ────────────────────
        $evolucaoMensal = collect();
        for ($i = 11; $i >= 0; $i--) {
            $mes = Carbon::today()->startOfMonth()->subMonths($i);
            $medido = ExecucaoObra::whereHas('contrato', fn($q) => $q->whereIn(
                'obra_id',
                $obras->pluck('id')
            ))
                ->whereYear('data_medicao', $mes->year)
                ->whereMonth('data_medicao', $mes->month)
                ->sum('valor_medido');

            $evolucaoMensal->push([
                'mes'    => $mes->format('M/y'),
                'valor'  => (float) $medido,
            ]);
        }

        // ── Ranking por empresa ───────────────────────────────────
        $porEmpresa = Empresa::withCount('contratos')
            ->with('contratos')
            ->having('contratos_count', '>', 0)
            ->get()
            ->map(fn($e) => [
                'empresa'          => $e,
                'total_contratos'  => $e->contratos_count,
                'valor_total'      => $e->contratos->sum('valor_contrato'),
            ])
            ->sortByDesc('valor_total')
            ->take(10)
            ->values();

        return compact(
            'tipo',
            'obras',
            'valorContratadoTotal',
            'valorMedidoTotal',
            'saldoTotal',
            'percentualGeral',
            'porStatus',
            'execucaoFinanceira',
            'contratosVencendo',
            'contratosVencidos',
            'vencimentoDias',
            'evolucaoMensal',
            'porEmpresa',
            'dataInicio',
            'dataFim',
        );
    }
}
