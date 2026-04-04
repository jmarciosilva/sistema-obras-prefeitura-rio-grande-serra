<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\Convenio;
use App\Models\Contrato;
use App\Models\StatusObra;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Totalizadores para os cards ───────────────────────────
        $totalObras      = Obra::count();
        $obrasEmExecucao = Obra::whereHas('status', fn($q) => $q->where('nome', 'Em Execução'))->count();
        $obrasConcluidas = Obra::whereHas('status', fn($q) => $q->where('nome', 'Concluída'))->count();
        $totalConvenios  = Convenio::count();

        // ── Obras por status (para o gráfico de barras) ───────────
        $obrasPorStatus = StatusObra::withCount('obras')
            ->orderBy('ordem')
            ->get();

        // ── Últimas 8 obras cadastradas ───────────────────────────
        $ultimasObras = Obra::with(['status', 'contratos'])
            ->latest()
            ->take(8)
            ->get();

        return view('dashboard', compact(
            'totalObras',
            'obrasEmExecucao',
            'obrasConcluidas',
            'totalConvenios',
            'obrasPorStatus',
            'ultimasObras',
        ));
    }
}
