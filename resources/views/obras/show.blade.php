@extends('layouts.app')

@section('title', 'Detalhe da Obra')
@section('subtitle', 'Acompanhamento completo da obra')

@section('content')

    {{-- =========================================================
|  RAIZ Alpine.js — estado global da página
|  Variáveis:
|    aba           → aba ativa (geral|convenios|contratos|execucoes|documentos)
|    ajuda         → exibe bloco de ajuda
|    modalDelete   → modal de confirmação de exclusão de documento
|    urlDelete     → URL do documento a ser excluído
|    modalConvenios→ modal de gerenciamento de convênios
========================================================== --}}
    <div x-data="{
        aba: '{{ session('aba', 'geral') }}',
        ajuda: false,
        modalDelete: false,
        urlDelete: '',
        modalConvenios: false
    }">

        {{-- =========================================================
    |  HEADER — título, endereço, status e botões de ação
    ========================================================== --}}
        <div class="flex flex-col md:flex-row md:justify-between mb-6 gap-4">

            <div>
                <h1 class="text-xl font-semibold text-slate-800">
                    🏗️ {{ $obra->descricao }}
                </h1>
                <div class="text-sm text-slate-500 mt-1">
                    📍 {{ $obra->endereco ?? 'Local não informado' }}
                </div>
                <div class="mt-2 flex items-center gap-2 flex-wrap">
                    <span class="px-2 py-0.5 rounded text-xs text-white"
                        style="background-color: {{ $obra->status->cor ?? '#64748b' }}">
                        {{ $obra->status->nome }}
                    </span>
                    @if ($obra->processo_execucao)
                        <span class="text-xs text-slate-400">
                            Processo: {{ $obra->processo_execucao }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Botões principais — ocultos para perfil operador --}}
            <div class="flex flex-wrap gap-2 items-start">

                <button @click="ajuda = !ajuda"
                    class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                    ❓ Ajuda
                </button>

                @if (auth()->user()->perfil !== 'operador')
                    <a href="{{ route('obras.execucoes.create', $obra) }}"
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700">
                        ➕ Medição
                    </a>

                    <a href="{{ route('contratos.create', ['obra_id' => $obra->id]) }}"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        ➕ Contrato
                    </a>

                    <a href="{{ route('obras.edit', $obra) }}"
                        class="px-4 py-2 text-sm bg-slate-600 text-white rounded-lg hover:bg-slate-700">
                        ✏️ Editar
                    </a>
                @endif

            </div>
        </div>

        {{-- =========================================================
    |  FLASH MESSAGES — sucesso e erro geral
    ========================================================== --}}
        @if (session('sucesso'))
            <div class="mb-4 bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm">
                ✅ {{ session('sucesso') }}
            </div>
        @endif
        @if ($errors->has('geral'))
            <div class="mb-4 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">
                ⚠️ {{ $errors->first('geral') }}
            </div>
        @endif

        {{-- =========================================================
    |  BLOCO DE AJUDA — expansível via botão ❓
    ========================================================== --}}
        <div x-show="ajuda" x-cloak class="mb-6 bg-yellow-50 border border-yellow-300 text-yellow-800 p-4 rounded-lg">
            <strong>📘 Como usar esta tela:</strong>
            <ul class="mt-2 list-disc ml-5 text-sm space-y-1">
                <li>Convênios são opcionais e podem ser vinculados no formulário de edição da obra</li>
                <li>Primeiro cadastre uma <b>empresa</b> (menu Administração → Empresas)</li>
                <li>Depois crie um <b>contrato</b> vinculado à obra usando o botão acima</li>
                <li>Registre <b>medições</b> conforme a execução avança</li>
                <li>Anexe documentos como contratos assinados, fotos e relatórios</li>
            </ul>
        </div>

        {{-- =========================================================
    |  ETAPAS DA OBRA — checklist visual das 3 etapas obrigatórias
    ========================================================== --}}
        <div class="mb-6 bg-white p-4 rounded-lg shadow">
            <h3 class="text-sm font-semibold text-slate-600 mb-3">🚦 Etapas da Obra</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">

                {{-- Etapa 1: Convênios (opcional) --}}
                <div
                    class="p-3 rounded-lg border
                {{ $obra->convenios->count() > 0 ? 'bg-green-50 border-green-300' : 'bg-slate-50 border-slate-200' }}">
                    <div class="font-medium">1️⃣ Convênios</div>
                    @if ($obra->convenios->count() > 0)
                        <div class="text-xs text-green-600">
                            ✔ {{ $obra->convenios->count() }} convênio(s) vinculado(s)
                        </div>
                    @else
                        <div class="text-xs text-slate-500">Opcional — nenhum convênio vinculado</div>
                    @endif
                </div>

                {{-- Etapa 2: Empresas (via contratos) --}}
                <div
                    class="p-3 rounded-lg border
                {{ $obra->contratos->count() > 0 ? 'bg-green-50 border-green-300' : 'bg-yellow-50 border-yellow-300' }}">
                    <div class="font-medium">2️⃣ Empresas</div>
                    @if ($obra->contratos->count() > 0)
                        <div class="text-xs text-green-600">✔ Empresas vinculadas via contratos</div>
                    @else
                        <div class="text-xs text-yellow-600">Cadastre uma empresa antes do contrato</div>
                    @endif
                </div>

                {{-- Etapa 3: Contratos --}}
                <div
                    class="p-3 rounded-lg border
                {{ $obra->contratos->count() > 0 ? 'bg-green-50 border-green-300' : 'bg-red-50 border-red-300' }}">
                    <div class="font-medium">3️⃣ Contratos</div>
                    @if ($obra->contratos->count() > 0)
                        <div class="text-xs text-green-600">
                            ✔ {{ $obra->contratos->count() }} contrato(s) cadastrado(s)
                        </div>
                    @else
                        <div class="text-xs text-red-600">Nenhum contrato cadastrado</div>
                    @endif
                </div>

            </div>
        </div>

        {{-- =========================================================
    |  KPIs — 5 cards financeiros calculados via accessors do model
    |  Dependem de contratos.execucoes estar carregado (feito no controller)
    ========================================================== --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">

            @php
                $valorMedido = $obra->valor_medido;
                $valorContrato = $obra->valor_contratado;
                $saldo = $obra->saldo_contratual;
                $percentual = $obra->percentual_executado;
            @endphp

            <div class="bg-white p-4 rounded-lg shadow">
                <p class="text-xs text-slate-500">Valor Medido</p>
                <p class="text-lg text-green-600 font-semibold">
                    R$ {{ number_format($valorMedido, 2, ',', '.') }}
                </p>
                @if ($valorContrato > 0)
                    <p class="text-xs text-slate-400 mt-0.5">
                        de R$ {{ number_format($valorContrato, 2, ',', '.') }}
                    </p>
                @endif
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
                <p class="text-xs text-slate-500">Saldo</p>
                <p
                    class="text-lg font-semibold
                {{ $saldo <= 0 && $valorContrato > 0 ? 'text-red-600' : 'text-blue-700' }}">
                    R$ {{ number_format($saldo, 2, ',', '.') }}
                </p>
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
                <p class="text-xs text-slate-500">% Executado</p>
                <p
                    class="text-lg font-semibold
                {{ $percentual >= 80 ? 'text-green-600' : ($percentual >= 40 ? 'text-yellow-600' : 'text-blue-600') }}">
                    {{ number_format($percentual, 1) }}%
                </p>
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
                <p class="text-xs text-slate-500">Contratos</p>
                <p class="text-lg font-semibold text-slate-800">
                    {{ $obra->contratos->count() }}
                </p>
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
                <p class="text-xs text-slate-500">Convênios</p>
                <p
                    class="text-lg font-semibold
                {{ $obra->convenios->count() > 0 ? 'text-indigo-600' : 'text-slate-400' }}">
                    {{ $obra->convenios->count() }}
                </p>
            </div>

        </div>

        {{-- =========================================================
    |  BARRA DE PROGRESSO GLOBAL da obra
    ========================================================== --}}
        @php $pct = min($percentual ?? $obra->percentual_executado, 100); @endphp
        <div class="mb-6">
            <div class="flex justify-between text-xs text-slate-500 mb-1">
                <span>Progresso de execução</span>
                <span>{{ number_format($pct, 1) }}%</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-3">
                <div class="h-3 rounded-full transition-all
                {{ $pct >= 80 ? 'bg-green-500' : ($pct >= 40 ? 'bg-yellow-500' : 'bg-blue-600') }}"
                    style="width: {{ $pct }}%">
                </div>
            </div>
        </div>

        {{-- =========================================================
    |  PAINEL DE ABAS
    ========================================================== --}}
        <div class="bg-white rounded-lg shadow">

            {{-- ── Navegação das abas ── --}}
            <div class="border-b flex flex-wrap gap-1 px-4 py-2 text-sm">

                @php
                    $abas = [
                        'geral' => ['label' => 'Geral', 'icon' => '📋'],
                        'convenios' => ['label' => 'Convênios', 'icon' => '🤝', 'count' => $obra->convenios->count()],
                        'contratos' => ['label' => 'Contratos', 'icon' => '📄', 'count' => $obra->contratos->count()],
                        'execucoes' => [
                            'label' => 'Execuções',
                            'icon' => '📊',
                            'count' => $obra->contratos->sum(fn($c) => $c->execucoes->count()),
                        ],
                        'documentos' => [
                            'label' => 'Documentos',
                            'icon' => '📎',
                            'count' =>
                                $obra->documentos->count() +
                                $obra->contratos->sum(fn($c) => $c->execucoes->sum(fn($e) => $e->documentos->count())),
                        ],
                    ];
                @endphp

                @foreach ($abas as $key => $meta)
                    <button @click="aba = '{{ $key }}'"
                        :class="aba === '{{ $key }}'
                            ?
                            'bg-blue-50 text-blue-700 border border-blue-200 font-semibold' :
                            'text-slate-600 hover:bg-slate-50 border border-transparent'"
                        class="px-4 py-2 rounded-lg text-sm flex items-center gap-1.5 transition">
                        {{ $meta['icon'] }} {{ $meta['label'] }}
                        @if (isset($meta['count']) && $meta['count'] > 0)
                            <span class="ml-1 text-xs px-1.5 py-0.5 rounded-full bg-slate-200 text-slate-600">
                                {{ $meta['count'] }}
                            </span>
                        @endif
                    </button>
                @endforeach

            </div>

            {{-- ── Conteúdo das abas ── --}}
            <div class="p-6">

                {{-- =================================================
            |  ABA: GERAL — observações e demanda vinculada
            ================================================== --}}
                <div x-show="aba === 'geral'">

                    <h3 class="text-sm font-semibold text-slate-500 uppercase mb-3">Observações</h3>

                    @if ($obra->observacoes)
                        <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                            {{ $obra->observacoes }}
                        </p>
                    @else
                        <p class="text-sm text-slate-400 italic">Nenhuma observação registrada.</p>
                    @endif

                    @if ($obra->demandaProposta ?? null)
                        <div class="mt-6">
                            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-2">Demanda / Proposta</h3>
                            <p class="text-sm text-slate-700">{{ $obra->demandaProposta->numero_demanda }}</p>
                        </div>
                    @endif

                </div>

                {{-- =================================================
            |  ABA: CONVÊNIOS — lista e gerenciamento via modal
            ================================================== --}}
                <div x-show="aba === 'convenios'" x-cloak>

                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-slate-500 uppercase">
                            Convênios vinculados ({{ $obra->convenios->count() }})
                        </h3>
                        @if (auth()->user()->perfil !== 'operador')
                            <button type="button" @click="modalConvenios = true"
                                class="px-3 py-1.5 text-xs rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-100">
                                🔗 Gerenciar vínculos
                            </button>
                        @endif
                    </div>

                    @forelse($obra->convenios as $conv)
                        <div class="border border-slate-200 rounded-lg p-4 mb-3 hover:bg-slate-50 transition">
                            <div class="flex items-start justify-between gap-4">

                                <div class="flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-medium text-slate-800">
                                            {{ $conv->numero_convenio_ano ?? 'Convênio #' . $conv->id }}
                                        </span>
                                        @if ($conv->categoria)
                                            <span class="px-2 py-0.5 rounded-full text-xs bg-indigo-100 text-indigo-700">
                                                {{ $conv->categoria->nome }}
                                            </span>
                                        @endif
                                        @if ($conv->vigencia)
                                            @php $cv = $conv->vigencia->isPast(); @endphp
                                            <span
                                                class="px-2 py-0.5 rounded-full text-xs
                                            {{ $cv ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                                Vigência: {{ $conv->vigencia->format('d/m/Y') }}
                                                {{ $cv ? '⚠️' : '' }}
                                            </span>
                                        @endif
                                    </div>

                                    <p class="text-sm text-slate-600 mt-1">
                                        {{ Str::limit($conv->descricao, 120) }}
                                    </p>

                                    <div class="mt-2 flex flex-wrap gap-4 text-xs text-slate-500">
                                        @if ($conv->orgaoFinanciador)
                                            <span>🏛️ {{ $conv->orgaoFinanciador->nome }}</span>
                                        @endif
                                        @if ($conv->valor_repasse_contrapartida)
                                            <span>💰 R$
                                                {{ number_format($conv->valor_repasse_contrapartida, 2, ',', '.') }}</span>
                                        @endif
                                    </div>
                                </div>

                                <a href="{{ route('convenios.show', $conv) }}"
                                    class="shrink-0 px-3 py-1.5 text-xs rounded-lg border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                    👁 Ver
                                </a>

                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 text-slate-400">
                            <p class="text-2xl mb-2">🤝</p>
                            <p class="text-sm">Nenhum convênio vinculado a esta obra.</p>
                            @if (auth()->user()->perfil !== 'operador')
                                <button type="button" @click="modalConvenios = true"
                                    class="inline-block mt-3 text-xs text-indigo-600 hover:underline">
                                    🔗 Clique aqui para associar convênios
                                </button>
                            @endif
                        </div>
                    @endforelse

                </div>

                {{-- =================================================
            |  ABA: CONTRATOS — lista com alertas de vencimento
            ================================================== --}}
                <div x-show="aba === 'contratos'" x-cloak>

                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-slate-500 uppercase">
                            Contratos ({{ $obra->contratos->count() }})
                        </h3>
                        @if (auth()->user()->perfil !== 'operador')
                            <a href="{{ route('contratos.create', ['obra_id' => $obra->id]) }}"
                                class="px-3 py-1.5 text-xs rounded-lg bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100">
                                ➕ Novo Contrato
                            </a>
                        @endif
                    </div>

                    @forelse($obra->contratos as $contrato)
                        @php
                            $vencido = $contrato->estaVencido();
                            $breve = !$vencido && $contrato->venceEm(30);
                        @endphp
                        <div
                            class="border rounded-lg p-4 mb-3 hover:bg-slate-50 transition
                        {{ $vencido ? 'border-red-200' : ($breve ? 'border-yellow-200' : 'border-slate-200') }}">

                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">

                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-medium text-slate-800">
                                            {{ $contrato->numero_contrato_ano ?? 'Contrato #' . $contrato->id }}
                                        </span>
                                        @if ($vencido)
                                            <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700">
                                                ⚠️ Vigência vencida
                                            </span>
                                        @elseif ($breve)
                                            <span class="px-2 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-700">
                                                ⏳ Vence em breve
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-3 text-xs text-slate-600">
                                        <div>
                                            <span class="text-slate-400">Empresa</span>
                                            <p class="font-medium">{{ $contrato->empresa->nomeExibicao() }}</p>
                                        </div>
                                        <div>
                                            <span class="text-slate-400">Valor</span>
                                            <p class="font-medium">
                                                R$
                                                {{ $contrato->valor_contrato ? number_format($contrato->valor_contrato, 2, ',', '.') : '—' }}
                                            </p>
                                        </div>
                                        <div>
                                            <span class="text-slate-400">Licitação</span>
                                            <p class="font-medium">{{ $contrato->processo_licitacao ?? '—' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-slate-400">Vigência</span>
                                            <p class="font-medium {{ $vencido ? 'text-red-600' : '' }}">
                                                {{ $contrato->vigencia_contrato?->format('d/m/Y') ?? '—' }}
                                            </p>
                                        </div>
                                    </div>

                                </div>

                                <div class="shrink-0 flex gap-2">
                                    <a href="{{ route('contratos.show', $contrato) }}"
                                        class="px-3 py-1.5 text-xs rounded-lg border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                        👁 Ver
                                    </a>
                                    @if (auth()->user()->perfil !== 'operador')
                                        <a href="{{ route('contratos.edit', $contrato) }}"
                                            class="px-3 py-1.5 text-xs rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100">
                                            ✏️
                                        </a>
                                    @endif
                                </div>
                            </div>

                        </div>
                    @empty
                        <div class="text-center py-10 text-slate-400">
                            <p class="text-2xl mb-2">📄</p>
                            <p class="text-sm">Nenhum contrato cadastrado para esta obra.</p>
                            @if (auth()->user()->perfil !== 'operador')
                                <a href="{{ route('contratos.create', ['obra_id' => $obra->id]) }}"
                                    class="inline-block mt-3 text-xs text-blue-600 hover:underline">
                                    ➕ Criar primeiro contrato
                                </a>
                            @endif
                        </div>
                    @endforelse

                </div>

                {{-- =================================================
            |  ABA: EXECUÇÕES — FASE 4
            |
            |  Contém:
            |    • Toggle Gráficos / Tabela
            |    • KPI cards (sempre visíveis)
            |    • Card de Projeção de Conclusão (algoritmo de ritmo médio)
            |    • Gráfico 1 — Linha: evolução do % executado
            |    • Gráfico 2 — Barras: contratado × medido × saldo por contrato
            |    • Gráfico 3 — Misto: valor por medição + linha acumulada
            |    • Tabela original de medições (na vista "Tabela")
            |    • Modal explicativo "Como é calculado?"
            |
            |  Dados vêm via fetch() do endpoint:
            |    GET /obras/visualizar/{obra}/grafico  (obras.grafico)
            |    → ObraController@grafico()
            ================================================== --}}
                <div x-show="aba === 'execucoes'" x-cloak x-data="execucoesGrafico({{ $obra->id }}, '{{ route('obras.grafico', $obra) }}')" x-init="init()">

                    {{-- ── Header da aba com toggle e botão Nova Medição ── --}}
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide">
                            Execuções / Medições
                        </h3>
                        <div class="flex items-center gap-2">

                            {{-- Toggle Gráficos / Tabela --}}
                            <div class="flex rounded-lg border border-slate-200 overflow-hidden text-xs">
                                <button @click="vista = 'graficos'"
                                    :class="vista === 'graficos'
                                        ?
                                        'bg-blue-600 text-white' :
                                        'bg-white text-slate-600 hover:bg-slate-50'"
                                    class="px-3 py-1.5 transition">
                                    📈 Gráficos
                                </button>
                                <button @click="vista = 'tabela'"
                                    :class="vista === 'tabela'
                                        ?
                                        'bg-blue-600 text-white' :
                                        'bg-white text-slate-600 hover:bg-slate-50'"
                                    class="px-3 py-1.5 border-l border-slate-200 transition">
                                    📋 Tabela
                                </button>
                            </div>

                            @if (auth()->user()->perfil !== 'operador')
                                <a href="{{ route('obras.execucoes.create', $obra) }}"
                                    class="px-3 py-1.5 text-xs rounded-lg bg-green-50 border border-green-200 text-green-700 hover:bg-green-100">
                                    ➕ Nova Medição
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- ── Cálculos PHP para KPIs e tabela ── --}}
                    @php
                        $todasExecucoes = $obra->contratos
                            ->flatMap(
                                fn($c) => $c->execucoes->map(
                                    fn($e) => [
                                        'execucao' => $e,
                                        'contrato_label' => $c->numero_contrato_ano ?? 'Contrato #' . $c->id,
                                        'empresa' => $c->empresa->nomeExibicao(),
                                        'valor_contrato' => $c->valor_contrato ?? 0,
                                    ],
                                ),
                            )
                            ->sortByDesc(
                                fn($item) => $item['execucao']->data_medicao->format('Y-m-d') .
                                    str_pad($item['execucao']->id, 10, '0', STR_PAD_LEFT),
                            );

                        $totalMedido = $todasExecucoes->sum(fn($i) => $i['execucao']->valor_medido ?? 0);
                        $valorContrato = $obra->valor_contratado;
                        $pctGeral = $valorContrato > 0 ? min(($totalMedido / $valorContrato) * 100, 100) : 0;
                        $saldoGeral = max($valorContrato - $totalMedido, 0);
                    @endphp

                    {{-- ── KPI cards — sempre visíveis em ambas as vistas ── --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-center">
                            <p class="text-xs text-slate-500 mb-1">Total medido</p>
                            <p class="font-bold text-green-700 text-sm">
                                R$ {{ number_format($totalMedido, 2, ',', '.') }}
                            </p>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
                            <p class="text-xs text-slate-500 mb-1">Valor contratado</p>
                            <p class="font-bold text-blue-700 text-sm">
                                R$ {{ number_format($valorContrato, 2, ',', '.') }}
                            </p>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-center">
                            <p class="text-xs text-slate-500 mb-1">Saldo</p>
                            <p class="font-bold text-sm {{ $saldoGeral <= 0 ? 'text-red-600' : 'text-slate-700' }}">
                                R$ {{ number_format($saldoGeral, 2, ',', '.') }}
                            </p>
                        </div>
                        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-3 text-center">
                            <p class="text-xs text-slate-500 mb-1">% Executado</p>
                            <p class="font-bold text-indigo-700 text-sm">{{ number_format($pctGeral, 1) }}%</p>
                            <div class="mt-1.5 w-full bg-slate-200 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full
                                {{ $pctGeral >= 80 ? 'bg-green-500' : ($pctGeral >= 40 ? 'bg-yellow-500' : 'bg-blue-600') }}"
                                    style="width: {{ min($pctGeral, 100) }}%">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ════════════════════════════════════════════
                |  VISTA: GRÁFICOS (Fase 4 — Chart.js)
                ════════════════════════════════════════════ --}}
                    <div x-show="vista === 'graficos'">

                        {{-- Estado: carregando --}}
                        <div x-show="carregando" class="flex flex-col items-center justify-center py-16 text-slate-400">
                            <div class="w-8 h-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mb-3">
                            </div>
                            <p class="text-sm">Carregando dados do gráfico...</p>
                        </div>

                        {{-- Estado: erro ao buscar dados --}}
                        <div x-show="erro && !carregando"
                            class="bg-red-50 border border-red-200 text-red-700 px-4 py-4 rounded-lg text-sm mb-4">
                            <div class="flex items-start justify-between gap-4">
                                <span>⚠️ <span x-text="erro"></span></span>
                                <button @click="carregando=true; erro=null; _jaIniciou=false; destruirCharts(); init()"
                                    class="shrink-0 px-3 py-1 text-xs bg-red-100 hover:bg-red-200 rounded border border-red-300 text-red-700">
                                    🔄 Tentar novamente
                                </button>
                            </div>
                            <p class="text-xs text-red-500 mt-1">
                                Abra o console do navegador (F12 → Console) para ver o erro detalhado.
                            </p>
                        </div>

                        {{-- Estado: sem medições --}}
                        <div x-show="!carregando && !erro && semDados" class="text-center py-16 text-slate-400">
                            <p class="text-3xl mb-2">📊</p>
                            <p class="text-sm">Nenhuma medição registrada para gerar gráficos.</p>
                            @if ($obra->contratos->count() === 0)
                                <p class="text-xs mt-1">Cadastre um contrato primeiro.</p>
                            @elseif (auth()->user()->perfil !== 'operador')
                                <a href="{{ route('obras.execucoes.create', $obra) }}"
                                    class="inline-block mt-3 text-xs text-green-600 hover:underline">
                                    ➕ Registrar primeira medição
                                </a>
                            @endif
                        </div>

                        {{-- Conteúdo: gráficos + projeção --}}
                        <div x-show="!carregando && !erro && !semDados" class="space-y-6">

                            {{-- ── Card de Projeção de Conclusão ── --}}
                            <div x-show="projecao" class="rounded-xl border p-4"
                                :class="projecao?.concluida ?
                                    'bg-green-50 border-green-200' :
                                    (projecao?.confianca === 'alta' ?
                                        'bg-blue-50 border-blue-200' :
                                        'bg-amber-50 border-amber-200')">

                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h4 class="font-semibold text-sm mb-1"
                                            :class="projecao?.concluida ? 'text-green-700' : 'text-slate-800'">
                                            <span x-show="projecao?.concluida">✅ Obra concluída financeiramente</span>
                                            <span x-show="!projecao?.concluida">🎯 Projeção de Conclusão</span>
                                        </h4>

                                        <div x-show="!projecao?.concluida" class="flex flex-wrap gap-5 mt-2">
                                            <div>
                                                <p class="text-xs text-slate-500">Data estimada</p>
                                                <p class="text-lg font-bold text-blue-700"
                                                    x-text="projecao?.data_conclusao ?? '—'"></p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-slate-500">Dias restantes</p>
                                                <p class="text-lg font-bold text-slate-800"
                                                    x-text="(projecao?.dias_restantes ?? 0) + ' dias'"></p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-slate-500">Ritmo médio/mês</p>
                                                <p class="text-base font-bold text-slate-700"
                                                    x-text="'R$ ' + formatarValor(projecao?.ritmo_mensal ?? 0)"></p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-slate-500">Confiança</p>
                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                                    :class="projecao?.confianca === 'alta' ?
                                                        'bg-green-100 text-green-700' :
                                                        'bg-amber-100 text-amber-700'"
                                                    x-text="projecao?.confianca === 'alta'
                                                      ? '🟢 Alta (3 medições)'
                                                      : '🟡 Média (2 medições)'">
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" @click="modalAjudaGrafico = true"
                                        class="shrink-0 px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50">
                                        ❓ Como é calculado?
                                    </button>
                                </div>
                            </div>

                            {{-- ── Gráfico 1: Linha — % executado ao longo das medições ── --}}
                            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h4 class="font-semibold text-slate-800 text-sm">
                                            📈 Evolução do Percentual Executado
                                        </h4>
                                        <p class="text-xs text-slate-400 mt-0.5">
                                            Progresso acumulado ao longo das medições
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-slate-500">
                                        <span class="flex items-center gap-1.5">
                                            <span class="w-3 h-0.5 bg-blue-500 inline-block rounded"></span>
                                            % Executado
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <span
                                                class="w-3 border-t-2 border-dashed border-slate-400 inline-block"></span>
                                            Meta (100%)
                                        </span>
                                    </div>
                                </div>
                                <div style="position:relative; height:220px">
                                    <canvas id="grafico-linha-{{ $obra->id }}"></canvas>
                                </div>
                            </div>

                            {{-- ── Gráfico 2: Barras — contratado × medido × saldo por contrato ── --}}
                            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h4 class="font-semibold text-slate-800 text-sm">
                                            💰 Contratado × Medido × Saldo por Contrato
                                        </h4>
                                        <p class="text-xs text-slate-400 mt-0.5">
                                            Comparativo financeiro de cada contrato da obra
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-slate-500">
                                        <span class="flex items-center gap-1.5">
                                            <span class="w-3 h-3 rounded bg-blue-200 inline-block"></span> Contratado
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <span class="w-3 h-3 rounded bg-green-500 inline-block"></span> Medido
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <span class="w-3 h-3 rounded bg-slate-300 inline-block"></span> Saldo
                                        </span>
                                    </div>
                                </div>
                                <div style="position:relative; height:220px">
                                    <canvas id="grafico-barras-{{ $obra->id }}"></canvas>
                                </div>
                            </div>

                            {{-- ── Gráfico 3: Misto — valor por medição + linha acumulada ── --}}
                            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h4 class="font-semibold text-slate-800 text-sm">
                                            📊 Valor Medido por Medição
                                        </h4>
                                        <p class="text-xs text-slate-400 mt-0.5">
                                            Valor financeiro individual + acumulado
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-slate-500">
                                        <span class="flex items-center gap-1.5">
                                            <span class="w-3 h-3 rounded bg-green-400 inline-block"></span> Por medição
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <span class="w-3 h-0.5 bg-indigo-500 inline-block rounded"></span> Acumulado
                                        </span>
                                    </div>
                                </div>
                                <div style="position:relative; height:200px">
                                    <canvas id="grafico-acumulado-{{ $obra->id }}"></canvas>
                                </div>
                            </div>

                        </div>{{-- /!semDados --}}

                    </div>{{-- /vista graficos --}}

                    {{-- ════════════════════════════════════════════
                |  VISTA: TABELA — listagem original de medições
                ════════════════════════════════════════════ --}}
                    <div x-show="vista === 'tabela'">

                        @if ($todasExecucoes->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50 border-b">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs text-slate-500">#</th>
                                            <th class="px-3 py-2 text-left text-xs text-slate-500">Data</th>
                                            <th class="px-3 py-2 text-left text-xs text-slate-500">Contrato / Empresa</th>
                                            <th class="px-3 py-2 text-left text-xs text-slate-500">Observação</th>
                                            <th class="px-3 py-2 text-right text-xs text-slate-500">Valor Medido</th>
                                            <th class="px-3 py-2 text-right text-xs text-slate-500">Saldo após</th>
                                            <th class="px-3 py-2 text-center text-xs text-slate-500">% Acum.</th>
                                            @if (auth()->user()->perfil !== 'operador')
                                                <th class="px-3 py-2 text-right text-xs text-slate-500">Ações</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        @foreach ($todasExecucoes as $idx => $item)
                                            @php $ex = $item['execucao']; @endphp
                                            <tr class="hover:bg-slate-50">

                                                <td class="px-3 py-2 text-slate-400 text-xs">
                                                    {{ $todasExecucoes->count() - $idx }}ª
                                                </td>

                                                <td class="px-3 py-2 text-slate-700 whitespace-nowrap font-medium">
                                                    {{ $ex->data_medicao?->format('d/m/Y') ?? '—' }}
                                                </td>

                                                <td class="px-3 py-2">
                                                    <p class="text-slate-700 font-medium text-xs">
                                                        {{ $item['contrato_label'] }}
                                                    </p>
                                                    <p class="text-xs text-slate-500">{{ $item['empresa'] }}</p>
                                                </td>

                                                <td class="px-3 py-2 text-slate-500 text-xs max-w-xs">
                                                    {{ Str::limit($ex->observacao ?? '—', 50) }}
                                                </td>

                                                <td
                                                    class="px-3 py-2 text-right font-semibold text-green-700 whitespace-nowrap">
                                                    R$ {{ number_format($ex->valor_medido ?? 0, 2, ',', '.') }}
                                                </td>

                                                <td
                                                    class="px-3 py-2 text-right whitespace-nowrap text-xs
                                                {{ ($ex->saldo_contratual ?? 1) <= 0 ? 'text-red-500 font-semibold' : 'text-slate-500' }}">
                                                    @if ($ex->saldo_contratual !== null)
                                                        R$ {{ number_format($ex->saldo_contratual, 2, ',', '.') }}
                                                    @else
                                                        <span class="text-slate-300">—</span>
                                                    @endif
                                                </td>

                                                <td class="px-3 py-2 text-center">
                                                    @if ($ex->percentual_executado !== null)
                                                        @php $p = (float) $ex->percentual_executado; @endphp
                                                        <div class="inline-flex flex-col items-center gap-1">
                                                            <span
                                                                class="text-xs font-semibold
                                                            {{ $p >= 80 ? 'text-green-700' : ($p >= 40 ? 'text-yellow-700' : 'text-blue-700') }}">
                                                                {{ number_format($p, 1) }}%
                                                            </span>
                                                            <div class="w-14 bg-slate-200 rounded-full h-1.5">
                                                                <div class="h-1.5 rounded-full
                                                                {{ $p >= 80 ? 'bg-green-500' : ($p >= 40 ? 'bg-yellow-500' : 'bg-blue-500') }}"
                                                                    style="width: {{ min($p, 100) }}%">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-slate-300 text-xs">—</span>
                                                    @endif
                                                </td>

                                                @if (auth()->user()->perfil !== 'operador')
                                                    <td class="px-3 py-2 text-right">
                                                        <a href="{{ route('obras.execucoes.edit', [$obra, $ex]) }}"
                                                            class="px-2 py-1 text-xs rounded border border-blue-200 text-blue-700 hover:bg-blue-50">
                                                            ✏️
                                                        </a>
                                                    </td>
                                                @endif

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-10 text-slate-400">
                                <p class="text-2xl mb-2">📊</p>
                                <p class="text-sm">Nenhuma medição registrada para esta obra.</p>
                                @if ($obra->contratos->count() === 0)
                                    <p class="text-xs mt-1">
                                        É necessário ter ao menos um contrato para registrar medições.
                                    </p>
                                @elseif (auth()->user()->perfil !== 'operador')
                                    <a href="{{ route('obras.execucoes.create', $obra) }}"
                                        class="inline-block mt-3 text-xs text-green-600 hover:underline">
                                        ➕ Registrar primeira medição
                                    </a>
                                @endif
                            </div>
                        @endif

                    </div>{{-- /vista tabela --}}

                    {{-- ── Modal: explicação do algoritmo de projeção ── --}}
                    <div x-show="modalAjudaGrafico" x-cloak
                        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
                            <div
                                class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white px-6 py-4 rounded-t-2xl flex justify-between items-center">
                                <h3 class="font-semibold">📐 Como é calculada a projeção?</h3>
                                <button @click="modalAjudaGrafico = false"
                                    class="text-white/70 hover:text-white text-xl">×</button>
                            </div>
                            <div class="p-6 text-sm text-slate-700 space-y-4">
                                <div class="p-3 bg-blue-50 rounded-lg border border-blue-100">
                                    <strong class="block text-blue-800 mb-1">Ritmo médio de medição</strong>
                                    Calcula o valor medido total nas últimas 3 medições dividido pelo número
                                    de dias entre elas, obtendo o ritmo médio diário em R$/dia.
                                </div>
                                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                                    <strong class="block text-slate-800 mb-1">Dias restantes</strong>
                                    Divide o saldo contratual atual pelo ritmo médio diário. O resultado
                                    é o número estimado de dias até a conclusão financeira.
                                </div>
                                <div class="p-3 bg-amber-50 rounded-lg border border-amber-100">
                                    <strong class="block text-amber-800 mb-1">Nível de confiança</strong>
                                    <strong>Alta</strong> — baseada em 3 ou mais medições.<br>
                                    <strong>Média</strong> — baseada em apenas 2 medições.<br>
                                    Com mais medições ao longo do tempo, a projeção fica mais precisa.
                                </div>
                                <p class="text-xs text-slate-400">
                                    ⚠️ A projeção é uma estimativa baseada no ritmo histórico. Paralisações,
                                    aditivos contratuais ou aceleração de obras afetam o resultado real.
                                </p>
                            </div>
                            <div class="px-6 py-4 border-t text-right">
                                <button @click="modalAjudaGrafico = false"
                                    class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">
                                    Entendi 👍
                                </button>
                            </div>
                        </div>
                    </div>

                </div>{{-- /aba execucoes --}}

                {{-- =================================================
            |  ABA: DOCUMENTOS — arquivos da obra e das medições
            ================================================== --}}
                <div x-show="aba === 'documentos'" x-cloak>

                    @php
                        // Ícone por extensão de arquivo
                        function iconeDoc(string $nome): string
                        {
                            $ext = strtolower(pathinfo($nome, PATHINFO_EXTENSION));
                            return match ($ext) {
                                'pdf' => '📄',
                                'jpg', 'jpeg', 'png', 'gif', 'webp' => '🖼️',
                                'xlsx', 'xls', 'csv' => '📊',
                                'docx', 'doc' => '📝',
                                default => '📎',
                            };
                        }

                        // Coleta documentos de medições via contratos → execucoes → documentos
                        $docsMedicao = $obra->contratos->flatMap(
                            fn($c) => $c->execucoes->flatMap(
                                fn($ex) => $ex->documentos->map(
                                    fn($d) => [
                                        'doc' => $d,
                                        'medicao_data' => $ex->data_medicao->format('d/m/Y'),
                                        'medicao_id' => $ex->id,
                                        'contrato_label' => $c->numero_contrato_ano ?? 'Contrato #' . $c->id,
                                        'rota_download' => route('obras.execucoes.documentos.download', [
                                            $obra,
                                            $ex,
                                            $d,
                                        ]),
                                        'rota_destroy' => route('obras.execucoes.documentos.destroy', [$obra, $ex, $d]),
                                    ],
                                ),
                            ),
                        );

                        $totalDocs = $obra->documentos->count() + $docsMedicao->count();
                    @endphp

                    {{-- Cabeçalho com botão "Anexar à Obra" --}}
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-sm font-semibold text-slate-500 uppercase">
                            Documentos
                            <span class="ml-1 text-xs font-normal text-slate-400">({{ $totalDocs }} no total)</span>
                        </h3>
                        @if (auth()->user()->perfil !== 'operador')
                            <div x-data="{ aberto: false }">
                                <button @click="aberto = !aberto"
                                    class="px-3 py-1.5 text-xs rounded-lg bg-slate-700 border border-slate-600 text-white hover:bg-slate-800">
                                    📎 Anexar à Obra
                                </button>
                                <div x-show="aberto" x-cloak
                                    class="mt-3 bg-slate-50 border border-slate-200 rounded-lg p-4">
                                    <form method="POST" action="{{ route('obras.documentos.store', $obra) }}"
                                        enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3 items-end">
                                        @csrf
                                        <div class="flex-1 space-y-1">
                                            <label class="text-xs text-slate-600 font-medium">Arquivo</label>
                                            <input type="file" name="arquivo" required
                                                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 bg-white">
                                        </div>
                                        <div class="flex-1 space-y-1">
                                            <label class="text-xs text-slate-600 font-medium">Tipo</label>
                                            <select name="tipo"
                                                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2">
                                                <option value="contrato">Contrato</option>
                                                <option value="ata">Ata / Ofício</option>
                                                <option value="foto">Foto / Registro</option>
                                                <option value="outros" selected>Outros</option>
                                            </select>
                                        </div>
                                        <div class="flex-1 space-y-1">
                                            <label class="text-xs text-slate-600 font-medium">Descrição</label>
                                            <input type="text" name="descricao"
                                                placeholder="Ex: Contrato assinado, Ata nº 5..."
                                                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2">
                                        </div>
                                        <button type="submit"
                                            class="px-4 py-2 text-sm bg-slate-700 text-white rounded-lg hover:bg-slate-800 whitespace-nowrap">
                                            ✔ Enviar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Seção 1: documentos diretamente da obra --}}
                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                Documentos da Obra ({{ $obra->documentos->count() }})
                            </h4>
                        </div>

                        @forelse($obra->documentos as $doc)
                            <div class="flex items-center justify-between py-3 border-b last:border-0 text-sm">
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <span class="text-xl shrink-0">{{ iconeDoc($doc->nome_original) }}</span>
                                    <div class="min-w-0">
                                        <p class="font-medium text-slate-800 truncate">
                                            {{ $doc->descricao ?? $doc->nome_original }}
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            {{ $doc->tipo_label }}
                                            · {{ $doc->tamanho_legível }}
                                            · {{ $doc->created_at->format('d/m/Y H:i') }}
                                            · {{ $doc->usuario->name ?? '—' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex gap-2 shrink-0 ml-3">
                                    <a href="{{ route('obras.documentos.download', [$obra, $doc]) }}"
                                        class="px-3 py-1.5 text-xs rounded-lg border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                        ⬇️ Baixar
                                    </a>
                                    @if (auth()->user()->perfil === 'admin')
                                        <button type="button"
                                            @click="modalDelete = true; urlDelete = '{{ route('obras.documentos.destroy', [$obra, $doc]) }}'"
                                            class="px-2 py-1.5 text-xs rounded-lg border border-red-200 text-red-700 bg-red-50 hover:bg-red-100">
                                            🗑
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400 italic py-2 pl-4">
                                Nenhum documento da obra. Use "Anexar à Obra" para adicionar.
                            </p>
                        @endforelse
                    </div>

                    {{-- Seção 2: documentos anexados às medições --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                Documentos de Medições ({{ $docsMedicao->count() }})
                            </h4>
                        </div>

                        @if ($docsMedicao->isEmpty())
                            <p class="text-sm text-slate-400 italic py-2 pl-4">
                                Nenhum documento de medição. Anexe documentos ao registrar ou editar uma medição.
                            </p>
                        @else
                            @php $docsPorMedicao = $docsMedicao->groupBy('medicao_id'); @endphp

                            @foreach ($docsPorMedicao as $medicaoId => $itens)
                                @php $primeiro = $itens->first(); @endphp
                                <div class="bg-slate-50 border border-slate-200 rounded-lg mb-1 overflow-hidden">

                                    {{-- Cabeçalho do grupo de medição --}}
                                    <div class="px-4 py-2 flex items-center gap-3 bg-slate-100 border-b border-slate-200">
                                        <span class="text-xs font-semibold text-slate-600">
                                            📊 Medição {{ $primeiro['medicao_data'] }}
                                        </span>
                                        <span class="text-xs text-slate-500">
                                            {{ $primeiro['contrato_label'] }}
                                        </span>
                                        <span class="ml-auto text-xs text-slate-400">
                                            {{ $itens->count() }} arquivo(s)
                                        </span>
                                        @if (auth()->user()->perfil !== 'operador')
                                            <a href="{{ route('obras.execucoes.edit', [$obra, $medicaoId]) }}"
                                                class="text-xs text-blue-600 hover:underline">
                                                ✏️ editar medição
                                            </a>
                                        @endif
                                    </div>

                                    {{-- Arquivos desta medição --}}
                                    @foreach ($itens as $item)
                                        @php $doc = $item['doc']; @endphp
                                        <div
                                            class="flex items-center justify-between px-4 py-2.5 border-b last:border-0 text-sm hover:bg-white transition">
                                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                                <span class="text-lg shrink-0">{{ iconeDoc($doc->nome_original) }}</span>
                                                <div class="min-w-0">
                                                    <p class="font-medium text-slate-700 truncate">
                                                        {{ $doc->descricao ?? $doc->nome_original }}
                                                    </p>
                                                    <p class="text-xs text-slate-500">
                                                        {{ $doc->tipo_label }}
                                                        · {{ $doc->tamanho_legível }}
                                                        · {{ $doc->created_at->format('d/m/Y H:i') }}
                                                        · {{ $doc->usuario->name ?? '—' }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex gap-2 shrink-0 ml-3">
                                                <a href="{{ $item['rota_download'] }}"
                                                    class="px-3 py-1.5 text-xs rounded-lg border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                                    ⬇️ Baixar
                                                </a>
                                                @if (auth()->user()->perfil === 'admin')
                                                    <button type="button"
                                                        @click="modalDelete = true; urlDelete = '{{ $item['rota_destroy'] }}'"
                                                        class="px-2 py-1.5 text-xs rounded-lg border border-red-200 text-red-700 bg-red-50 hover:bg-red-100">
                                                        🗑
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @endif
                    </div>

                    {{-- Estado vazio total --}}
                    @if ($totalDocs === 0)
                        <div class="text-center py-10 text-slate-400">
                            <p class="text-3xl mb-2">📎</p>
                            <p class="text-sm">Nenhum documento encontrado.</p>
                            <p class="text-xs mt-1">
                                Anexe documentos à obra usando o botão acima ou ao registrar medições.
                            </p>
                        </div>
                    @endif

                </div>{{-- /aba documentos --}}

            </div>{{-- /p-6 --}}
        </div>{{-- /painel abas --}}

        {{-- =========================================================
    |  MODAL: GERENCIAR CONVÊNIOS
    |  Abre via @click="modalConvenios = true" no botão da aba convênios
    ========================================================== --}}
        <div x-show="modalConvenios" x-cloak
            class="fixed inset-0 bg-black/50 flex items-start justify-center z-50 overflow-y-auto py-8 px-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl" @click.outside="modalConvenios = false">

                <div
                    class="bg-gradient-to-r from-indigo-600 to-violet-600 text-white px-6 py-4 rounded-t-2xl flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">🤝 Gerenciar Convênios</h3>
                        <p class="text-sm opacity-80 mt-0.5">Selecione os convênios vinculados a esta obra</p>
                    </div>
                    <button @click="modalConvenios = false"
                        class="text-white/70 hover:text-white text-2xl leading-none">×</button>
                </div>

                <form method="POST" action="{{ route('obras.convenios.sync', $obra) }}">
                    @csrf
                    @method('PUT')

                    <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">

                        @if ($todosConvenios->isEmpty())
                            <div class="text-center py-8 text-slate-400">
                                <p class="text-2xl mb-2">🤝</p>
                                <p class="text-sm">Nenhum convênio cadastrado no sistema.</p>
                                <a href="{{ route('convenios.create') }}"
                                    class="inline-block mt-3 text-xs text-indigo-600 hover:underline">
                                    ➕ Cadastrar primeiro convênio
                                </a>
                            </div>
                        @else
                            @php $conveniosSelecionados = $obra->convenios->pluck('id')->toArray(); @endphp
                            <div class="space-y-2">
                                @foreach ($todosConvenios as $conv)
                                    @php $selecionado = in_array($conv->id, $conveniosSelecionados); @endphp
                                    <label
                                        class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition
                                    {{ $selecionado ? 'bg-indigo-50 border-indigo-300' : 'bg-white border-slate-200 hover:bg-slate-50' }}">

                                        <input type="checkbox" name="convenios[]" value="{{ $conv->id }}"
                                            {{ $selecionado ? 'checked' : '' }}
                                            class="mt-0.5 rounded text-indigo-600 focus:ring-indigo-500">

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-medium text-slate-800 text-sm">
                                                    {{ $conv->numero_convenio_ano ?? 'Convênio #' . $conv->id }}
                                                </span>
                                                @if ($conv->categoria)
                                                    <span
                                                        class="px-2 py-0.5 rounded-full text-xs bg-indigo-100 text-indigo-700">
                                                        {{ $conv->categoria->nome }}
                                                    </span>
                                                @endif
                                                @if ($conv->vigencia)
                                                    @php $venc = $conv->vigencia->isPast(); @endphp
                                                    <span
                                                        class="text-xs {{ $venc ? 'text-red-500' : 'text-green-600' }}">
                                                        {{ $venc ? '⚠️ Vencido' : '✔ Vigente' }}
                                                        · {{ $conv->vigencia->format('d/m/Y') }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if ($conv->descricao)
                                                <p class="text-xs text-slate-500 mt-0.5 truncate">
                                                    {{ Str::limit($conv->descricao, 80) }}
                                                </p>
                                            @endif
                                            @if ($conv->orgaoFinanciador)
                                                <p class="text-xs text-slate-400 mt-0.5">
                                                    🏛️ {{ $conv->orgaoFinanciador->nome }}
                                                </p>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                    </div>

                    <div class="px-6 py-4 border-t bg-slate-50 rounded-b-2xl flex justify-between items-center">
                        <span class="text-xs text-slate-500">
                            {{ $obra->convenios->count() }} convênio(s) atualmente vinculado(s)
                        </span>
                        <div class="flex gap-3">
                            <button type="button" @click="modalConvenios = false"
                                class="px-4 py-2 text-sm border rounded-lg text-slate-600 hover:bg-slate-100">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="px-5 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                ✔ Salvar vínculos
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- =========================================================
    |  MODAL: EXCLUIR DOCUMENTO
    |  urlDelete preenchido via @click nos botões 🗑 de cada doc
    ========================================================== --}}
        <div x-show="modalDelete" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md text-center">
                <h3 class="text-lg font-semibold text-red-600 mb-4">Confirmar exclusão</h3>
                <p class="text-sm text-slate-600 mb-6">O documento será removido permanentemente.</p>
                <div class="flex justify-center gap-3">
                    <button @click="modalDelete = false" class="px-4 py-2 border rounded">Cancelar</button>
                    <form :action="urlDelete" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="px-5 py-2 bg-red-600 text-white rounded">Excluir</button>
                    </form>
                </div>
            </div>
        </div>

    </div>{{-- /x-data raiz --}}

    {{-- =========================================================
|  CHART.JS — CDN (carregado uma vez por página via @once)
|  Fase 4: gráficos de evolução da aba Execuções
========================================================== --}}
    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    @endonce

    {{-- =========================================================
|  Alpine.js: componente execucoesGrafico()
|
|  Responsabilidades:
|    • fetch() do endpoint obras.grafico → JSON
|    • Renderiza 3 gráficos Chart.js nos <canvas> da aba
|    • Exibe card de projeção de conclusão
|    • Gerencia estados: carregando / erro / semDados
|
|  Parâmetros recebidos no x-data da aba:
|    obraId   → $obra->id  (para nomear os <canvas>)
|    urlDados → route('obras.grafico', $obra)
========================================================== --}}
    <script>
        function execucoesGrafico(obraId, urlDados) {
            return {
                // ── Estado ──────────────────────────────────────────────
                vista: 'graficos', // 'graficos' | 'tabela'
                carregando: true,
                erro: null,
                semDados: false,
                projecao: null,
                modalAjudaGrafico: false,

                // ── Instâncias Chart.js e flag de inicialização ─────────
                chartLinha: null,
                chartBarras: null,
                chartAcumulado: null,
                _jaIniciou: false, // impede dupla execução do init()

                // ── Destrói charts existentes para evitar "Canvas already in use" ─
                destruirCharts() {
                    if (this.chartLinha) {
                        this.chartLinha.destroy();
                        this.chartLinha = null;
                    }
                    if (this.chartBarras) {
                        this.chartBarras.destroy();
                        this.chartBarras = null;
                    }
                    if (this.chartAcumulado) {
                        this.chartAcumulado.destroy();
                        this.chartAcumulado = null;
                    }
                },

                // ── Inicialização: busca dados e renderiza ────────────────
                async init() {
                    // Garante que só roda uma vez (Alpine pode chamar x-init mais de uma vez)
                    if (this._jaIniciou) return;
                    this._jaIniciou = true;

                    // Destrói qualquer chart anterior antes de criar novos
                    this.destruirCharts();

                    try {
                        const res = await fetch(urlDados, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        // Se o servidor retornou HTML (ex: página de erro 500 do Laravel)
                        // em vez de JSON, isso seria capturado aqui
                        const contentType = res.headers.get('content-type') || '';
                        if (!contentType.includes('application/json')) {
                            const texto = await res.text();
                            console.error('[grafico] Resposta não-JSON recebida:', res.status, texto.slice(0, 300));
                            this.erro =
                                `Erro ${res.status}: o servidor retornou uma resposta inesperada. Veja o console (F12) para detalhes.`;
                            this.carregando = false;
                            return;
                        }

                        const data = await res.json();

                        // Se o controller retornou { ok: false, erro: "..." }
                        if (data.ok === false) {
                            console.error('[grafico] Erro retornado pelo servidor:', data.erro);
                            this.erro = 'Erro no servidor: ' + (data.erro ?? 'desconhecido');
                            this.carregando = false;
                            return;
                        }

                        // Sem medições — exibe estado vazio
                        if (!data.medicoes || data.medicoes.length === 0) {
                            this.semDados = true;
                            this.carregando = false;
                            return;
                        }

                        this.projecao = data.projecao;
                        this.carregando = false;

                        // Aguarda Alpine renderizar os <canvas> antes de desenhar
                        await this.$nextTick();

                        // Destrói novamente por garantia (caso $nextTick dispare re-render)
                        this.destruirCharts();

                        this.renderLinha(data);
                        this.renderBarras(data);
                        this.renderAcumulado(data);

                    } catch (e) {
                        // Erro de rede ou JSON inválido
                        console.error('[grafico] Exceção no fetch:', e);
                        this.erro = 'Não foi possível carregar os dados. Verifique sua conexão e tente recarregar.';
                        this.carregando = false;
                    }
                },

                // ── Helpers de formatação ─────────────────────────────────
                formatarValor(v) {
                    if (v >= 1_000_000) return (v / 1_000_000).toFixed(1).replace('.', ',') + ' M';
                    if (v >= 1_000) return (v / 1_000).toFixed(0) + ' k';
                    return Number(v).toFixed(0);
                },

                formatarBRL(v) {
                    return 'R$ ' + Number(v).toLocaleString('pt-BR', {
                        minimumFractionDigits: 2
                    });
                },

                // ── Gráfico 1: Linha — % executado acumulado ─────────────
                renderLinha(data) {
                    const el = document.getElementById(`grafico-linha-${obraId}`);
                    if (!el) return;

                    const labels = data.serie_acumulada.map(m => m.data);
                    const pcts = data.serie_acumulada.map(m => m.percentual);
                    const meta = labels.map(() => 100);

                    this.chartLinha = new Chart(el, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [{
                                    label: '% Executado',
                                    data: pcts,
                                    borderColor: '#2563eb',
                                    backgroundColor: 'rgba(37,99,235,0.1)',
                                    borderWidth: 2.5,
                                    pointRadius: 5,
                                    pointBackgroundColor: '#2563eb',
                                    pointHoverRadius: 7,
                                    tension: 0.3,
                                    fill: true,
                                },
                                {
                                    label: 'Meta (100%)',
                                    data: meta,
                                    borderColor: '#94a3b8',
                                    borderWidth: 1.5,
                                    borderDash: [6, 4],
                                    pointRadius: 0,
                                    fill: false,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => ctx.dataset.label + ': ' + ctx.parsed.y.toFixed(1) + '%',
                                    },
                                },
                            },
                            scales: {
                                y: {
                                    min: 0,
                                    max: 110,
                                    ticks: {
                                        callback: v => v + '%',
                                        font: {
                                            size: 11
                                        }
                                    },
                                    grid: {
                                        color: '#f1f5f9'
                                    },
                                },
                                x: {
                                    ticks: {
                                        font: {
                                            size: 10
                                        },
                                        maxRotation: 30
                                    },
                                    grid: {
                                        display: false
                                    },
                                },
                            },
                        },
                    });
                },

                // ── Gráfico 2: Barras agrupadas — contratado × medido × saldo ─
                renderBarras(data) {
                    const el = document.getElementById(`grafico-barras-${obraId}`);
                    if (!el) return;

                    const self = this;
                    const labels = data.barras.map(b => b.label || b.empresa);
                    const contratado = data.barras.map(b => b.contratado);
                    const medido = data.barras.map(b => b.medido);
                    const saldo = data.barras.map(b => b.saldo);

                    this.chartBarras = new Chart(el, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                    label: 'Contratado',
                                    data: contratado,
                                    backgroundColor: 'rgba(147,197,253,0.7)',
                                    borderColor: '#3b82f6',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                },
                                {
                                    label: 'Medido',
                                    data: medido,
                                    backgroundColor: 'rgba(34,197,94,0.75)',
                                    borderColor: '#16a34a',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                },
                                {
                                    label: 'Saldo',
                                    data: saldo,
                                    backgroundColor: 'rgba(203,213,225,0.7)',
                                    borderColor: '#94a3b8',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        font: {
                                            size: 11
                                        },
                                        boxWidth: 12
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => ctx.dataset.label + ': ' + self.formatarBRL(ctx.parsed.y),
                                    },
                                },
                            },
                            scales: {
                                y: {
                                    ticks: {
                                        callback: v => 'R$ ' + self.formatarValor(v),
                                        font: {
                                            size: 10
                                        }
                                    },
                                    grid: {
                                        color: '#f1f5f9'
                                    },
                                },
                                x: {
                                    ticks: {
                                        font: {
                                            size: 10
                                        }
                                    },
                                    grid: {
                                        display: false
                                    },
                                },
                            },
                        },
                    });
                },

                // ── Gráfico 3: Misto — barra (por medição) + linha (acumulado) ─
                renderAcumulado(data) {
                    const el = document.getElementById(`grafico-acumulado-${obraId}`);
                    if (!el) return;

                    const self = this;
                    const labels = data.serie_acumulada.map(m => m.data);
                    const acumulado = data.serie_acumulada.map(m => m.acumulado);
                    const porMedicao = data.medicoes.map(m => m.valor_medido);

                    this.chartAcumulado = new Chart(el, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                    type: 'bar',
                                    label: 'Valor por medição',
                                    data: porMedicao,
                                    backgroundColor: 'rgba(74,222,128,0.6)',
                                    borderColor: '#22c55e',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                    yAxisID: 'y',
                                },
                                {
                                    type: 'line',
                                    label: 'Acumulado',
                                    data: acumulado,
                                    borderColor: '#6366f1',
                                    backgroundColor: 'rgba(99,102,241,0.08)',
                                    borderWidth: 2.5,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#6366f1',
                                    tension: 0.3,
                                    fill: true,
                                    yAxisID: 'y',
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        font: {
                                            size: 11
                                        },
                                        boxWidth: 12
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => ctx.dataset.label + ': ' + self.formatarBRL(ctx.parsed.y),
                                    },
                                },
                            },
                            scales: {
                                y: {
                                    ticks: {
                                        callback: v => 'R$ ' + self.formatarValor(v),
                                        font: {
                                            size: 10
                                        }
                                    },
                                    grid: {
                                        color: '#f1f5f9'
                                    },
                                },
                                x: {
                                    ticks: {
                                        font: {
                                            size: 10
                                        },
                                        maxRotation: 30
                                    },
                                    grid: {
                                        display: false
                                    },
                                },
                            },
                        },
                    });
                },
            };
        }
    </script>

@endsection
