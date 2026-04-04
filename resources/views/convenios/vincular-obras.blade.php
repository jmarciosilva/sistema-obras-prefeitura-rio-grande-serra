@extends('layouts.app')

@section('title', 'Obras do Convênio')
@section('subtitle', 'Gerencie as obras financiadas por este convênio')

@section('content')

    <div x-data="vincularObras()">

        {{-- BREADCRUMB --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('convenios.index') }}" class="hover:text-slate-900">Convênios</a>
                <span>›</span>
                <a href="{{ route('convenios.show', $convenio) }}" class="hover:text-slate-900">
                    {{ $convenio->numero_convenio_ano ?? 'Convênio #' . $convenio->id }}
                </a>
                <span>›</span>
                <span class="text-slate-900 font-medium">Obras Vinculadas</span>
            </div>
            <button type="button" @click="modalAjuda = true"
                class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                ❓ Ajuda
            </button>
        </div>

        <div class="mb-6">
            <a href="{{ route('convenios.show', $convenio) }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                   text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-100 transition">
                ← Voltar ao Convênio
            </a>
        </div>

        {{-- CARD DO CONVÊNIO --}}
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center text-lg">🤝</div>
                <div>
                    <p class="font-semibold text-indigo-900">
                        {{ $convenio->numero_convenio_ano ?? 'Convênio sem número' }}
                    </p>
                    <p class="text-sm text-indigo-700">
                        {{ $convenio->categoria->nome ?? '—' }}
                        @if ($convenio->orgaoFinanciador)
                            · {{ $convenio->orgaoFinanciador->nome }}
                        @endif
                    </p>
                    <p class="text-xs text-indigo-600 mt-0.5">
                        {{ Str::limit($convenio->descricao, 100) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- FEEDBACK --}}
        @if (session('sucesso'))
            <div class="mb-6 bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm">
                ✅ {{ session('sucesso') }}
            </div>
        @endif
        @if ($errors->has('geral'))
            <div class="mb-6 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">
                ⚠️ {{ $errors->first('geral') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- ============================================================
            COLUNA ESQUERDA — obras já vinculadas
            ============================================================ --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-slate-800">🏗️ Obras vinculadas</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $vinculadas->count() }} obra(s) financiada(s) por este convênio
                        </p>
                    </div>
                </div>

                <div class="p-4 space-y-2">
                    @forelse($vinculadas as $obra)
                        <div
                            class="flex items-center justify-between p-3 rounded-lg border border-slate-200 hover:bg-slate-50">
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('obras.show', $obra) }}"
                                    class="text-sm font-medium text-blue-600 hover:underline truncate block">
                                    {{ $obra->descricao }}
                                </a>
                                <div class="flex items-center gap-2 mt-0.5">
                                    @if ($obra->status)
                                        <span class="px-1.5 py-0.5 rounded text-xs text-white"
                                            style="background-color: {{ $obra->status->cor ?? '#64748b' }}">
                                            {{ $obra->status->nome }}
                                        </span>
                                    @endif
                                    <span class="text-xs text-slate-400 truncate">
                                        {{ $obra->endereco ?? 'Local não informado' }}
                                    </span>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('convenios.desvincular-obra', [$convenio, $obra]) }}"
                                class="ml-3 shrink-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Desvincular esta obra do convênio?')"
                                    class="px-2 py-1.5 text-xs rounded-lg border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 transition">
                                    ✕ Desvincular
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="text-center py-10 text-slate-400">
                            <p class="text-3xl mb-2">🏗️</p>
                            <p class="text-sm">Nenhuma obra vinculada ainda.</p>
                            <p class="text-xs mt-1">Use o painel ao lado para adicionar obras.</p>
                        </div>
                    @endforelse
                </div>

            </div>

            {{-- ============================================================
            COLUNA DIREITA — obras disponíveis para vincular
            ============================================================ --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                <div class="px-6 py-4 border-b">
                    <h3 class="font-semibold text-slate-800">➕ Adicionar obras</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Selecione as obras a serem financiadas por este convênio</p>
                </div>

                <div class="p-4">

                    {{-- BUSCA INLINE --}}
                    <input type="text" x-model="busca" placeholder="Filtrar por descrição..."
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm mb-3 focus:ring-2 focus:ring-indigo-400">

                    <form method="POST" action="{{ route('convenios.vincular-obras', $convenio) }}">
                        @csrf

                        <div class="space-y-2 max-h-96 overflow-y-auto pr-1" id="lista-obras">
                            @forelse($disponiveis as $obra)
                                <label
                                    class="flex items-start gap-3 p-3 rounded-lg border border-slate-200 hover:bg-indigo-50
                                           hover:border-indigo-200 cursor-pointer transition"
                                    x-show="!busca || '{{ strtolower($obra->descricao) }}'.includes(busca.toLowerCase())"
                                    x-data>
                                    <input type="checkbox" name="obras[]" value="{{ $obra->id }}"
                                        class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-400">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-800">{{ $obra->descricao }}</p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            @if ($obra->status)
                                                <span class="px-1.5 py-0.5 rounded text-xs text-white"
                                                    style="background-color: {{ $obra->status->cor ?? '#64748b' }}">
                                                    {{ $obra->status->nome }}
                                                </span>
                                            @endif
                                            <span class="text-xs text-slate-400 truncate">
                                                {{ $obra->endereco ?? 'Local não informado' }}
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            @empty
                                <div class="text-center py-8 text-slate-400 text-sm">
                                    Todas as obras já estão vinculadas a este convênio.
                                </div>
                            @endforelse
                        </div>

                        @if ($disponiveis->count() > 0)
                            <div class="mt-4 pt-4 border-t flex justify-end">
                                <button type="submit"
                                    class="px-5 py-2.5 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                    ✔ Vincular selecionadas
                                </button>
                            </div>
                        @endif

                    </form>

                </div>
            </div>

        </div>

        {{-- MODAL AJUDA --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Obras do Convênio</h3>
                    <p class="text-sm opacity-90">Como vincular e desvincular obras</p>
                </div>
                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p><strong>Painel direito:</strong> Lista as obras ainda não vinculadas. Marque uma ou mais e clique em
                        "Vincular selecionadas".</p>
                    <p><strong>Painel esquerdo:</strong> Lista as obras já vinculadas. Use "Desvincular" para remover o
                        relacionamento sem excluir nenhum dado.</p>
                    <p><strong>Filtro:</strong> Digite parte do nome da obra para localizar rapidamente.</p>
                    <div class="bg-slate-50 p-4 rounded text-xs">
                        💡 Um convênio pode financiar várias obras simultaneamente.
                        A desvinculação é sempre reversível.
                    </div>
                </div>
                <div class="px-6 py-4 border-t flex justify-end">
                    <button @click="modalAjuda = false" class="px-5 py-2 bg-indigo-600 text-white rounded-lg">
                        Entendi 👍
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
        function vincularObras() {
            return {
                modalAjuda: false,
                busca: '',
            }
        }
    </script>

@endsection
