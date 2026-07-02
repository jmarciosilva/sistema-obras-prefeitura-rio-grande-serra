@extends('layouts.app')

@section('title', 'Visão Geral')
@section('subtitle', 'Painel executivo — Secretaria de Obras')

@section('content')

<div x-data="{ abaAlerta: '{{ $contratosVencidos->count() > 0 ? "contratos_vencidos" : ($contratosVencendo->count() > 0 ? "contratos_vencendo" : ($conveniosVencidos->count() > 0 ? "convenios_vencidos" : ($conveniosVencendo->count() > 0 ? "convenios_vencendo" : ($obrasSemMedicaoRecente->count() > 0 ? "sem_medicao" : "processos_pendentes")))) }}' }">

    {{-- ══ FILTRO DE PERÍODO ══════════════════════════════════════ --}}
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-slate-200 px-5 py-3 mb-6 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-36">
            <label class="block text-xs font-medium text-slate-500 mb-1">Período — início</label>
            <input type="date" name="inicio" value="{{ $periodoInicio->format('Y-m-d') }}"
                class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
        </div>
        <div class="flex-1 min-w-36">
            <label class="block text-xs font-medium text-slate-500 mb-1">Período — fim</label>
            <input type="date" name="fim" value="{{ $periodoFim->format('Y-m-d') }}"
                class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-sm">
        </div>
        <button class="px-4 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">🔍 Filtrar</button>
        <a href="{{ route('dashboard') }}" class="px-4 py-1.5 text-sm border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50">↺ Limpar</a>
        <span class="text-xs text-slate-400 self-center">
            {{ $periodoInicio->format('d/m/Y') }} até {{ $periodoFim->format('d/m/Y') }}
        </span>
    </form>

    {{-- ══ KPIs LINHA 1 — OBRAS ═══════════════════════════════════ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
        <a href="{{ route('obras.index') }}" class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm hover:border-blue-300 transition block">
            <p class="text-xs text-slate-500 uppercase font-medium">Total de Obras</p>
            <p class="text-3xl font-bold text-blue-600 mt-1">{{ $totalObras }}</p>
            <p class="text-xs text-slate-400 mt-1">cadastradas no sistema</p>
        </a>
        <div class="bg-white rounded-xl p-5 border border-yellow-200 shadow-sm">
            <p class="text-xs text-slate-500 uppercase font-medium">Em Execução</p>
            <p class="text-3xl font-bold text-yellow-500 mt-1">{{ $obrasEmExecucao }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $totalObras > 0 ? number_format(($obrasEmExecucao/$totalObras)*100,0) : 0 }}% do total</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-green-200 shadow-sm">
            <p class="text-xs text-slate-500 uppercase font-medium">Concluídas</p>
            <p class="text-3xl font-bold text-green-600 mt-1">{{ $obrasConcluidas }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $totalObras > 0 ? number_format(($obrasConcluidas/$totalObras)*100,0) : 0 }}% do total</p>
        </div>
        <a href="{{ route('convenios.index') }}" class="bg-white rounded-xl p-5 border border-indigo-200 shadow-sm hover:border-indigo-300 transition block">
            <p class="text-xs text-slate-500 uppercase font-medium">Convênios</p>
            <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $totalConvenios }}</p>
            <p class="text-xs text-slate-400 mt-1">instrumentos ativos</p>
        </a>
    </div>

    {{-- ══ KPIs LINHA 2 — FINANCEIRO ══════════════════════════════ --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500 uppercase font-medium">Valor Total Contratado</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">R$ {{ number_format($valorTotalContratado, 2, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">soma de todos os contratos</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-green-200 shadow-sm">
            <p class="text-xs text-slate-500 uppercase font-medium">Total Medido (período)</p>
            <p class="text-2xl font-bold text-green-700 mt-1">R$ {{ number_format($valorTotalMedido, 2, ',', '.') }}</p>
            <div class="mt-2 w-full bg-slate-100 rounded-full h-2">
                <div class="h-2 rounded-full {{ $percentualGlobal >= 80 ? 'bg-green-500' : ($percentualGlobal >= 40 ? 'bg-yellow-500' : 'bg-blue-500') }}"
                    style="width: {{ min($percentualGlobal, 100) }}%"></div>
            </div>
            <p class="text-xs text-slate-400 mt-1">{{ number_format($percentualGlobal, 1) }}% do contratado executado</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-blue-200 shadow-sm">
            <p class="text-xs text-slate-500 uppercase font-medium">Saldo a Medir</p>
            <p class="text-2xl font-bold {{ $saldoGlobal <= 0 ? 'text-red-600' : 'text-blue-700' }} mt-1">
                R$ {{ number_format($saldoGlobal, 2, ',', '.') }}
            </p>
            <p class="text-xs text-slate-400 mt-1">restante no total dos contratos</p>
        </div>
    </div>

    {{-- ══ GRÁFICOS ════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-6">

        {{-- PIZZA: obras por status + legenda completa --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">🗂 Distribuição por Status</h3>
            @if ($obrasPorStatus->sum('obras_count') > 0)
                <div class="flex justify-center mb-4">
                    <div style="position:relative;width:200px;height:200px;">
                        <canvas id="graficoPizza"></canvas>
                        {{-- Totalizador central --}}
                        <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;">
                            <span class="text-2xl font-bold text-slate-800">{{ $totalObras }}</span>
                            <span class="text-xs text-slate-400">obras</span>
                        </div>
                    </div>
                </div>

                {{-- LEGENDA com obras listadas --}}
                <div class="space-y-2 mt-2">
                    @foreach ($obrasPorStatus->where('obras_count', '>', 0) as $s)
                    <details class="group text-xs rounded-lg overflow-hidden border border-slate-100">
                        <summary class="flex items-center justify-between px-3 py-2 cursor-pointer hover:bg-slate-50 list-none">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-sm inline-block shrink-0" style="background-color: {{ $s->cor }}"></span>
                                <span class="font-medium text-slate-700">{{ $s->nome }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-800">{{ $s->obras_count }}</span>
                                <span class="text-slate-400">· {{ $totalObras > 0 ? number_format(($s->obras_count/$totalObras)*100, 0) : 0 }}%</span>
                                <svg class="w-3 h-3 text-slate-400 group-open:rotate-90 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </summary>
                        <div class="px-3 pb-2 pt-1 bg-slate-50 space-y-1">
                            @php
                                $obrasDoStatus = \App\Models\Obra::where('status_obra_id', $s->id)->with('contratos')->latest()->take(5)->get();
                            @endphp
                            @forelse($obrasDoStatus as $o)
                                <a href="{{ route('obras.show', $o) }}"
                                    class="flex items-center justify-between text-xs py-1 px-1 rounded hover:bg-white transition">
                                    <span class="text-slate-600 truncate max-w-[160px]">{{ $o->descricao }}</span>
                                    <span class="text-slate-400 shrink-0 ml-2">{{ $o->contratos->count() }} contrato(s)</span>
                                </a>
                            @empty
                                <p class="text-slate-400 italic py-1">Nenhuma obra neste status.</p>
                            @endforelse
                            @if ($s->obras_count > 5)
                                <a href="{{ route('obras.index') }}" class="text-blue-500 text-xs hover:underline pl-1">
                                    + {{ $s->obras_count - 5 }} mais →
                                </a>
                            @endif
                        </div>
                    </details>
                    @endforeach
                </div>
            @else
                <div class="flex items-center justify-center h-48 text-slate-400 text-sm flex-col gap-2">
                    <span class="text-3xl">🏗</span>
                    Nenhuma obra cadastrada ainda.
                </div>
            @endif
        </div>

        {{-- BARRAS: execução financeira por mês --}}
        <div class="lg:col-span-3 bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-700">📊 Execução Financeira por Mês</h3>
                <span class="text-xs text-slate-400">valores medidos no período</span>
            </div>
            @if ($execucaoPorMes->isNotEmpty())
                {{-- Totalizador acima --}}
                <div class="flex gap-4 mb-3">
                    <div>
                        <p class="text-xs text-slate-400">Total no período</p>
                        <p class="text-base font-bold text-blue-700">R$ {{ number_format($execucaoPorMes->sum('total'), 2, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Média mensal</p>
                        <p class="text-base font-bold text-slate-700">R$ {{ number_format($execucaoPorMes->avg('total'), 2, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Melhor mês</p>
                        <p class="text-base font-bold text-green-700">
                            {{ $execucaoPorMes->sortByDesc('total')->first()['label'] ?? '—' }}
                        </p>
                    </div>
                </div>
                <div style="position:relative;flex:1;min-height:200px;">
                    <canvas id="graficoBarras"></canvas>
                </div>
            @else
                <div class="flex items-center justify-center flex-1 min-h-48 text-slate-400 text-sm flex-col gap-2">
                    <span class="text-3xl">📊</span>
                    Nenhuma medição no período.
                </div>
            @endif
        </div>
    </div>

    {{-- ══ GRÁFICO DE BARRAS HORIZONTAL — obras por status com valor --}}
    @if ($obrasPorStatus->sum('obras_count') > 0)
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-slate-700">📈 Obras por Status — visão de execução financeira</h3>
            <a href="{{ route('obras.index') }}" class="text-xs text-blue-500 hover:underline">Ver todas as obras →</a>
        </div>
        <div style="position:relative;height:{{ max(150, $obrasPorStatus->where('obras_count', '>', 0)->count() * 48) }}px;">
            <canvas id="graficoHorizontal"></canvas>
        </div>
    </div>
    @endif

    {{-- ══ ALERTAS ═════════════════════════════════════════════════ --}}
    @if ($totalAlertas > 0)
    <div class="bg-white rounded-xl shadow-sm border border-red-100 mb-6">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
            <span class="text-base">⚠️</span>
            <h3 class="text-sm font-semibold text-slate-700">Alertas que requerem atenção</h3>
            <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">{{ $totalAlertas }}</span>
        </div>

        {{-- tabs --}}
        <div class="border-b border-slate-100 flex flex-wrap gap-1 px-4 py-2">
            @php
                $abasAlerta = [
                    ['key' => 'contratos_vencidos', 'label' => 'Contratos vencidos',    'count' => $contratosVencidos->count(),       'bg' => 'bg-red-50 text-red-700 border-red-200',    'badge' => 'bg-red-100 text-red-700'],
                    ['key' => 'contratos_vencendo', 'label' => 'Vencendo em 30 dias',   'count' => $contratosVencendo->count(),       'bg' => 'bg-yellow-50 text-yellow-700 border-yellow-200', 'badge' => 'bg-yellow-100 text-yellow-700'],
                    ['key' => 'convenios_vencidos', 'label' => 'Convênios vencidos',    'count' => $conveniosVencidos->count(),       'bg' => 'bg-orange-50 text-orange-700 border-orange-200',  'badge' => 'bg-orange-100 text-orange-700'],
                    ['key' => 'convenios_vencendo', 'label' => 'Convênios atenção',     'count' => $conveniosVencendo->count(),       'bg' => 'bg-amber-50 text-amber-700 border-amber-200',   'badge' => 'bg-amber-100 text-amber-700'],
                    ['key' => 'sem_medicao',        'label' => 'Sem medição (60 dias)', 'count' => $obrasSemMedicaoRecente->count(), 'bg' => 'bg-blue-50 text-blue-700 border-blue-200',      'badge' => 'bg-blue-100 text-blue-700'],
                    ['key' => 'processos_pendentes', 'label' => 'Processos pendentes', 'count' => $processosComPendencia->count(),  'bg' => 'bg-purple-50 text-purple-700 border-purple-200', 'badge' => 'bg-purple-100 text-purple-700'],
                ];
            @endphp
            @foreach ($abasAlerta as $tab)
                @if ($tab['count'] > 0)
                <button type="button"
                    @click="abaAlerta = '{{ $tab['key'] }}'"
                    :class="abaAlerta === '{{ $tab['key'] }}' ? '{{ $tab['bg'] }} border font-semibold' : 'text-slate-500 hover:bg-slate-50 border border-transparent'"
                    class="px-3 py-1.5 text-xs rounded-lg flex items-center gap-1.5 transition">
                    {{ $tab['label'] }}
                    <span class="px-1.5 py-0.5 rounded-full text-xs {{ $tab['badge'] }}">{{ $tab['count'] }}</span>
                </button>
                @endif
            @endforeach
        </div>

        <div class="p-4">
            {{-- CONTRATOS VENCIDOS --}}
            <div x-show="abaAlerta === 'contratos_vencidos'" x-cloak>
                @forelse($contratosVencidos as $c)
                <div class="flex items-center gap-3 py-2.5 border-b last:border-0 text-sm">
                    <span class="w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
                    <div class="flex-1">
                        <p class="font-medium text-slate-800">{{ $c->numero_contrato_ano ?? 'Contrato #'.$c->id }}</p>
                        <p class="text-xs text-slate-500">{{ $c->obra->descricao ?? '—' }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700 font-medium">Venceu {{ $c->vigencia_contrato->format('d/m/Y') }}</span>
                        <p class="text-xs text-red-400 mt-0.5">{{ $c->vigencia_contrato->diffForHumans() }}</p>
                    </div>
                    <a href="{{ route('contratos.show', $c) }}" class="px-2 py-1 text-xs rounded border border-slate-200 text-slate-600 hover:bg-slate-50 shrink-0">Ver →</a>
                </div>
                @empty
                <p class="text-sm text-slate-400 italic">Nenhum contrato vencido.</p>
                @endforelse
            </div>

            {{-- CONTRATOS VENCENDO --}}
            <div x-show="abaAlerta === 'contratos_vencendo'" x-cloak>
                @forelse($contratosVencendo as $c)
                <div class="flex items-center gap-3 py-2.5 border-b last:border-0 text-sm">
                    <span class="w-2 h-2 rounded-full bg-yellow-400 shrink-0"></span>
                    <div class="flex-1">
                        <p class="font-medium text-slate-800">{{ $c->numero_contrato_ano ?? 'Contrato #'.$c->id }}</p>
                        <p class="text-xs text-slate-500">{{ $c->obra->descricao ?? '—' }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="px-2 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-700 font-medium">Vence {{ $c->vigencia_contrato->format('d/m/Y') }}</span>
                        <p class="text-xs text-yellow-500 mt-0.5">{{ $c->vigencia_contrato->diffForHumans() }}</p>
                    </div>
                    <a href="{{ route('contratos.show', $c) }}" class="px-2 py-1 text-xs rounded border border-slate-200 text-slate-600 hover:bg-slate-50 shrink-0">Ver →</a>
                </div>
                @empty
                <p class="text-sm text-slate-400 italic">Nenhum contrato vencendo em breve.</p>
                @endforelse
            </div>

            {{-- CONVÊNIOS VENCIDOS --}}
            <div x-show="abaAlerta === 'convenios_vencidos'" x-cloak>
                @forelse($conveniosVencidos as $cv)
                <div class="flex items-center gap-3 py-2.5 border-b last:border-0 text-sm">
                    <span class="w-2 h-2 rounded-full bg-orange-500 shrink-0"></span>
                    <div class="flex-1">
                        <p class="font-medium text-slate-800">{{ $cv->numero_convenio_ano ?? 'Convênio #'.$cv->id }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ Str::limit($cv->descricao, 60) }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="px-2 py-0.5 rounded-full text-xs bg-orange-100 text-orange-700 font-medium">Venceu {{ $cv->vigencia->format('d/m/Y') }}</span>
                    </div>
                    <a href="{{ route('convenios.show', $cv) }}" class="px-2 py-1 text-xs rounded border border-slate-200 text-slate-600 hover:bg-slate-50 shrink-0">Ver →</a>
                </div>
                @empty
                <p class="text-sm text-slate-400 italic">Nenhum convênio vencido.</p>
                @endforelse
            </div>

            {{-- CONVÊNIOS VENCENDO --}}
            <div x-show="abaAlerta === 'convenios_vencendo'" x-cloak>
                @forelse($conveniosVencendo as $cv)
                <div class="flex items-center gap-3 py-2.5 border-b last:border-0 text-sm">
                    <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
                    <div class="flex-1">
                        <p class="font-medium text-slate-800">{{ $cv->numero_convenio_ano ?? 'Convênio #'.$cv->id }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ Str::limit($cv->descricao, 60) }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700 font-medium">Vence {{ $cv->vigencia->format('d/m/Y') }}</span>
                        <p class="text-xs text-amber-500 mt-0.5">{{ $cv->vigencia->diffForHumans() }}</p>
                    </div>
                    <a href="{{ route('convenios.show', $cv) }}" class="px-2 py-1 text-xs rounded border border-slate-200 text-slate-600 hover:bg-slate-50 shrink-0">Ver →</a>
                </div>
                @empty
                <p class="text-sm text-slate-400 italic">Nenhum convênio vencendo em breve.</p>
                @endforelse
            </div>

            {{-- SEM MEDIÇÃO --}}
            <div x-show="abaAlerta === 'sem_medicao'" x-cloak>
                @forelse($obrasSemMedicaoRecente as $obra)
                <div class="flex items-center gap-3 py-2.5 border-b last:border-0 text-sm">
                    <span class="w-2 h-2 rounded-full bg-blue-400 shrink-0"></span>
                    <div class="flex-1">
                        <p class="font-medium text-slate-800">{{ Str::limit($obra->descricao, 55) }}</p>
                        <p class="text-xs text-slate-500">{{ $obra->endereco ?? '—' }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700 shrink-0">+60 dias sem medição</span>
                    <a href="{{ route('obras.show', $obra) }}" class="px-2 py-1 text-xs rounded border border-slate-200 text-slate-600 hover:bg-slate-50 shrink-0">Ver →</a>
                </div>
                @empty
                <p class="text-sm text-slate-400 italic">Todas as obras em execução têm medições recentes.</p>
                @endforelse
            </div>

            {{-- PROCESSOS PENDENTES --}}
            <div x-show="abaAlerta === 'processos_pendentes'" x-cloak>
                @forelse($processosComPendencia as $p)
                <div class="flex items-center gap-3 py-2.5 border-b last:border-0 text-sm">
                    <span class="w-2 h-2 rounded-full bg-purple-500 shrink-0"></span>
                    <div class="flex-1">
                        <p class="font-medium text-slate-800">{{ $p->processo_numero }} — {{ Str::limit($p->requerente, 35) }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ Str::limit($p->motivo_pendencia, 70) }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium text-white shrink-0"
                        style="background-color: {{ $p->faseAtual->cor ?? '#64748b' }}">
                        {{ $p->faseAtual->nome ?? '—' }}
                    </span>
                    <a href="{{ route('processos.show', $p) }}" class="px-2 py-1 text-xs rounded border border-slate-200 text-slate-600 hover:bg-slate-50 shrink-0">Ver →</a>
                </div>
                @empty
                <p class="text-sm text-slate-400 italic">Nenhum processo com pendência registrada.</p>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    {{-- ══ OBRAS RECENTES ══════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">🏗 Obras Recentes</h3>
            <a href="{{ route('obras.index') }}" class="text-xs text-blue-500 hover:underline">Ver todas →</a>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Obra</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Endereço</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Contratos</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($ultimasObras as $obra)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-800 truncate max-w-[200px]">{{ $obra->descricao }}</p>
                        @if ($obra->processo_execucao)
                            <p class="text-xs text-slate-400">{{ $obra->processo_execucao }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500 hidden md:table-cell max-w-[180px]">
                        <span class="truncate block">{{ $obra->endereco ?? '—' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium text-white whitespace-nowrap"
                            style="background-color: {{ $obra->status->cor ?? '#6c757d' }}">
                            {{ $obra->status->nome }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center hidden md:table-cell">
                        <span class="text-xs font-semibold {{ $obra->contratos->count() > 0 ? 'text-slate-700' : 'text-slate-300' }}">
                            {{ $obra->contratos->count() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('obras.show', $obra) }}"
                            class="px-3 py-1 text-xs rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100 whitespace-nowrap">
                            Ver →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-10 text-slate-400 text-sm">
                        <p class="text-2xl mb-2">🏗</p>
                        Nenhuma obra cadastrada.
                        @if(auth()->user()->perfil !== 'operador')
                            <br><a href="{{ route('obras.create') }}" class="text-xs text-blue-500 hover:underline mt-2 inline-block">➕ Cadastrar primeira obra</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
    🗂️ PROCESSOS ADMINISTRATIVOS
    ══════════════════════════════════════════════════════════════ --}}
    <div class="border-t border-slate-200 mt-8 pt-6">
        <h2 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
            🗂️ Processos Administrativos
        </h2>

        {{-- ══ KPIs ═══════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <a href="{{ route('processos.index') }}" class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm hover:border-blue-300 transition block">
                <p class="text-xs text-slate-500 uppercase font-medium">Total de Processos</p>
                <p class="text-3xl font-bold text-blue-600 mt-1">{{ $totalProcessos }}</p>
                <p class="text-xs text-slate-400 mt-1">cadastrados no sistema</p>
            </a>
            <a href="{{ route('processos.index', ['situacao' => 'aberto']) }}" class="bg-white rounded-xl p-5 border border-green-200 shadow-sm hover:border-green-300 transition block">
                <p class="text-xs text-slate-500 uppercase font-medium">Abertos</p>
                <p class="text-3xl font-bold text-green-600 mt-1">{{ $processosAbertos }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ $totalProcessos > 0 ? number_format(($processosAbertos/$totalProcessos)*100,0) : 0 }}% do total</p>
            </a>
            <a href="{{ route('processos.index', ['situacao' => 'arquivado']) }}" class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm hover:border-slate-300 transition block">
                <p class="text-xs text-slate-500 uppercase font-medium">Arquivados</p>
                <p class="text-3xl font-bold text-slate-500 mt-1">{{ $processosArquivados }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ $totalProcessos > 0 ? number_format(($processosArquivados/$totalProcessos)*100,0) : 0 }}% do total</p>
            </a>
            <div class="bg-white rounded-xl p-5 border border-amber-200 shadow-sm">
                <p class="text-xs text-slate-500 uppercase font-medium">A Classificar</p>
                <p class="text-3xl font-bold text-amber-600 mt-1">{{ $processosAClassificar }}</p>
                <p class="text-xs text-slate-400 mt-1">importados sem fase estruturada</p>
            </div>
        </div>

        {{-- ══ GRÁFICOS ═══════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-6">

            {{-- PIZZA: processos por fase — resposta direta ao pedido do Secretário --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-slate-700 mb-4">📍 Processos por Fase</h3>
                @if ($processosPorFase->sum('processos_count') > 0)
                    <div class="flex justify-center mb-4">
                        <div style="position:relative;width:200px;height:200px;">
                            <canvas id="graficoPizzaProcessos"></canvas>
                            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;">
                                <span class="text-2xl font-bold text-slate-800">{{ $totalProcessos }}</span>
                                <span class="text-xs text-slate-400">processos</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 mt-2">
                        @foreach ($processosPorFase->where('processos_count', '>', 0) as $f)
                        <details class="group text-xs rounded-lg overflow-hidden border border-slate-100">
                            <summary class="flex items-center justify-between px-3 py-2 cursor-pointer hover:bg-slate-50 list-none">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-sm inline-block shrink-0" style="background-color: {{ $f->cor }}"></span>
                                    <span class="font-medium text-slate-700">{{ $f->nome }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-800">{{ $f->processos_count }}</span>
                                    <span class="text-slate-400">· {{ $totalProcessos > 0 ? number_format(($f->processos_count/$totalProcessos)*100, 0) : 0 }}%</span>
                                    <svg class="w-3 h-3 text-slate-400 group-open:rotate-90 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </div>
                            </summary>
                            <div class="px-3 pb-2 pt-1 bg-slate-50 space-y-1">
                                @php
                                    $processosDaFase = \App\Models\Processo::where('fase_atual_id', $f->id)->latest()->take(5)->get();
                                @endphp
                                @forelse($processosDaFase as $p)
                                    <a href="{{ route('processos.show', $p) }}"
                                        class="flex items-center justify-between text-xs py-1 px-1 rounded hover:bg-white transition">
                                        <span class="text-slate-600 truncate max-w-[160px]">{{ $p->processo_numero }} — {{ $p->requerente }}</span>
                                    </a>
                                @empty
                                    <p class="text-slate-400 italic py-1">Nenhum processo nesta fase.</p>
                                @endforelse
                                @if ($f->processos_count > 5)
                                    <a href="{{ route('processos.index', ['fase_atual_id' => $f->id]) }}" class="text-blue-500 text-xs hover:underline pl-1">
                                        + {{ $f->processos_count - 5 }} mais →
                                    </a>
                                @endif
                            </div>
                        </details>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center justify-center h-48 text-slate-400 text-sm flex-col gap-2">
                        <span class="text-3xl">🗂️</span>
                        Nenhum processo cadastrado ainda.
                    </div>
                @endif
            </div>

            {{-- BARRAS HORIZONTAL: processos por tipo --}}
            <div class="lg:col-span-3 bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-700">📋 Processos por Tipo</h3>
                    <a href="{{ route('processos.index') }}" class="text-xs text-blue-500 hover:underline">Ver todos os processos →</a>
                </div>
                @if ($processosPorTipo->sum('processos_count') > 0)
                    <div style="position:relative;flex:1;min-height:{{ max(200, $processosPorTipo->where('processos_count', '>', 0)->count() * 32) }}px;">
                        <canvas id="graficoHorizontalProcessos"></canvas>
                    </div>
                @else
                    <div class="flex items-center justify-center flex-1 min-h-48 text-slate-400 text-sm flex-col gap-2">
                        <span class="text-3xl">📋</span>
                        Nenhum processo cadastrado ainda.
                    </div>
                @endif
            </div>
        </div>

        {{-- ══ PROCESSOS RECENTES ═════════════════════════════════════ --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">🗂️ Processos Recentes</h3>
                <a href="{{ route('processos.index') }}" class="text-xs text-blue-500 hover:underline">Ver todos →</a>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Processo</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Tipo</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Fase Atual</th>
                        <th class="px-4 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($ultimosProcessos as $p)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">{{ $p->processo_numero }}</p>
                            <p class="text-xs text-slate-400 truncate max-w-[200px]">{{ $p->requerente }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500 hidden md:table-cell">
                            {{ $p->tipoProcesso->nome ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium text-white whitespace-nowrap"
                                style="background-color: {{ $p->faseAtual->cor ?? '#6c757d' }}">
                                {{ $p->faseAtual->nome ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('processos.show', $p) }}"
                                class="px-3 py-1 text-xs rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100 whitespace-nowrap">
                                Ver →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-10 text-slate-400 text-sm">
                            <p class="text-2xl mb-2">🗂️</p>
                            Nenhum processo cadastrado.
                            @if(auth()->user()->perfil !== 'operador' && auth()->user()->perfil !== 'secretario')
                                <br><a href="{{ route('processos.create') }}" class="text-xs text-blue-500 hover:underline mt-2 inline-block">➕ Cadastrar primeiro processo</a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {

    const fmt = (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    @if ($obrasPorStatus->sum('obras_count') > 0)
    // ── Pizza --
    const pizzaCtx = document.getElementById('graficoPizza');
    if (pizzaCtx) {
        const labels = @json($obrasPorStatus->where('obras_count', '>', 0)->pluck('nome')->values());
        const counts = @json($obrasPorStatus->where('obras_count', '>', 0)->pluck('obras_count')->values());
        const cores  = @json($obrasPorStatus->where('obras_count', '>', 0)->pluck('cor')->values());
        const total  = counts.reduce((a,b) => a+b, 0);

        new Chart(pizzaCtx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{ data: counts, backgroundColor: cores, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.label}: ${ctx.parsed} obra(s) · ${Math.round(ctx.parsed/total*100)}%`
                        }
                    }
                }
            }
        });
    }

    // ── Barras horizontal — status com quantidade --
    const horizCtx = document.getElementById('graficoHorizontal');
    if (horizCtx) {
        const statusLabels = @json($obrasPorStatus->where('obras_count', '>', 0)->pluck('nome')->values());
        const statusCounts = @json($obrasPorStatus->where('obras_count', '>', 0)->pluck('obras_count')->values());
        const statusCores  = @json($obrasPorStatus->where('obras_count', '>', 0)->pluck('cor')->values());

        new Chart(horizCtx, {
            type: 'bar',
            data: {
                labels: statusLabels,
                datasets: [{
                    label: 'Obras',
                    data: statusCounts,
                    backgroundColor: statusCores.map(c => c + 'cc'),
                    borderColor: statusCores,
                    borderWidth: 1.5,
                    borderRadius: 5,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.parsed.x} obra(s)`
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                    y: { grid: { display: false } }
                }
            }
        });
    }
    @endif

    @if ($execucaoPorMes->isNotEmpty())
    // ── Barras verticais — execução por mês --
    const barrasCtx = document.getElementById('graficoBarras');
    if (barrasCtx) {
        const meses  = @json($execucaoPorMes->pluck('label'));
        const totais = @json($execucaoPorMes->pluck('total'));
        const maxVal = Math.max(...totais);

        new Chart(barrasCtx, {
            type: 'bar',
            data: {
                labels: meses,
                datasets: [{
                    label: 'Valor Medido',
                    data: totais,
                    backgroundColor: totais.map(v => v === maxVal ? 'rgba(22,163,74,0.8)' : 'rgba(59,130,246,0.6)'),
                    borderColor:     totais.map(v => v === maxVal ? 'rgb(22,163,74)' : 'rgb(59,130,246)'),
                    borderWidth: 1.5,
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ' ' + fmt(ctx.parsed.y)
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            callback: (v) => {
                                if (v >= 1000000) return 'R$ ' + (v/1000000).toFixed(1) + 'M';
                                if (v >= 1000)    return 'R$ ' + (v/1000).toFixed(0) + 'K';
                                return 'R$ ' + v;
                            }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { autoSkip: false, maxRotation: 45 }
                    }
                }
            }
        });
    }
    @endif

    @if ($processosPorFase->sum('processos_count') > 0)
    // ── Pizza — processos por fase --
    const pizzaProcessosCtx = document.getElementById('graficoPizzaProcessos');
    if (pizzaProcessosCtx) {
        const labels = @json($processosPorFase->where('processos_count', '>', 0)->pluck('nome')->values());
        const counts = @json($processosPorFase->where('processos_count', '>', 0)->pluck('processos_count')->values());
        const cores  = @json($processosPorFase->where('processos_count', '>', 0)->pluck('cor')->values());
        const totalP = counts.reduce((a,b) => a+b, 0);

        new Chart(pizzaProcessosCtx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{ data: counts, backgroundColor: cores, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.label}: ${ctx.parsed} processo(s) · ${Math.round(ctx.parsed/totalP*100)}%`
                        }
                    }
                }
            }
        });
    }
    @endif

    @if ($processosPorTipo->sum('processos_count') > 0)
    // ── Barras horizontal — processos por tipo --
    const horizProcessosCtx = document.getElementById('graficoHorizontalProcessos');
    if (horizProcessosCtx) {
        const tipos = @json($processosPorTipo->where('processos_count', '>', 0)->sortByDesc('processos_count')->values());
        const tipoLabels = tipos.map(t => t.nome);
        const tipoCounts = tipos.map(t => t.processos_count);

        new Chart(horizProcessosCtx, {
            type: 'bar',
            data: {
                labels: tipoLabels,
                datasets: [{
                    label: 'Processos',
                    data: tipoCounts,
                    backgroundColor: 'rgba(99,102,241,0.7)',
                    borderColor: 'rgb(99,102,241)',
                    borderWidth: 1.5,
                    borderRadius: 5,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.parsed.x} processo(s)`
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                    y: { grid: { display: false }, ticks: { autoSkip: false } }
                }
            }
        });
    }
    @endif

});
</script>
@endpush