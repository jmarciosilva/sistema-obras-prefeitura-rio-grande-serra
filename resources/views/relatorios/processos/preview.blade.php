@extends('layouts.app')

@section('title', 'Pré-visualização — Relatório de Processos')
@section('subtitle', 'Confira os dados antes de exportar')

@section('content')

<div class="space-y-6 pb-24">

    {{-- BREADCRUMB + VOLTAR --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm text-slate-600">
            <a href="{{ route('relatorios.processos.index') }}" class="hover:text-slate-900">Relatórios de Processos</a>
            <span>›</span>
            <span class="text-slate-900 font-medium">Pré-visualização</span>
        </div>
        <a href="{{ route('relatorios.processos.index') }}"
            class="px-4 py-2 text-sm border border-slate-300 rounded-lg text-slate-700 bg-white hover:bg-slate-50">
            ← Voltar aos filtros
        </a>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Processos no filtro</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ $totalFiltrado }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Abertos</p>
            <p class="text-2xl font-bold text-green-700 mt-1">{{ $abertosFiltrado }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Com Pendência</p>
            <p class="text-2xl font-bold text-red-700 mt-1">{{ $comPendencia->count() }}</p>
        </div>
    </div>

    {{-- ═══ GERAL ═══ --}}
    @if ($filtros['tipo'] === 'geral' || empty($filtros['tipo']))
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">📋 Relatório Geral</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Processo</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Tipo</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Fase</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Responsável</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Situação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($processos as $p)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2.5">
                            <p class="font-medium text-slate-800">{{ $p->processo_numero }}</p>
                            <p class="text-xs text-slate-400">{{ $p->requerente }}</p>
                        </td>
                        <td class="px-4 py-2.5 text-slate-600">{{ $p->tipoProcesso->nome ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium text-white"
                                style="background-color: {{ $p->faseAtual->cor ?? '#64748b' }}">
                                {{ $p->faseAtual->nome ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-slate-600">{{ $p->responsavelTecnico->nome ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-slate-600">{{ $p->situacao_label }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-8 text-slate-400">Nenhum processo encontrado com os filtros aplicados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ═══ POR FASE ═══ --}}
    @if ($filtros['tipo'] === 'por_fase')
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">📍 Processos por Fase</h3>
        </div>
        <div class="p-5 space-y-3">
            @forelse($porFase as $f)
            <div>
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="flex items-center gap-2 font-medium text-slate-700">
                        <span class="w-3 h-3 rounded-sm inline-block" style="background-color: {{ $f['cor'] }}"></span>
                        {{ $f['nome'] }}
                    </span>
                    <span class="text-slate-500">{{ $f['total'] }} ({{ $porFase->sum('total') > 0 ? number_format(($f['total']/$porFase->sum('total'))*100, 1) : 0 }}%)</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2">
                    <div class="h-2 rounded-full" style="width: {{ $porFase->sum('total') > 0 ? ($f['total']/$porFase->sum('total'))*100 : 0 }}%; background-color: {{ $f['cor'] }}"></div>
                </div>
            </div>
            @empty
            <p class="text-center py-8 text-slate-400">Nenhum processo encontrado com os filtros aplicados.</p>
            @endforelse
        </div>
    </div>
    @endif

    {{-- ═══ POR TIPO ═══ --}}
    @if ($filtros['tipo'] === 'por_tipo')
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">🏷️ Processos por Tipo</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Tipo</th>
                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-slate-500 uppercase">Quantidade</th>
                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 uppercase">% do Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($porTipo as $t)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2.5 font-medium text-slate-800">{{ $t['nome'] }}</td>
                    <td class="px-4 py-2.5 text-center text-slate-600">{{ $t['total'] }}</td>
                    <td class="px-4 py-2.5 text-right text-slate-600">
                        {{ $porTipo->sum('total') > 0 ? number_format(($t['total']/$porTipo->sum('total'))*100, 1) : 0 }}%
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center py-8 text-slate-400">Nenhum processo encontrado com os filtros aplicados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    {{-- ═══ PENDÊNCIAS ═══ --}}
    @if ($filtros['tipo'] === 'pendencias')
    <div class="bg-white rounded-xl shadow-sm border border-red-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">⚠️ Processos com Pendência</h3>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($comPendencia as $p)
            <div class="flex items-center gap-3 px-5 py-3 text-sm">
                <span class="w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
                <div class="flex-1">
                    <p class="font-medium text-slate-800">{{ $p->processo_numero }} — {{ $p->requerente }}</p>
                    <p class="text-xs text-slate-500">{{ $p->motivo_pendencia }}</p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium text-white shrink-0"
                    style="background-color: {{ $p->faseAtual->cor ?? '#64748b' }}">
                    {{ $p->faseAtual->nome ?? '—' }}
                </span>
            </div>
            @empty
            <p class="text-center py-8 text-slate-400">Nenhum processo com pendência encontrado com os filtros aplicados.</p>
            @endforelse
        </div>
    </div>
    @endif

    {{-- ═══ POR RESPONSÁVEL ═══ --}}
    @if ($filtros['tipo'] === 'por_responsavel')
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">👷 Processos por Responsável Técnico</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Responsável</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Registro</th>
                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-slate-500 uppercase">Processos</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($porResponsavel as $r)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2.5 font-medium text-slate-800">{{ $r['responsavel']->nome }}</td>
                    <td class="px-4 py-2.5 text-slate-500">{{ $r['responsavel']->registro ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-center text-slate-600">{{ $r['total'] }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center py-8 text-slate-400">Nenhum responsável técnico com processos encontrado com os filtros aplicados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    {{-- BARRA DE EXPORTAÇÃO FIXA --}}
    <div class="fixed bottom-0 left-0 right-0 md:left-64 bg-white border-t border-slate-200 shadow-lg px-6 py-3 flex gap-3 justify-end z-30">
        <span class="self-center text-xs text-slate-400 mr-auto">Exportar com os mesmos filtros aplicados nesta prévia:</span>
        <a href="{{ route('relatorios.processos.pdf', request()->query()) }}"
            class="flex items-center gap-2 px-5 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
            📄 Exportar PDF
        </a>
        <a href="{{ route('relatorios.processos.excel', request()->query()) }}"
            class="flex items-center gap-2 px-5 py-2 text-sm font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            📊 Exportar Excel
        </a>
    </div>

</div>

@endsection
