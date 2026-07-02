<?php

namespace App\Http\Controllers;

use App\Models\FaseProcesso;
use App\Models\Processo;
use App\Models\ResponsavelTecnico;
use App\Models\TipoProcesso;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RelatorioProcessoExport;

/**
 * Relatórios de Processos Administrativos (Fase 7.5) — reaproveita a mesma
 * infraestrutura de exportação da Fase 3 (dompdf + maatwebsite/excel), mas em
 * um controller e views próprios para não interferir nos relatórios de Obras.
 */
class RelatorioProcessoController extends Controller
{
    /** dompdf estoura o limite de memória do PHP em tabelas HTML muito grandes. */
    private const LIMITE_LINHAS_PDF = 500;

    // ──────────────────────────────────────────────────────────────
    // TELA PRINCIPAL — hub de relatórios de processos
    // ──────────────────────────────────────────────────────────────
    public function index(): View
    {
        $totais = [
            'processos'        => Processo::count(),
            'abertos'          => Processo::where('situacao', Processo::SITUACAO_ABERTO)->count(),
            'com_pendencia'    => Processo::whereNotNull('motivo_pendencia')->where('motivo_pendencia', '!=', '')->count(),
            'a_classificar'    => Processo::whereHas('faseAtual', fn($q) => $q->where('nome', 'A Classificar'))->count(),
        ];

        $tiposProcesso = TipoProcesso::ordenados()->get();
        $fasesProcesso = FaseProcesso::ordenados()->get();
        $responsaveis  = ResponsavelTecnico::ordenados()->get();

        return view('relatorios.processos.index', compact('totais', 'tiposProcesso', 'fasesProcesso', 'responsaveis'));
    }

    // ──────────────────────────────────────────────────────────────
    // PRÉVIA HTML
    // ──────────────────────────────────────────────────────────────
    public function preview(Request $request): View
    {
        $dados = $this->coletarDados($request);

        return view('relatorios.processos.preview', array_merge($dados, [
            'filtros' => $request->only([
                'tipo', 'tipo_processo_id', 'fase_atual_id', 'responsavel_tecnico_id',
                'situacao', 'data_inicio', 'data_fim',
            ]),
        ]));
    }

    // ──────────────────────────────────────────────────────────────
    // EXPORTAR PDF
    // ──────────────────────────────────────────────────────────────
    public function exportarPdf(Request $request)
    {
        $dados = $this->coletarDados($request);

        // dompdf consome memória de forma desproporcional em tabelas HTML grandes
        // (>500 linhas já é suficiente para estourar o limite padrão de memória do PHP).
        // A lista completa sem esse limite continua disponível na exportação Excel.
        $totalProcessosOriginal = $dados['processos']->count();
        $dados['processos']     = $dados['processos']->take(self::LIMITE_LINHAS_PDF);
        $dados['comPendencia']  = $dados['comPendencia']->take(self::LIMITE_LINHAS_PDF);

        $pdf = Pdf::loadView('relatorios.processos.pdf', array_merge($dados, [
            'gerado_em'              => now()->format('d/m/Y H:i'),
            'gerado_por'             => auth()->user()->name,
            'totalProcessosOriginal' => $totalProcessosOriginal,
        ]))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont'          => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
            ]);

        $nomeArquivo = 'relatorio-processos-' . now()->format('Y-m-d-His') . '.pdf';

        return $pdf->download($nomeArquivo);
    }

    // ──────────────────────────────────────────────────────────────
    // EXPORTAR EXCEL
    // ──────────────────────────────────────────────────────────────
    public function exportarExcel(Request $request)
    {
        $dados       = $this->coletarDados($request);
        $nomeArquivo = 'relatorio-processos-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(new RelatorioProcessoExport($dados), $nomeArquivo);
    }

    // ──────────────────────────────────────────────────────────────
    // MOTOR DE DADOS — centraliza os filtros
    // ──────────────────────────────────────────────────────────────
    private function coletarDados(Request $request): array
    {
        $tipo                 = $request->input('tipo', 'geral');
        $tipoProcessoId       = $request->input('tipo_processo_id');
        $faseAtualId          = $request->input('fase_atual_id');
        $responsavelTecnicoId = $request->input('responsavel_tecnico_id');
        $situacao             = $request->input('situacao');
        $dataInicio           = $request->input('data_inicio');
        $dataFim               = $request->input('data_fim');

        $query = Processo::with(['tipoProcesso', 'faseAtual', 'responsavelTecnico'])
            ->when($tipoProcessoId, fn($q) => $q->where('tipo_processo_id', $tipoProcessoId))
            ->when($faseAtualId, fn($q) => $q->where('fase_atual_id', $faseAtualId))
            ->when($responsavelTecnicoId, fn($q) => $q->where('responsavel_tecnico_id', $responsavelTecnicoId))
            ->when($situacao, fn($q) => $q->where('situacao', $situacao))
            ->when($dataInicio, fn($q) => $q->whereDate('data_entrada', '>=', Carbon::parse($dataInicio)))
            ->when($dataFim, fn($q) => $q->whereDate('data_entrada', '<=', Carbon::parse($dataFim)));

        $processos = $query->latest('data_entrada')->get();

        // ── KPIs do conjunto filtrado ──────────────────────────────
        $totalFiltrado    = $processos->count();
        $abertosFiltrado  = $processos->where('situacao', Processo::SITUACAO_ABERTO)->count();
        $comPendencia     = $processos->filter(fn($p) => filled($p->motivo_pendencia));

        // ── Agrupamento por fase (gráfico pizza) ───────────────────
        $porFase = FaseProcesso::ordenados()->get()->map(fn($f) => [
            'nome'  => $f->nome,
            'cor'   => $f->cor,
            'total' => $processos->where('fase_atual_id', $f->id)->count(),
        ])->filter(fn($i) => $i['total'] > 0)->values();

        // ── Agrupamento por tipo (barras) ──────────────────────────
        $porTipo = TipoProcesso::ordenados()->get()->map(fn($t) => [
            'nome'  => $t->nome,
            'total' => $processos->where('tipo_processo_id', $t->id)->count(),
        ])->filter(fn($i) => $i['total'] > 0)->sortByDesc('total')->values();

        // ── Ranking por responsável técnico ────────────────────────
        $porResponsavel = $processos
            ->filter(fn($p) => $p->responsavelTecnico !== null)
            ->groupBy('responsavel_tecnico_id')
            ->map(fn($grupo) => [
                'responsavel' => $grupo->first()->responsavelTecnico,
                'total'       => $grupo->count(),
            ])
            ->sortByDesc('total')
            ->values();

        return compact(
            'tipo',
            'processos',
            'totalFiltrado',
            'abertosFiltrado',
            'comPendencia',
            'porFase',
            'porTipo',
            'porResponsavel',
            'dataInicio',
            'dataFim',
        );
    }
}
