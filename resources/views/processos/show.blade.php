@extends('layouts.app')

@section('title', 'Visualizar Processo')
@section('subtitle', 'Detalhes e linha do tempo de trâmites')

@section('content')

    <div x-data="{ modalAjuda: false, modalDelete: false, modalDeleteTramite: false, urlDeleteTramite: '' }">

        {{-- =========================================================
        BREADCRUMB + AJUDA
        ========================================================= --}}
        <div class="flex items-center justify-between mb-6">

            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('processos.index') }}" class="hover:text-slate-900">Processos</a>
                <span>›</span>
                <span class="text-slate-900 font-medium">
                    {{ $processo->processo_numero }}
                </span>
            </div>

            <button type="button" @click="modalAjuda = true"
                class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                ❓ Ajuda
            </button>

        </div>

        {{-- BOTÃO VOLTAR --}}
        <div class="mb-6">
            <a href="{{ route('processos.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                   text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-100 transition">
                ← Voltar
            </a>
        </div>

        {{-- =========================================================
        ALERTA DE PENDÊNCIA
        ========================================================= --}}
        @if ($processo->motivo_pendencia)
            <div class="mb-6 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">
                ⚠️ <strong>Pendência:</strong> {{ $processo->motivo_pendencia }}
            </div>
        @endif

        <div class="flex justify-center">
            <div class="w-full max-w-4xl space-y-6">

                {{-- =========================================================
                CARD — DADOS DO PROCESSO
                ========================================================= --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                    {{-- HEADER --}}
                    <div class="px-8 py-6 border-b border-slate-200">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-2xl">
                                    🗂️
                                </div>
                                <div>
                                    <h2 class="text-xl font-semibold text-slate-900">
                                        {{ $processo->processo_numero }}
                                    </h2>
                                    <p class="text-sm text-slate-600 mt-1">
                                        {{ $processo->tipoProcesso->nome ?? '—' }}
                                    </p>
                                </div>
                            </div>

                            <div class="text-right space-y-2">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-medium text-white"
                                    style="background-color: {{ $processo->faseAtual->cor ?? '#64748b' }}">
                                    {{ $processo->faseAtual->nome ?? '—' }}
                                </span>
                                <div>
                                    <span
                                        class="inline-block px-2 py-0.5 rounded text-xs
                                        {{ $processo->situacao === 'arquivado' ? 'bg-slate-200 text-slate-600' : 'bg-green-100 text-green-700' }}">
                                        {{ $processo->situacao_label }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- DADOS --}}
                    <div class="px-8 py-6 space-y-6">

                        <div>
                            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">
                                Identificação
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                                <div>
                                    <p class="text-slate-500">Requerente</p>
                                    <p class="font-medium text-slate-800">{{ $processo->requerente }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">CEP</p>
                                    <p class="font-medium text-slate-800">{{ $processo->cep ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-slate-500">Endereço</p>
                                <p class="font-medium text-slate-800">{{ $processo->endereco_completo ?? '—' }}</p>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">
                                Situação Atual
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-sm">
                                <div>
                                    <p class="text-slate-500">Entrada</p>
                                    <p class="font-medium text-slate-800">
                                        {{ $processo->data_entrada?->format('d/m/Y') ?? '—' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Responsável Técnico</p>
                                    <p class="font-medium text-slate-800">
                                        {{ $processo->responsavelTecnico->nome ?? '—' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Setor Atual</p>
                                    <p class="font-medium text-slate-800">{{ $processo->setor_atual ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Caixa Atual</p>
                                    <p class="font-medium text-slate-800">{{ $processo->caixa_atual ?? '—' }}</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- AÇÕES --}}
                    <div class="px-8 py-5 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-between items-center">

                        @if (auth()->user()->perfil === 'admin')
                            <button type="button" @click="modalDelete = true"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium
                                   text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 transition">
                                🗑 Excluir Processo
                            </button>
                        @else
                            <span></span>
                        @endif

                        @if (auth()->user()->perfil !== 'operador' && auth()->user()->perfil !== 'secretario')
                            <a href="{{ route('processos.edit', $processo) }}"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium
                                   text-white bg-blue-600 hover:bg-blue-700 transition">
                                ✏️ Editar Processo
                            </a>
                        @endif

                    </div>

                </div>

                {{-- =========================================================
                CARD — NOVO TRÂMITE
                ========================================================= --}}
                @if (auth()->user()->perfil !== 'operador' && auth()->user()->perfil !== 'secretario')
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                        <div class="px-8 py-5 border-b">
                            <h3 class="text-base font-semibold text-slate-800">🔄 Registrar Trâmite</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Escolha a fase atual do processo e descreva a movimentação
                            </p>
                        </div>

                        <form method="POST" action="{{ route('processos.tramites.store', $processo) }}" class="p-8 space-y-4">
                            @csrf

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Fase *</label>
                                    <select name="fase_id" required
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                        <option value="">— Selecione a fase —</option>
                                        @foreach ($fasesProcesso as $fase)
                                            <option value="{{ $fase->id }}"
                                                {{ old('fase_id', $processo->fase_atual_id) == $fase->id ? 'selected' : '' }}>
                                                {{ $fase->nome }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Data</label>
                                    <input type="date" name="data" value="{{ old('data', now()->format('Y-m-d')) }}"
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                </div>

                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">Descrição *</label>
                                <textarea name="descricao" required rows="3"
                                    placeholder="Ex: Notificado via e-mail em 21/10 — aguardar devolutiva do técnico"
                                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">{{ old('descricao') }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Setor de Destino</label>
                                    <input type="text" name="setor_destino" list="setores-sugeridos"
                                        value="{{ old('setor_destino') }}" placeholder="Ex: CTM"
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    <datalist id="setores-sugeridos">
                                        @foreach (\App\Models\Tramite::$setoresSugeridos as $setor)
                                            <option value="{{ $setor }}"></option>
                                        @endforeach
                                    </datalist>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Tipo de Evento</label>
                                    <input type="text" name="tipo_evento" list="eventos-sugeridos"
                                        value="{{ old('tipo_evento') }}" placeholder="Ex: Vistoria"
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    <datalist id="eventos-sugeridos">
                                        @foreach (\App\Models\Tramite::$tiposEventoSugeridos as $evento)
                                            <option value="{{ $evento }}"></option>
                                        @endforeach
                                    </datalist>
                                </div>

                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">Motivo da Pendência</label>
                                <input type="text" name="motivo_pendencia" value="{{ old('motivo_pendencia') }}"
                                    placeholder="Preencha se este trâmite deixa o processo parado por algum motivo"
                                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-slate-400">
                                    Ao preencher, este texto passa a ser exibido como pendência do processo.
                                </p>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit"
                                    class="px-6 py-2.5 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                    ✔ Registrar Trâmite
                                </button>
                            </div>

                        </form>

                    </div>
                @endif

                {{-- =========================================================
                CARD — LINHA DO TEMPO DE TRÂMITES
                ========================================================= --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                    <div class="px-8 py-5 border-b">
                        <h3 class="text-base font-semibold text-slate-800">📜 Linha do Tempo</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Histórico completo de trâmites, do mais recente para o mais antigo
                        </p>
                    </div>

                    <div class="p-8">
                        @forelse ($processo->tramites as $tramite)
                            <div class="flex gap-4 pb-6 last:pb-0">

                                {{-- LINHA VERTICAL --}}
                                <div class="flex flex-col items-center">
                                    <span class="w-3 h-3 rounded-full mt-1"
                                        style="background-color: {{ $tramite->faseProcesso->cor ?? '#64748b' }}"></span>
                                    <span class="flex-1 w-px bg-slate-200"></span>
                                </div>

                                <div class="flex-1 pb-2">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-sm font-medium text-slate-800">
                                                {{ $tramite->data?->format('d/m/Y') ?? '—' }}
                                            </span>
                                            @if ($tramite->faseProcesso)
                                                <span class="px-2 py-0.5 rounded-full text-xs text-white"
                                                    style="background-color: {{ $tramite->faseProcesso->cor }}">
                                                    {{ $tramite->faseProcesso->nome }}
                                                </span>
                                            @endif
                                            @if ($tramite->setor_destino)
                                                <span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-600">
                                                    📍 {{ $tramite->setor_destino }}
                                                </span>
                                            @endif
                                            @if ($tramite->tipo_evento)
                                                <span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-600">
                                                    {{ $tramite->tipo_evento }}
                                                </span>
                                            @endif
                                        </div>

                                        @if (auth()->user()->perfil === 'admin')
                                            <button type="button"
                                                @click="modalDeleteTramite = true; urlDeleteTramite = '{{ route('processos.tramites.destroy', [$processo, $tramite]) }}'"
                                                class="text-xs text-red-500 hover:text-red-700">
                                                🗑
                                            </button>
                                        @endif
                                    </div>

                                    <p class="text-sm text-slate-600 mt-1">
                                        {{ $tramite->descricao }}
                                    </p>
                                </div>

                            </div>
                        @empty
                            <p class="text-sm text-slate-500 text-center py-6">
                                Nenhum trâmite registrado ainda.
                            </p>
                        @endforelse
                    </div>

                </div>

            </div>
        </div>

        {{-- =========================================================
        MODAL AJUDA
        ========================================================= --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">

                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Visualização de Processo</h3>
                    <p class="text-sm opacity-90">Entenda os dados exibidos</p>
                </div>

                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p><strong>Fase Atual:</strong> Sempre reflete a fase do trâmite mais recente registrado.</p>
                    <p><strong>Registrar Trâmite:</strong> A cada movimentação, escolha a fase correspondente e
                        descreva o que aconteceu — igual ao hábito de anotar na planilha, mas agora estruturado.</p>
                    <p><strong>Motivo da Pendência:</strong> Preencha ao registrar um trâmite que deixa o processo
                        parado (ex.: notificação aguardando devolutiva do técnico).</p>
                    <p><strong>Linha do Tempo:</strong> Histórico completo, do mais recente para o mais antigo.</p>

                    <div class="bg-slate-50 p-4 rounded text-xs">
                        💡 O Secretário usa exatamente essa tela para saber em qual fase está o processo, e por quê.
                    </div>
                </div>

                <div class="px-6 py-4 border-t flex justify-end">
                    <button @click="modalAjuda = false" class="px-5 py-2 bg-blue-600 text-white rounded-lg">
                        Entendi 👍
                    </button>
                </div>

            </div>
        </div>

        {{-- =========================================================
        MODAL EXCLUSÃO — PROCESSO
        ========================================================= --}}
        <div x-show="modalDelete" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md text-center">

                <h3 class="text-lg font-semibold text-red-600 mb-4">
                    Confirmar exclusão
                </h3>

                <p class="text-sm text-slate-600 mb-6">
                    Essa ação é irreversível. Todo o histórico de trâmites deste processo também será removido.
                </p>

                <div class="flex justify-center gap-3">
                    <button @click="modalDelete = false" class="px-4 py-2 border rounded">
                        Cancelar
                    </button>

                    <form action="{{ route('processos.destroy', $processo) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="px-5 py-2 bg-red-600 text-white rounded">
                            Excluir
                        </button>
                    </form>
                </div>

            </div>
        </div>

        {{-- =========================================================
        MODAL EXCLUSÃO — TRÂMITE
        ========================================================= --}}
        <div x-show="modalDeleteTramite" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md text-center">

                <h3 class="text-lg font-semibold text-red-600 mb-4">
                    Excluir trâmite
                </h3>

                <p class="text-sm text-slate-600 mb-6">
                    Remove este registro da linha do tempo. A fase atual do processo será recalculada a partir do
                    trâmite mais recente restante.
                </p>

                <div class="flex justify-center gap-3">
                    <button @click="modalDeleteTramite = false" class="px-4 py-2 border rounded">
                        Cancelar
                    </button>

                    <form :action="urlDeleteTramite" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="px-5 py-2 bg-red-600 text-white rounded">
                            Excluir
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>

@endsection
