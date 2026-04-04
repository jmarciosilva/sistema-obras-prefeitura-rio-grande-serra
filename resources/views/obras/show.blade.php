@extends('layouts.app')

@section('title', 'Detalhe da Obra')
@section('subtitle', 'Acompanhamento completo da obra')

@section('content')

    <div x-data="{ aba: '{{ session('aba', 'geral') }}', ajuda: false, modalDelete: false, urlDelete: '', modalConvenios: false }">

        {{-- =========================================================
        | HEADER
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

            {{-- BOTÕES PRINCIPAIS --}}
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

        {{-- FLASH --}}
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
        | BLOCO DE AJUDA
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
        | ETAPAS DA OBRA
        ========================================================== --}}
        <div class="mb-6 bg-white p-4 rounded-lg shadow">
            <h3 class="text-sm font-semibold text-slate-600 mb-3">🚦 Etapas da Obra</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">

                {{-- PASSO 1: CONVÊNIOS --}}
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

                {{-- PASSO 2: EMPRESAS --}}
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

                {{-- PASSO 3: CONTRATOS --}}
                <div
                    class="p-3 rounded-lg border
                    {{ $obra->contratos->count() > 0 ? 'bg-green-50 border-green-300' : 'bg-red-50 border-red-300' }}">
                    <div class="font-medium">3️⃣ Contratos</div>
                    @if ($obra->contratos->count() > 0)
                        <div class="text-xs text-green-600">✔ {{ $obra->contratos->count() }} contrato(s) cadastrado(s)
                        </div>
                    @else
                        <div class="text-xs text-red-600">Nenhum contrato cadastrado</div>
                    @endif
                </div>

            </div>
        </div>

        {{-- =========================================================
        | KPIs
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
                    class="text-lg font-semibold {{ $saldo <= 0 && $valorContrato > 0 ? 'text-red-600' : 'text-blue-700' }}">
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
                    class="text-lg font-semibold {{ $obra->convenios->count() > 0 ? 'text-indigo-600' : 'text-slate-400' }}">
                    {{ $obra->convenios->count() }}
                </p>
            </div>

        </div>

        {{-- =========================================================
        | BARRA DE PROGRESSO
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
        | ABAS
        ========================================================== --}}
        <div class="bg-white rounded-lg shadow">

            {{-- NAVEGAÇÃO --}}
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

            <div class="p-6">

                {{-- ─────────────────────────────────────────────
                ABA: GERAL
                ───────────────────────────────────────────── --}}
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

                {{-- ─────────────────────────────────────────────
                ABA: CONVÊNIOS
                ───────────────────────────────────────────── --}}
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

                {{-- ─────────────────────────────────────────────
                ABA: CONTRATOS
                ───────────────────────────────────────────── --}}
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

                {{-- ─────────────────────────────────────────────
                ABA: EXECUÇÕES
                ───────────────────────────────────────────── --}}
                <div x-show="aba === 'execucoes'" x-cloak>

                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-slate-500 uppercase">
                            Execuções / Medições
                        </h3>
                        @if (auth()->user()->perfil !== 'operador')
                            <a href="{{ route('obras.execucoes.create', $obra) }}"
                                class="px-3 py-1.5 text-xs rounded-lg bg-green-50 border border-green-200 text-green-700 hover:bg-green-100">
                                ➕ Nova Medição
                            </a>
                        @endif
                    </div>

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

                        $totalMedido = $todasExecucoes->sum(fn($item) => $item['execucao']->valor_medido ?? 0);
                        $valorContrato = $obra->valor_contratado;
                        $pctGeral = $valorContrato > 0 ? min(($totalMedido / $valorContrato) * 100, 100) : 0;
                        $saldoGeral = max($valorContrato - $totalMedido, 0);
                    @endphp

                    @if ($todasExecucoes->count() > 0)

                        {{-- RESUMO 4 CARDS --}}
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
                                                    {{ $item['contrato_label'] }}</p>
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
                                <p class="text-xs mt-1 text-slate-400">
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

                </div>

                {{-- ─────────────────────────────────────────────
                ABA: DOCUMENTOS
                ───────────────────────────────────────────── --}}
                <div x-show="aba === 'documentos'" x-cloak>

                    @php
                        // Helper de ícone reutilizado nesta aba
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

                        // Coleta documentos de medições (via contratos.execucoes.documentos)
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

                    {{-- CABEÇALHO --}}
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

                    {{-- ════════════════════════════════════════
                    SEÇÃO 1 — DOCUMENTOS DA OBRA
                    ════════════════════════════════════════ --}}
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

                    {{-- ════════════════════════════════════════
                    SEÇÃO 2 — DOCUMENTOS DE MEDIÇÕES
                    ════════════════════════════════════════ --}}
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
                            {{-- Agrupa por medição para exibição hierárquica --}}
                            @php
                                $docsPorMedicao = $docsMedicao->groupBy('medicao_id');
                            @endphp

                            @foreach ($docsPorMedicao as $medicaoId => $itens)
                                @php $primeiro = $itens->first(); @endphp

                                {{-- CABEÇALHO DA MEDIÇÃO --}}
                                <div class="bg-slate-50 border border-slate-200 rounded-lg mb-1 overflow-hidden">
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

                                    {{-- ARQUIVOS DESTA MEDIÇÃO --}}
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

                    {{-- ESTADO VAZIO TOTAL --}}
                    @if ($totalDocs === 0)
                        <div class="text-center py-10 text-slate-400">
                            <p class="text-3xl mb-2">📎</p>
                            <p class="text-sm">Nenhum documento encontrado.</p>
                            <p class="text-xs mt-1">
                                Anexe documentos à obra usando o botão acima ou ao registrar medições.
                            </p>
                        </div>
                    @endif

                </div>

            </div>
        </div>

        {{-- =========================================================
        | MODAL: GERENCIAR CONVÊNIOS
        ========================================================== --}}
        <div x-show="modalConvenios" x-cloak
            class="fixed inset-0 bg-black/50 flex items-start justify-center z-50 overflow-y-auto py-8 px-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl" @click.outside="modalConvenios = false">

                {{-- HEADER --}}
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
                            @php
                                $conveniosSelecionados = $obra->convenios->pluck('id')->toArray();
                            @endphp

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
        | MODAL: EXCLUIR DOCUMENTO
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

    </div>

@endsection
