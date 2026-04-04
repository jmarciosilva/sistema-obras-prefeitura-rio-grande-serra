@extends('layouts.app')

@section('title', 'Pré-visualização do Relatório')
@section('subtitle', 'Confira os dados antes de exportar')

@section('content')

<div class="space-y-6" x-data="{ ajuda: false }">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-600 mb-2">
                <a href="{{ route('relatorios.index') }}" class="hover:text-slate-900">Relatórios</a>
                <span>›</span>
                <span class="font-medium text-slate-900">Pré-visualização</span>
            </div>
            <h1 class="text-xl font-semibold text-slate-800">📋 Pré-visualização do Relatório</h1>
        </div>
        <div class="flex gap-2 flex-wrap">
            <button @click="ajuda = true"
                class="px-4 py-2 text-sm bg-amber-100 text-amber-800 border border-amber-200 rounded-lg hover:bg-amber-200">
                ❓ Ajuda
            </button>
            <a href="{{ route('relatorios.index') }}"
                class="px-4 py-2 text-sm bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">
                ← Voltar
            </a>
        </div>
    </div>

    {{-- BANNER DE FILTROS ATIVOS --}}
    @php
        $filtrosAtivos = array_filter($filtros, fn($v) => !empty($v) && $v !== 'geral');
    @endphp
    @if (count($filtrosAtivos) > 0)
        <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-sm text-blue-700 flex flex-wrap gap-3 items-center">
            <span class="font-medium">🔍 Filtros ativos:</span>
            @foreach ($filtrosAtivos as $chave => $valor)
                <span class="px-2 py-0.5 bg-blue-100 rounded text-xs">{{ $chave }}: {{ $valor }}</span>
            @endforeach
        </div>
    @endif

    {{-- KPIs GERAIS --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs text-slate-500">Obras no relatório</p>
            <p class="text-2xl font-bold text-slate-800">{{ $obras->count() }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs text-slate-500">Valor Contratado</p>
            <p class="text-base font-bold text-blue-700">R$ {{ number_format($valorContratadoTotal, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs text-slate-500">Valor Medido</p>
            <p class="text-base font-bold text-green-700">R$ {{ number_format($valorMedidoTotal, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs text-slate-500">% Geral Executado</p>
            <p class="text-2xl font-bold {{ $percentualGeral >= 80 ? 'text-green-600' : ($percentualGeral >= 40 ? 'text-yellow-600' : 'text-blue-600') }}">
                {{ number_format($percentualGeral, 1) }}%
            </p>
        </div>
    </div>

    {{-- GRÁFICO: OBRAS POR STATUS --}}
    @if ($porStatus->isNotEmpty())
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h3 class="font-semibold text-slate-700 mb-4 text-sm uppercase tracking-wide">🚦 Obras por Status</h3>
        <div class="flex flex-wrap gap-3">
            @foreach ($porStatus as $item)
                @php $pct = $obras->count() > 0 ? round($item['total'] / $obras->count() * 100) : 0; @endphp
                <div class="flex-1 min-w-[140px]">
                    <div class="flex justify-between text-xs text-slate-600 mb-1">
                        <span>{{ $item['nome'] }}</span>
                        <span class="font-semibold">{{ $item['total'] }}</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-2 rounded-full" style="width: {{ $pct }}%; background-color: {{ $item['cor'] }}"></div>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5 text-right">{{ $pct }}%</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- TABELA: EXECUÇÃO FINANCEIRA --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-semibold text-slate-700 text-sm uppercase tracking-wide">💰 Execução Financeira por Obra</h3>
            <span class="text-xs text-slate-400">{{ $execucaoFinanceira->count() }} obra(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Obra</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500">Contratado</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500">Medido</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500">Saldo</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500">Execução</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($execucaoFinanceira as $item)
                        @php
                            $o   = $item['obra'];
                            $pct = (float) $item['percentual'];
                            $cor = $pct >= 80 ? '#16a34a' : ($pct >= 40 ? '#d97706' : '#2563eb');
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 max-w-xs">
                                <p class="font-medium text-slate-800 truncate text-xs">{{ $o->descricao }}</p>
                                @if ($o->endereco)
                                    <p class="text-xs text-slate-400 truncate">📍 {{ $o->endereco }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs text-white"
                                    style="background-color: {{ $o->status->cor ?? '#64748b' }}">
                                    {{ $o->status->nome ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-xs font-medium text-slate-700">
                                R$ {{ number_format($item['valor_contratado'], 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-xs font-semibold text-green-700">
                                R$ {{ number_format($item['valor_medido'], 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-xs {{ $item['saldo'] <= 0 ? 'text-red-600 font-semibold' : 'text-slate-500' }}">
                                R$ {{ number_format($item['saldo'], 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-xs font-bold" style="color: {{ $cor }}">{{ number_format($pct, 1) }}%</span>
                                    <div class="w-20 bg-slate-200 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full" style="width: {{ min($pct, 100) }}%; background-color: {{ $cor }}"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-400">Nenhuma obra encontrada com os filtros aplicados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABELA: CONTRATOS VENCENDO --}}
    @if ($contratosVencendo->isNotEmpty() || $contratosVencidos->isNotEmpty())
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="font-semibold text-slate-700 text-sm uppercase tracking-wide">⏳ Contratos — Vencimentos</h3>
        </div>

        @if ($contratosVencendo->isNotEmpty())
        <div class="px-6 py-3 bg-amber-50 border-b border-amber-200">
            <p class="text-xs font-semibold text-amber-700">⚠️ Vencendo nos próximos {{ $vencimentoDias }} dias ({{ $contratosVencendo->count() }} contrato(s))</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs text-slate-500">Contrato</th>
                        <th class="px-4 py-2 text-left text-xs text-slate-500">Obra</th>
                        <th class="px-4 py-2 text-left text-xs text-slate-500">Empresa</th>
                        <th class="px-4 py-2 text-center text-xs text-slate-500">Vigência</th>
                        <th class="px-4 py-2 text-center text-xs text-slate-500">Dias restantes</th>
                        <th class="px-4 py-2 text-right text-xs text-slate-500">Valor</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($contratosVencendo as $item)
                        @php
                            $c   = $item['contrato'];
                            $dias = $item['dias_restantes'];
                            $urgencia = $dias <= 10 ? 'bg-red-50' : ($dias <= 30 ? 'bg-amber-50' : '');
                        @endphp
                        <tr class="{{ $urgencia }} hover:bg-slate-50">
                            <td class="px-4 py-2 text-xs font-medium text-slate-800">{{ $c->numero_contrato_ano ?? '#'.$c->id }}</td>
                            <td class="px-4 py-2 text-xs text-slate-600 max-w-xs truncate">{{ Str::limit($c->obra->descricao ?? '—', 45) }}</td>
                            <td class="px-4 py-2 text-xs text-slate-600">{{ $c->empresa->razao_social ?? '—' }}</td>
                            <td class="px-4 py-2 text-center text-xs text-slate-600">{{ $c->vigencia_contrato?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $dias <= 10 ? 'bg-red-100 text-red-700' : ($dias <= 30 ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                                    {{ $dias }} dias
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right text-xs font-semibold text-slate-700">
                                R$ {{ number_format($c->valor_contrato ?? 0, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if ($contratosVencidos->isNotEmpty())
        <div class="px-6 py-3 bg-red-50 border-t border-red-200">
            <p class="text-xs font-semibold text-red-700">🔴 Contratos Vencidos ({{ $contratosVencidos->count() }})</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs text-slate-500">Contrato</th>
                        <th class="px-4 py-2 text-left text-xs text-slate-500">Obra</th>
                        <th class="px-4 py-2 text-left text-xs text-slate-500">Empresa</th>
                        <th class="px-4 py-2 text-center text-xs text-slate-500">Venceu em</th>
                        <th class="px-4 py-2 text-right text-xs text-slate-500">Valor</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($contratosVencidos as $c)
                        <tr class="bg-red-50/30 hover:bg-red-50">
                            <td class="px-4 py-2 text-xs font-medium text-red-700">{{ $c->numero_contrato_ano ?? '#'.$c->id }}</td>
                            <td class="px-4 py-2 text-xs text-slate-600 max-w-xs truncate">{{ Str::limit($c->obra->descricao ?? '—', 45) }}</td>
                            <td class="px-4 py-2 text-xs text-slate-600">{{ $c->empresa->razao_social ?? '—' }}</td>
                            <td class="px-4 py-2 text-center text-xs text-red-600 font-semibold">{{ $c->vigencia_contrato?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-right text-xs font-semibold text-slate-700">
                                R$ {{ number_format($c->valor_contrato ?? 0, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

    {{-- RANKING POR EMPRESA --}}
    @if ($porEmpresa->isNotEmpty())
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h3 class="font-semibold text-slate-700 text-sm uppercase tracking-wide mb-4">🏢 Ranking por Empresa</h3>
        @php $maxValor = $porEmpresa->max('valor_total'); @endphp
        <div class="space-y-3">
            @foreach ($porEmpresa as $i => $item)
                @php $largura = $maxValor > 0 ? round($item['valor_total'] / $maxValor * 100) : 0; @endphp
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-medium text-slate-700">
                            {{ ($i+1) }}. {{ Str::limit($item['empresa']->razao_social, 50) }}
                        </span>
                        <span class="text-slate-500">
                            {{ $item['total_contratos'] }} contrato(s) —
                            <strong class="text-blue-700">R$ {{ number_format($item['valor_total'], 0, ',', '.') }}</strong>
                        </span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-2 rounded-full bg-blue-500" style="width: {{ $largura }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- BOTÕES DE EXPORTAÇÃO FIXOS --}}
    <div class="sticky bottom-4 flex justify-center">
        <div class="bg-white border border-slate-200 shadow-lg rounded-2xl px-6 py-3 flex gap-3 items-center">
            <span class="text-xs text-slate-500">Exportar este relatório:</span>
            <a href="{{ route('relatorios.pdf') }}?{{ http_build_query($filtros) }}"
                class="flex items-center gap-2 px-5 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                📄 PDF com Gráficos
            </a>
            <a href="{{ route('relatorios.excel') }}?{{ http_build_query($filtros) }}"
                class="flex items-center gap-2 px-5 py-2 text-sm font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                📊 Excel (5 abas)
            </a>
            <a href="{{ route('relatorios.index') }}"
                class="px-4 py-2 text-sm text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50">
                ← Voltar
            </a>
        </div>
    </div>

    {{-- MODAL DE AJUDA --}}
    <div x-show="ajuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4 rounded-t-2xl flex justify-between">
                <h3 class="font-semibold">📘 Pré-visualização — Ajuda</h3>
                <button @click="ajuda = false" class="text-white/70 hover:text-white text-xl">×</button>
            </div>
            <div class="p-6 text-sm text-slate-700 space-y-3">
                <p>Esta tela mostra exatamente os dados que serão exportados.</p>
                <p><strong>📄 PDF com Gráficos:</strong> Exporta o relatório em PDF com gráficos de pizza, barras de progresso e tabelas formatadas para impressão.</p>
                <p><strong>📊 Excel (5 abas):</strong> Planilha com abas separadas para Resumo, Obras, Execução Financeira, Vencimentos e Por Empresa.</p>
                <p><strong>Os filtros aplicados</strong> são preservados automaticamente nos botões de exportação desta tela.</p>
            </div>
            <div class="px-6 py-4 border-t text-right">
                <button @click="ajuda = false" class="px-5 py-2 bg-blue-600 text-white rounded-lg">Entendi</button>
            </div>
        </div>
    </div>

</div>

@endsection