<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\Convenio;
use App\Models\Contrato;
use App\Models\ExecucaoObra;
use App\Models\StatusObra;
use App\Models\FaseProcesso;
use App\Models\Processo;
use App\Models\TipoProcesso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        // ── Filtro de período ─────────────────────────────────────
        $periodoInicio = $request->filled('inicio')
            ? Carbon::parse($request->inicio)->startOfDay()
            : Carbon::now()->subMonths(12)->startOfDay();

        $periodoFim = $request->filled('fim')
            ? Carbon::parse($request->fim)->endOfDay()
            : Carbon::now()->endOfDay();

        // ── KPIs principais ───────────────────────────────────────
        $totalObras      = Obra::count();
        $obrasEmExecucao = Obra::whereHas('status', fn($q) => $q->where('nome', 'like', '%Execu%'))->count();
        $obrasConcluidas = Obra::whereHas('status', fn($q) => $q->where('nome', 'like', '%Conclu%'))->count();
        $totalConvenios  = Convenio::count();

        // ── Financeiro global ─────────────────────────────────────
        $valorTotalContratado = Contrato::sum('valor_contrato');
        $valorTotalMedido     = ExecucaoObra::sum('valor_medido');
        $saldoGlobal          = max($valorTotalContratado - $valorTotalMedido, 0);
        $percentualGlobal     = $valorTotalContratado > 0
            ? min(($valorTotalMedido / $valorTotalContratado) * 100, 100)
            : 0;

        // ── Obras por status (gráfico pizza) ──────────────────────
        $obrasPorStatus = StatusObra::withCount('obras')
            ->orderBy('ordem')
            ->get();

        // ── Execução financeira por mês (gráfico barras) ──────────
        // Agrupa valor_medido por mês nos últimos 12 meses
        $execucaoPorMes = ExecucaoObra::select(
            DB::raw('YEAR(data_medicao) as ano'),
            DB::raw('MONTH(data_medicao) as mes'),
            DB::raw('SUM(valor_medido) as total')
        )
            ->whereBetween('data_medicao', [$periodoInicio, $periodoFim])
            ->groupBy('ano', 'mes')
            ->orderBy('ano')
            ->orderBy('mes')
            ->get()
            ->map(fn($r) => [
                'label' => Carbon::createFromDate($r->ano, $r->mes, 1)->translatedFormat('M/y'),
                'total' => (float) $r->total,
            ]);

        // ── ALERTAS ───────────────────────────────────────────────

        // Contratos vencidos
        $contratosVencidos = Contrato::with(['obra'])
            ->whereNotNull('vigencia_contrato')
            ->where('vigencia_contrato', '<', now())
            ->orderBy('vigencia_contrato')
            ->take(10)
            ->get();

        // Contratos vencendo em até 30 dias
        $contratosVencendo = Contrato::with(['obra'])
            ->whereNotNull('vigencia_contrato')
            ->whereBetween('vigencia_contrato', [now(), now()->addDays(30)])
            ->orderBy('vigencia_contrato')
            ->take(10)
            ->get();

        // Convênios vencidos
        $conveniosVencidos = Convenio::whereNotNull('vigencia')
            ->where('vigencia', '<', now())
            ->orderBy('vigencia')
            ->take(10)
            ->get();

        // Convênios vencendo em até 60 dias
        $conveniosVencendo = Convenio::whereNotNull('vigencia')
            ->whereBetween('vigencia', [now(), now()->addDays(60)])
            ->orderBy('vigencia')
            ->take(10)
            ->get();

        // Obras sem medição nos últimos 60 dias (em execução)
        $obrasSemMedicaoRecente = Obra::with(['status', 'contratos'])
            ->whereHas('status', fn($q) => $q->where('nome', 'like', '%Execu%'))
            ->whereDoesntHave('contratos.execucoes', function ($q) {
                $q->where('data_medicao', '>=', now()->subDays(60));
            })
            ->whereHas('contratos') // só obras com contrato
            ->take(8)
            ->get();

        // ── Últimas obras cadastradas ─────────────────────────────
        $ultimasObras = Obra::with(['status', 'contratos'])
            ->latest()
            ->take(8)
            ->get();

        // ────────────────────────────────────────────────────────────
        // 🗂️ PROCESSOS ADMINISTRATIVOS (Fase 7.5)
        // ────────────────────────────────────────────────────────────

        // ── KPIs ────────────────────────────────────────────────────
        $totalProcessos       = Processo::count();
        $processosAbertos     = Processo::where('situacao', Processo::SITUACAO_ABERTO)->count();
        $processosArquivados  = Processo::where('situacao', Processo::SITUACAO_ARQUIVADO)->count();
        $processosAClassificar = Processo::whereHas('faseAtual', fn($q) => $q->where('nome', 'A Classificar'))->count();

        // ── Processos por fase (gráfico pizza — resposta direta ao pedido do Secretário) ──
        $processosPorFase = FaseProcesso::withCount('processos')
            ->orderBy('ordem')
            ->get();

        // ── Processos por tipo (gráfico barras horizontal) ─────────
        $processosPorTipo = TipoProcesso::withCount('processos')
            ->orderBy('ordem')
            ->get();

        // ── Processos com pendência — "parado, e por qual motivo" ──
        $processosComPendencia = Processo::with(['tipoProcesso', 'faseAtual'])
            ->where('situacao', Processo::SITUACAO_ABERTO)
            ->whereNotNull('motivo_pendencia')
            ->where('motivo_pendencia', '!=', '')
            ->latest('updated_at')
            ->take(10)
            ->get();

        // ── Últimos processos cadastrados ──────────────────────────
        $ultimosProcessos = Processo::with(['tipoProcesso', 'faseAtual'])
            ->latest()
            ->take(8)
            ->get();

        // ── Total de alertas para badge ───────────────────────────
        $totalAlertas = $contratosVencidos->count()
            + $contratosVencendo->count()
            + $conveniosVencidos->count()
            + $conveniosVencendo->count()
            + $obrasSemMedicaoRecente->count()
            + $processosComPendencia->count();

        return view('dashboard', compact(
            // KPIs
            'totalObras',
            'obrasEmExecucao',
            'obrasConcluidas',
            'totalConvenios',
            // Financeiro
            'valorTotalContratado',
            'valorTotalMedido',
            'saldoGlobal',
            'percentualGlobal',
            // Gráficos
            'obrasPorStatus',
            'execucaoPorMes',
            // Alertas
            'contratosVencidos',
            'contratosVencendo',
            'conveniosVencidos',
            'conveniosVencendo',
            'obrasSemMedicaoRecente',
            'totalAlertas',
            // Listagem
            'ultimasObras',
            // Filtro
            'periodoInicio',
            'periodoFim',
            // Processos administrativos
            'totalProcessos',
            'processosAbertos',
            'processosArquivados',
            'processosAClassificar',
            'processosPorFase',
            'processosPorTipo',
            'processosComPendencia',
            'ultimosProcessos',
        ));
    }
}
