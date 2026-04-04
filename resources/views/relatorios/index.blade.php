@extends('layouts.app')

@section('title', 'Relatórios')
@section('subtitle', 'Exportação de dados em PDF e Excel')

@section('content')

<div x-data="relatorios()" class="space-y-6">

    {{-- ═══════════════════════════════════════════════════════
    | HEADER
    ══════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">📊 Relatórios</h1>
            <p class="text-sm text-slate-500 mt-0.5">Gere relatórios em PDF (com gráficos) ou planilha Excel.</p>
        </div>
        <button @click="ajuda = true"
            class="self-start sm:self-auto px-4 py-2 text-sm bg-amber-100 text-amber-800 border border-amber-200 rounded-lg hover:bg-amber-200 transition">
            ❓ Ajuda
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════════
    | CARDS DE KPIs RÁPIDOS
    ══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Total de Obras</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ $totais['obras'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Contratos</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ $totais['contratos'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Valor Contratado</p>
            <p class="text-lg font-bold text-blue-700 mt-1">
                R$ {{ number_format($totais['valor_contratado'], 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Valor Medido</p>
            <p class="text-lg font-bold text-green-700 mt-1">
                R$ {{ number_format($totais['valor_medido'], 0, ',', '.') }}
            </p>
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
                    'geral'       => ['icon' => '📋', 'label' => 'Geral',               'desc' => 'Todas as obras com KPIs'],
                    'financeiro'  => ['icon' => '💰', 'label' => 'Execução Financeira', 'desc' => 'Contratado × Medido × Saldo'],
                    'vencimento'  => ['icon' => '⏳', 'label' => 'Vencimentos',         'desc' => 'Contratos vencendo/vencidos'],
                    'status'      => ['icon' => '🚦', 'label' => 'Por Status',           'desc' => 'Obras agrupadas por fase'],
                    'empresa'     => ['icon' => '🏢', 'label' => 'Por Empresa',          'desc' => 'Ranking de empresas contratadas'],
                    'orgao'       => ['icon' => '🏛️', 'label' => 'Por Órgão',           'desc' => 'Por órgão financiador'],
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
                    <span>📋 <strong>Relatório Geral:</strong> Lista todas as obras com status, endereço, valor contratado, medido, saldo e percentual de execução.</span>
                </template>
                <template x-if="tipo === 'financeiro'">
                    <span>💰 <strong>Execução Financeira:</strong> Compara o valor contratado × medido × saldo para cada obra, com barra de progresso visual no PDF.</span>
                </template>
                <template x-if="tipo === 'vencimento'">
                    <span>⏳ <strong>Vencimentos:</strong> Lista contratos que vencerão nos próximos dias configurados, incluindo contratos já vencidos.</span>
                </template>
                <template x-if="tipo === 'status'">
                    <span>🚦 <strong>Por Status:</strong> Agrupa obras por fase (Planejamento, Licitação, Execução, etc.) com gráfico de pizza no PDF.</span>
                </template>
                <template x-if="tipo === 'empresa'">
                    <span>🏢 <strong>Por Empresa:</strong> Ranking das empresas contratadas por valor total e número de contratos.</span>
                </template>
                <template x-if="tipo === 'orgao'">
                    <span>🏛️ <strong>Por Órgão Financiador:</strong> Filtra obras vinculadas a um órgão específico (convênios).</span>
                </template>
            </div>

            {{-- FILTROS --}}
            <form id="form-relatorio" method="GET">
                <input type="hidden" name="tipo" :value="tipo">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">

                    {{-- STATUS --}}
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-600">Filtrar por Status</label>
                        <select name="status_id"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos os status</option>
                            @foreach ($statuses as $s)
                                <option value="{{ $s->id }}">{{ $s->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ÓRGÃO --}}
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-600">Órgão Financiador</label>
                        <select name="orgao_id"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos os órgãos</option>
                            @foreach ($orgaos as $o)
                                <option value="{{ $o->id }}">{{ $o->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- EMPRESA --}}
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-600">Empresa Contratada</label>
                        <select name="empresa_id"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Todas as empresas</option>
                            @foreach ($empresas as $e)
                                <option value="{{ $e->id }}">{{ $e->razao_social }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- DATA INÍCIO --}}
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-600">Período — Data Início</label>
                        <input type="date" name="data_inicio"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- DATA FIM --}}
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-600">Período — Data Fim</label>
                        <input type="date" name="data_fim"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- VENCIMENTO (só aparece no tipo vencimento) --}}
                    <div class="space-y-1" x-show="tipo === 'vencimento'">
                        <label class="text-xs font-medium text-slate-600">Alertar vencimentos em até</label>
                        <select name="vencimento_dias"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="30">30 dias</option>
                            <option value="60">60 dias</option>
                            <option value="90">90 dias</option>
                            <option value="180">180 dias</option>
                        </select>
                    </div>

                </div>

                {{-- BOTÕES DE AÇÃO --}}
                <div class="flex flex-wrap gap-3 pt-4 border-t border-slate-200">

                    {{-- PRÉ-VISUALIZAR --}}
                    <button type="submit" formaction="{{ route('relatorios.preview') }}"
                        class="flex items-center gap-2 px-5 py-2.5 text-sm font-medium bg-slate-700 text-white rounded-lg hover:bg-slate-800 transition">
                        👁 Pré-visualizar
                    </button>

                    {{-- EXPORTAR PDF --}}
                    <button type="submit" formaction="{{ route('relatorios.pdf') }}"
                        class="flex items-center gap-2 px-5 py-2.5 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        📄 Exportar PDF
                    </button>

                    {{-- EXPORTAR EXCEL --}}
                    <button type="submit" formaction="{{ route('relatorios.excel') }}"
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
                'icon'  => '⏳',
                'titulo'=> 'Contratos vencendo em 30 dias',
                'desc'  => 'Exporta apenas os contratos com vigência nos próximos 30 dias.',
                'tipo'  => 'vencimento',
                'extra' => 'vencimento_dias=30',
                'cor'   => 'border-amber-300 bg-amber-50',
                'btn'   => 'bg-amber-500 hover:bg-amber-600',
            ],
            [
                'icon'  => '💰',
                'titulo'=> 'Execução financeira completa',
                'desc'  => 'Contratado × medido × saldo de todas as obras sem filtro.',
                'tipo'  => 'financeiro',
                'extra' => '',
                'cor'   => 'border-green-300 bg-green-50',
                'btn'   => 'bg-green-600 hover:bg-green-700',
            ],
            [
                'icon'  => '📋',
                'titulo'=> 'Obras em execução',
                'desc'  => 'Somente obras com status "Em Execução".',
                'tipo'  => 'geral',
                'extra' => 'status_id=3',
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
                    <a href="{{ route('relatorios.preview') }}?tipo={{ $a['tipo'] }}&{{ $a['extra'] }}"
                        class="px-3 py-1.5 text-xs rounded-lg text-slate-700 bg-white border border-slate-300 hover:bg-slate-50">
                        👁 Ver
                    </a>
                    <a href="{{ route('relatorios.pdf') }}?tipo={{ $a['tipo'] }}&{{ $a['extra'] }}"
                        class="px-3 py-1.5 text-xs rounded-lg text-white {{ $a['btn'] }}">
                        📄 PDF
                    </a>
                    <a href="{{ route('relatorios.excel') }}?tipo={{ $a['tipo'] }}&{{ $a['extra'] }}"
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
                    <h2 class="text-lg font-bold">📘 Como usar os Relatórios</h2>
                    <p class="text-sm opacity-80 mt-0.5">Guia completo do módulo de exportação</p>
                </div>
                <button @click="ajuda = false" class="text-white/70 hover:text-white text-2xl leading-none">×</button>
            </div>

            <div class="p-6 space-y-5 text-sm text-slate-700">

                <div class="flex gap-3 p-4 bg-blue-50 rounded-lg border border-blue-100">
                    <span class="text-2xl">1️⃣</span>
                    <div>
                        <strong class="block text-slate-800 mb-1">Escolha o tipo de relatório</strong>
                        Clique nas abas no topo do painel para selecionar o que deseja exportar: relatório geral, execução financeira, vencimentos, agrupamento por status, por empresa ou por órgão financiador.
                    </div>
                </div>

                <div class="flex gap-3 p-4 bg-slate-50 rounded-lg border border-slate-200">
                    <span class="text-2xl">2️⃣</span>
                    <div>
                        <strong class="block text-slate-800 mb-1">Aplique os filtros desejados</strong>
                        Você pode combinar filtros: status da obra, órgão financiador, empresa contratada e período de medição. Deixe os campos em branco para incluir todos os registros.
                    </div>
                </div>

                <div class="flex gap-3 p-4 bg-slate-50 rounded-lg border border-slate-200">
                    <span class="text-2xl">3️⃣</span>
                    <div>
                        <strong class="block text-slate-800 mb-1">Pré-visualize antes de exportar</strong>
                        O botão <strong>👁 Pré-visualizar</strong> mostra exatamente o que será exportado. Use-o para conferir os dados antes de gerar o arquivo final.
                    </div>
                </div>

                <div class="flex gap-3 p-4 bg-red-50 rounded-lg border border-red-100">
                    <span class="text-2xl">4️⃣</span>
                    <div>
                        <strong class="block text-slate-800 mb-1">📄 Exportar PDF</strong>
                        Gera um arquivo PDF em formato A4 paisagem com <strong>gráficos visuais</strong> (pizza de status, barras de execução financeira, evolução mensal). Ideal para apresentações e impressão.
                    </div>
                </div>

                <div class="flex gap-3 p-4 bg-green-50 rounded-lg border border-green-100">
                    <span class="text-2xl">5️⃣</span>
                    <div>
                        <strong class="block text-slate-800 mb-1">📊 Exportar Excel</strong>
                        Gera uma planilha com <strong>5 abas</strong>: Resumo, Obras, Execução Financeira, Contratos Vencendo e Por Empresa. Ideal para análise e cruzamento de dados.
                    </div>
                </div>

                <div class="p-4 bg-amber-50 rounded-lg border border-amber-200">
                    <strong class="block text-amber-800 mb-2">💡 Atalhos rápidos</strong>
                    <p class="text-amber-700">Os três cartões abaixo do painel são atalhos para os relatórios mais comuns. Clique em PDF ou Excel diretamente sem precisar configurar filtros.</p>
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
function relatorios() {
    return {
        tipo: 'geral',
        ajuda: false,
    }
}
</script>

@endsection