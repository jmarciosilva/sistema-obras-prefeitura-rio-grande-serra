@extends('layouts.app')

@section('title', 'Relatórios de Processos')
@section('subtitle', 'Exportação de dados de Processos Administrativos em PDF e Excel')

@section('content')

<div x-data="relatoriosProcessos()" class="space-y-6">

    {{-- ═══════════════════════════════════════════════════════
    | HEADER
    ══════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-600 mb-1">
                <a href="{{ route('relatorios.index') }}" class="hover:text-slate-900">Relatórios</a>
                <span>›</span>
                <span class="text-slate-900 font-medium">Processos Administrativos</span>
            </div>
            <h1 class="text-xl font-semibold text-slate-800">🗂️ Relatórios de Processos</h1>
            <p class="text-sm text-slate-500 mt-0.5">Gere relatórios em PDF (com gráficos) ou planilha Excel.</p>
        </div>
        <button @click="ajuda = true"
            class="self-start sm:self-auto px-4 py-2 text-sm bg-amber-100 text-amber-800 border border-amber-200 rounded-lg hover:bg-amber-200 transition">
            ❓ Ajuda
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════════
    | SELETOR DE MÓDULO
    ══════════════════════════════════════════════════════════ --}}
    <div class="flex gap-2">
        <a href="{{ route('relatorios.index') }}"
            class="px-4 py-2 text-sm font-medium rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
            🏗️ Obras
        </a>
        <span class="px-4 py-2 text-sm font-semibold rounded-lg bg-blue-600 text-white">🗂️ Processos Administrativos</span>
    </div>

    {{-- ═══════════════════════════════════════════════════════
    | CARDS DE KPIs RÁPIDOS
    ══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Total de Processos</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ $totais['processos'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Abertos</p>
            <p class="text-2xl font-bold text-green-700 mt-1">{{ $totais['abertos'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Com Pendência</p>
            <p class="text-2xl font-bold text-red-700 mt-1">{{ $totais['com_pendencia'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500">A Classificar</p>
            <p class="text-2xl font-bold text-amber-700 mt-1">{{ $totais['a_classificar'] }}</p>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
    | PAINEL DE RELATÓRIOS
    ══════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

        {{-- ABAS DE TIPO --}}
        <div class="border-b border-slate-200 px-6 pt-4">
            <div class="flex flex-wrap gap-1 -mb-px">
                @php
                $tiposRelatorio = [
                    'geral'           => ['icon' => '📋', 'label' => 'Geral',              'desc' => 'Todos os processos com fase e responsável'],
                    'por_fase'        => ['icon' => '📍', 'label' => 'Por Fase',           'desc' => 'Agrupado por fase de tramitação'],
                    'por_tipo'        => ['icon' => '🏷️', 'label' => 'Por Tipo',           'desc' => 'Agrupado por tipo de serviço'],
                    'pendencias'      => ['icon' => '⚠️', 'label' => 'Pendências',         'desc' => 'Processos parados, com motivo registrado'],
                    'por_responsavel' => ['icon' => '👷', 'label' => 'Por Responsável',    'desc' => 'Ranking por responsável técnico'],
                ];
                @endphp

                @foreach ($tiposRelatorio as $key => $meta)
                    <button type="button" @click="tipo = '{{ $key }}'"
                        :class="tipo === '{{ $key }}'
                            ? 'border-blue-600 text-blue-700 bg-blue-50 font-semibold'
                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="px-4 py-2.5 text-sm border-b-2 rounded-t-lg transition flex items-center gap-1.5">
                        {{ $meta['icon'] }} {{ $meta['label'] }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="p-6">

            {{-- DESCRIÇÃO DA ABA ATIVA --}}
            <div class="mb-5 p-3 bg-blue-50 border border-blue-100 rounded-lg text-sm text-blue-700">
                <template x-if="tipo === 'geral'">
                    <span>📋 <strong>Relatório Geral:</strong> Lista todos os processos com número, requerente, tipo, fase atual, responsável técnico e situação.</span>
                </template>
                <template x-if="tipo === 'por_fase'">
                    <span>📍 <strong>Por Fase:</strong> Quantidade de processos em cada fase de tramitação, com percentual do total.</span>
                </template>
                <template x-if="tipo === 'por_tipo'">
                    <span>🏷️ <strong>Por Tipo:</strong> Quantidade de processos por tipo de serviço (alvará, certidão, ofício etc.).</span>
                </template>
                <template x-if="tipo === 'pendencias'">
                    <span>⚠️ <strong>Pendências:</strong> Processos abertos com motivo de pendência registrado — exatamente os que precisam de atenção.</span>
                </template>
                <template x-if="tipo === 'por_responsavel'">
                    <span>👷 <strong>Por Responsável Técnico:</strong> Ranking de responsáveis técnicos por quantidade de processos vinculados.</span>
                </template>
            </div>

            {{-- FILTROS --}}
            <form id="form-relatorio" method="GET">
                <input type="hidden" name="tipo" :value="tipo">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">

                    {{-- TIPO DE PROCESSO --}}
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-600">Tipo de Processo</label>
                        <select name="tipo_processo_id"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos os tipos</option>
                            @foreach ($tiposProcesso as $t)
                                <option value="{{ $t->id }}">{{ $t->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- FASE --}}
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-600">Fase Atual</label>
                        <select name="fase_atual_id"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Todas as fases</option>
                            @foreach ($fasesProcesso as $f)
                                <option value="{{ $f->id }}">{{ $f->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- RESPONSÁVEL --}}
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-600">Responsável Técnico</label>
                        <select name="responsavel_tecnico_id"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos os responsáveis</option>
                            @foreach ($responsaveis as $r)
                                <option value="{{ $r->id }}">{{ $r->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- SITUAÇÃO --}}
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-600">Situação</label>
                        <select name="situacao"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Aberto e arquivado</option>
                            @foreach (\App\Models\Processo::$situacoes as $valor => $label)
                                <option value="{{ $valor }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- DATA INÍCIO --}}
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-600">Entrada — Data Início</label>
                        <input type="date" name="data_inicio"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- DATA FIM --}}
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-600">Entrada — Data Fim</label>
                        <input type="date" name="data_fim"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                </div>

                {{-- BOTÕES DE AÇÃO --}}
                <div class="flex flex-wrap gap-3 pt-4 border-t border-slate-200">

                    <button type="submit" formaction="{{ route('relatorios.processos.preview') }}"
                        class="flex items-center gap-2 px-5 py-2.5 text-sm font-medium bg-slate-700 text-white rounded-lg hover:bg-slate-800 transition">
                        👁 Pré-visualizar
                    </button>

                    <button type="submit" formaction="{{ route('relatorios.processos.pdf') }}"
                        class="flex items-center gap-2 px-5 py-2.5 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        📄 Exportar PDF
                    </button>

                    <button type="submit" formaction="{{ route('relatorios.processos.excel') }}"
                        class="flex items-center gap-2 px-5 py-2.5 text-sm font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        📊 Exportar Excel
                    </button>

                    <p class="self-center text-xs text-slate-400 ml-auto">
                        O PDF inclui gráficos visuais. O Excel inclui 5 abas com dados detalhados.
                    </p>
                </div>

            </form>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
    | CARTÕES DE ATALHO
    ══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        @php
        $atalhos = [
            [
                'icon'  => '⚠️',
                'titulo'=> 'Processos com pendência',
                'desc'  => 'Só processos abertos com motivo de pendência registrado.',
                'tipo'  => 'pendencias',
                'cor'   => 'border-red-300 bg-red-50',
                'btn'   => 'bg-red-600 hover:bg-red-700',
            ],
            [
                'icon'  => '📍',
                'titulo'=> 'Processos a classificar',
                'desc'  => 'Processos importados sem fase estruturada definida.',
                'tipo'  => 'geral',
                'extra' => '',
                'cor'   => 'border-amber-300 bg-amber-50',
                'btn'   => 'bg-amber-500 hover:bg-amber-600',
            ],
            [
                'icon'  => '📋',
                'titulo'=> 'Processos abertos',
                'desc'  => 'Todos os processos com situação "Aberto".',
                'tipo'  => 'geral',
                'extra' => 'situacao=aberto',
                'cor'   => 'border-blue-300 bg-blue-50',
                'btn'   => 'bg-blue-600 hover:bg-blue-700',
            ],
        ];
        @endphp

        @foreach ($atalhos as $a)
            <div class="rounded-xl border {{ $a['cor'] }} p-5">
                <div class="text-2xl mb-2">{{ $a['icon'] }}</div>
                <h3 class="font-semibold text-slate-800 text-sm mb-1">{{ $a['titulo'] }}</h3>
                <p class="text-xs text-slate-600 mb-4">{{ $a['desc'] }}</p>
                <div class="flex gap-2">
                    <a href="{{ route('relatorios.processos.preview') }}?tipo={{ $a['tipo'] }}&{{ $a['extra'] ?? '' }}"
                        class="px-3 py-1.5 text-xs rounded-lg text-slate-700 bg-white border border-slate-300 hover:bg-slate-50">
                        👁 Ver
                    </a>
                    <a href="{{ route('relatorios.processos.pdf') }}?tipo={{ $a['tipo'] }}&{{ $a['extra'] ?? '' }}"
                        class="px-3 py-1.5 text-xs rounded-lg text-white {{ $a['btn'] }}">
                        📄 PDF
                    </a>
                    <a href="{{ route('relatorios.processos.excel') }}?tipo={{ $a['tipo'] }}&{{ $a['extra'] ?? '' }}"
                        class="px-3 py-1.5 text-xs rounded-lg text-white bg-green-600 hover:bg-green-700">
                        📊 Excel
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ═══════════════════════════════════════════════════════
    | MODAL DE AJUDA
    ══════════════════════════════════════════════════════════ --}}
    <div x-show="ajuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">

            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-5 rounded-t-2xl flex justify-between items-start">
                <div>
                    <h2 class="text-lg font-bold">📘 Como usar os Relatórios de Processos</h2>
                    <p class="text-sm opacity-80 mt-0.5">Guia completo do módulo de exportação</p>
                </div>
                <button @click="ajuda = false" class="text-white/70 hover:text-white text-2xl leading-none">×</button>
            </div>

            <div class="p-6 space-y-5 text-sm text-slate-700">

                <div class="flex gap-3 p-4 bg-blue-50 rounded-lg border border-blue-100">
                    <span class="text-2xl">1️⃣</span>
                    <div>
                        <strong class="block text-slate-800 mb-1">Escolha o tipo de relatório</strong>
                        Geral, por fase, por tipo de serviço, pendências ou por responsável técnico.
                    </div>
                </div>

                <div class="flex gap-3 p-4 bg-slate-50 rounded-lg border border-slate-200">
                    <span class="text-2xl">2️⃣</span>
                    <div>
                        <strong class="block text-slate-800 mb-1">Aplique os filtros desejados</strong>
                        Combine tipo, fase, responsável técnico, situação e período de entrada. Deixe em branco para incluir todos os registros.
                    </div>
                </div>

                <div class="flex gap-3 p-4 bg-red-50 rounded-lg border border-red-100">
                    <span class="text-2xl">3️⃣</span>
                    <div>
                        <strong class="block text-slate-800 mb-1">📄 Exportar PDF</strong>
                        PDF em A4 paisagem com gráfico de distribuição por fase e tabelas formatadas.
                    </div>
                </div>

                <div class="flex gap-3 p-4 bg-green-50 rounded-lg border border-green-100">
                    <span class="text-2xl">4️⃣</span>
                    <div>
                        <strong class="block text-slate-800 mb-1">📊 Exportar Excel</strong>
                        Planilha com 5 abas: Resumo, Processos, Por Fase, Por Tipo e Pendências.
                    </div>
                </div>

            </div>

            <div class="px-6 py-4 border-t flex justify-end">
                <button @click="ajuda = false" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Entendi 👍
                </button>
            </div>

        </div>
    </div>

</div>

<script>
function relatoriosProcessos() {
    return {
        tipo: 'geral',
        ajuda: false,
    }
}
</script>

@endsection
