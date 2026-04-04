@extends('layouts.app')

@section('title', 'Visualizar Convênio')
@section('subtitle', 'Detalhes do convênio')

@section('content')

    <div x-data="{ modalAjuda: false, modalDelete: false }">

        {{-- BREADCRUMB --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('convenios.index') }}" class="hover:text-slate-900">Convênios</a>
                <span>›</span>
                <span class="text-slate-900 font-medium">
                    {{ $convenio->numero_convenio_ano ?? 'Convênio #' . $convenio->id }}
                </span>
            </div>
            <button type="button" @click="modalAjuda = true"
                class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                ❓ Ajuda
            </button>
        </div>

        <div class="mb-6">
            <a href="{{ route('convenios.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                   text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-100 transition">
                ← Voltar
            </a>
        </div>

        {{-- ALERTA VIGÊNCIA --}}
        @php
            $vencido = $convenio->vigencia?->isPast();
            $breve   = $convenio->vigencia && !$vencido && $convenio->vigencia->diffInDays(now()) <= 30;
        @endphp
        @if ($vencido)
            <div class="mb-6 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">
                ⚠️ <strong>Vigência vencida.</strong> Este convênio expirou em {{ $convenio->vigencia->format('d/m/Y') }}.
            </div>
        @elseif ($breve)
            <div class="mb-6 bg-yellow-50 border border-yellow-300 text-yellow-700 px-4 py-3 rounded-lg text-sm">
                ⚠️ <strong>Atenção:</strong> Este convênio vence em {{ now()->diffInDays($convenio->vigencia) }} dias
                ({{ $convenio->vigencia->format('d/m/Y') }}).
            </div>
        @endif

        <div class="flex justify-center">
            <div class="w-full max-w-4xl space-y-6">

                {{-- CARD PRINCIPAL --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                    <div class="px-8 py-6 border-b border-slate-200">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center text-2xl">
                                🤝
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">
                                    {{ $convenio->numero_convenio_ano ?? 'Convênio sem número' }}
                                </h2>
                                <p class="text-sm text-slate-500 mt-1">
                                    {{ $convenio->categoria->nome ?? '—' }}
                                    @if ($convenio->orgaoFinanciador)
                                        · {{ $convenio->orgaoFinanciador->nome }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="px-8 py-6 space-y-6">

                        {{-- DESCRIÇÃO --}}
                        <div>
                            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-2">Objeto / Descrição</h3>
                            <p class="text-sm text-slate-700 leading-relaxed">
                                {{ $convenio->descricao ?? '—' }}
                            </p>
                        </div>

                        {{-- IDENTIFICAÇÃO --}}
                        <div>
                            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Identificação</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                                <div>
                                    <p class="text-slate-500">Processo Pref. / Concedente</p>
                                    <p class="font-medium text-slate-800">{{ $convenio->processo_pref_concedente ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Nº Convênio / Ano</p>
                                    <p class="font-medium text-slate-800">{{ $convenio->numero_convenio_ano ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">PRESCON</p>
                                    <p class="font-medium text-slate-800">{{ $convenio->cadastro_prescon_num ?? '—' }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- VALOR --}}
                        <div>
                            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Valor</h3>
                            <div class="bg-slate-50 rounded-lg p-4 inline-block">
                                <p class="text-xs text-slate-500 mb-1">Repasse / Contrapartida</p>
                                <p class="text-2xl font-bold text-slate-800">
                                    R$ {{ $convenio->valor_repasse_contrapartida
                                        ? number_format($convenio->valor_repasse_contrapartida, 2, ',', '.')
                                        : '—' }}
                                </p>
                            </div>
                        </div>

                        {{-- DATAS --}}
                        <div>
                            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Datas</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                                <div>
                                    <p class="text-slate-500">Assinatura</p>
                                    <p class="font-medium text-slate-800">
                                        {{ $convenio->assinatura?->format('d/m/Y') ?? '—' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Vigência</p>
                                    <p class="font-medium {{ $vencido ? 'text-red-600' : ($breve ? 'text-yellow-600' : 'text-slate-800') }}">
                                        {{ $convenio->vigencia?->format('d/m/Y') ?? '—' }}
                                        @if ($vencido) <span class="text-xs">(vencido)</span>
                                        @elseif ($breve) <span class="text-xs">(vence em breve)</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- METADADOS --}}
                        <div>
                            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Informações do Sistema</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                                <div>
                                    <p class="text-slate-500">Cadastrado em</p>
                                    <p class="font-medium text-slate-800">{{ $convenio->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Última atualização</p>
                                    <p class="font-medium text-slate-800">{{ $convenio->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="px-8 py-5 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-between items-center">
                        <button type="button" @click="modalDelete = true"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium
                               text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 transition">
                            🗑 Excluir Convênio
                        </button>
                        <a href="{{ route('convenios.edit', $convenio) }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium
                               text-white bg-blue-600 hover:bg-blue-700 transition">
                            ✏️ Editar Convênio
                        </a>
                    </div>

                </div>

                {{-- OBRAS VINCULADAS --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                    <div class="px-8 py-5 border-b flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-800">🏗️ Obras Vinculadas</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $convenio->obras->count() }} obra(s) financiada(s) por este convênio
                            </p>
                        </div>
                        @if (auth()->user()->perfil !== 'operador')
                            <a href="{{ route('convenios.obras', $convenio) }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg
                                       bg-indigo-600 text-white hover:bg-indigo-700 transition">
                                🔗 Gerenciar obras
                            </a>
                        @endif
                    </div>

                    <div class="p-6">
                        @forelse($convenio->obras as $obra)
                            <div class="flex items-center justify-between py-3 border-b last:border-0 text-sm">
                                <div>
                                    <a href="{{ route('obras.show', $obra) }}"
                                        class="font-medium text-blue-600 hover:underline">
                                        {{ $obra->descricao }}
                                    </a>
                                    <p class="text-xs text-slate-500">{{ $obra->endereco ?? 'Local não informado' }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if ($obra->status)
                                        <span class="px-2 py-0.5 rounded text-xs text-white"
                                            style="background-color: {{ $obra->status->cor ?? '#64748b' }}">
                                            {{ $obra->status->nome }}
                                        </span>
                                    @endif
                                    <a href="{{ route('obras.show', $obra) }}"
                                        class="px-2 py-1 text-xs rounded border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                        👁 Ver
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-slate-400">
                                <p class="text-2xl mb-2">🏗️</p>
                                <p class="text-sm">Nenhuma obra vinculada a este convênio.</p>
                                @if (auth()->user()->perfil !== 'operador')
                                    <a href="{{ route('convenios.obras', $convenio) }}"
                                        class="inline-block mt-3 text-xs text-indigo-600 hover:underline">
                                        🔗 Clique para vincular obras
                                    </a>
                                @endif
                            </div>
                        @endforelse
                    </div>

                </div>

            </div>
        </div>

        {{-- MODAL AJUDA --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Visualização de Convênio</h3>
                    <p class="text-sm opacity-90">Entenda os dados exibidos</p>
                </div>
                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p><strong>Descrição:</strong> Objeto do convênio conforme instrumento assinado.</p>
                    <p><strong>Valor:</strong> Total do repasse incluindo contrapartida municipal.</p>
                    <p><strong>Obras vinculadas:</strong> Obras que utilizam este convênio como fonte de recursos.</p>
                    <div class="bg-slate-50 p-4 rounded text-xs">
                        💡 Para vincular uma obra, acesse a tela da obra e use a aba "Convênios".
                    </div>
                </div>
                <div class="px-6 py-4 border-t flex justify-end">
                    <button @click="modalAjuda = false" class="px-5 py-2 bg-blue-600 text-white rounded-lg">
                        Entendi 👍
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL EXCLUSÃO --}}
        <div x-show="modalDelete" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md text-center">
                <h3 class="text-lg font-semibold text-red-600 mb-4">Confirmar exclusão</h3>
                <p class="text-sm text-slate-600 mb-6">Essa ação é irreversível. O convênio será desvinculado de todas as obras.</p>
                <div class="flex justify-center gap-3">
                    <button @click="modalDelete = false" class="px-4 py-2 border rounded">Cancelar</button>
                    <form action="{{ route('convenios.destroy', $convenio) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="px-5 py-2 bg-red-600 text-white rounded">Excluir</button>
                    </form>
                </div>
            </div>
        </div>

    </div>

@endsection