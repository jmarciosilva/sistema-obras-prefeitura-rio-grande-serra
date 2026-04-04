@extends('layouts.app')

@section('title', 'Status de Obras')
@section('subtitle', 'Gerencie os status disponíveis para as obras municipais')

@section('content')

    <div class="space-y-6" x-data="statusTable()">

        {{-- HEADER --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">Status de Obras</h1>
                <p class="text-sm text-slate-500">
                    Defina os status, cores e ordem de exibição das obras.
                </p>
            </div>
            <div class="flex gap-2">
                <button type="button" @click="modalAjuda = true"
                    class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                    ❓ Ajuda
                </button>
                <a href="{{ route('admin.status-obras.create') }}"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    ➕ Novo Status
                </a>
            </div>
        </div>

        {{-- FEEDBACK --}}
        @if (session('sucesso'))
            <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm">
                ✅ {{ session('sucesso') }}
            </div>
        @endif

        @if ($errors->has('geral'))
            <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">
                ⚠️ {{ $errors->first('geral') }}
            </div>
        @endif

        {{-- TABELA --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">

            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs text-slate-500 font-semibold w-12">Ordem</th>
                        <th class="px-4 py-3 text-left text-xs text-slate-500 font-semibold">Status</th>
                        <th class="px-4 py-3 text-left text-xs text-slate-500 font-semibold">Cor</th>
                        <th class="px-4 py-3 text-center text-xs text-slate-500 font-semibold">Obras</th>
                        <th class="px-4 py-3 text-right text-xs text-slate-500 font-semibold">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($statuses as $s)
                        <tr class="hover:bg-slate-50">

                            {{-- ORDEM --}}
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 text-slate-600 text-xs font-bold">
                                    {{ $s->ordem }}
                                </span>
                            </td>

                            {{-- NOME COM BADGE --}}
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium text-white"
                                    style="background-color: {{ $s->cor }}">
                                    {{ $s->nome }}
                                </span>
                            </td>

                            {{-- COR --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded border border-slate-200 shrink-0"
                                        style="background-color: {{ $s->cor }}"></div>
                                    <code class="text-xs text-slate-500 font-mono">{{ $s->cor }}</code>
                                </div>
                            </td>

                            {{-- OBRAS --}}
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-semibold
                                    {{ $s->obras_count > 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $s->obras_count }}
                                </span>
                            </td>

                            {{-- AÇÕES --}}
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.status-obras.show', $s) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                        👁 Ver
                                    </a>
                                    <a href="{{ route('admin.status-obras.edit', $s) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100">
                                        ✏️ Editar
                                    </a>
                                    <button type="button"
                                        @click="abrirModalDelete('{{ route('admin.status-obras.destroy', $s) }}', '{{ $s->nome }}', {{ $s->obras_count }})"
                                        class="px-3 py-1 text-xs rounded-lg border border-red-200 text-red-700 bg-red-50 hover:bg-red-100">
                                        🗑 Excluir
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-slate-400">
                                <p class="text-2xl mb-2">🏷️</p>
                                <p class="text-sm">Nenhum status cadastrado.</p>
                                <a href="{{ route('admin.status-obras.create') }}"
                                    class="inline-block mt-3 text-xs text-blue-600 hover:underline">
                                    ➕ Criar primeiro status
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

        {{-- MODAL AJUDA --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Status de Obras</h3>
                    <p class="text-sm opacity-90">Como utilizar esta tela</p>
                </div>
                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p><strong>Status</strong> definem o estado atual de cada obra (ex: Em Planejamento, Em Execução,
                        Concluída).</p>
                    <p><strong>Cor:</strong> Usada nos badges visuais em toda a listagem de obras. Escolha uma cor que
                        represente bem o estado.</p>
                    <p><strong>Ordem:</strong> Define a sequência de exibição nos selects e filtros. Números menores
                        aparecem primeiro.</p>
                    <p><strong>Obras:</strong> O número indica quantas obras estão com aquele status. Status com obras
                        vinculadas não podem ser excluídos.</p>
                    <div class="bg-slate-50 p-4 rounded text-xs">
                        💡 Sugestão de cores: Em Planejamento → <code>#6c757d</code> · Em Execução → <code>#007bff</code> ·
                        Concluída → <code>#28a745</code> · Paralisada → <code>#dc3545</code>
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

                <template x-if="temObras">
                    <div
                        class="mb-4 bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-lg text-sm text-left">
                        ⚠️ O status <strong x-text="nomeStatus"></strong> possui obras vinculadas e
                        <strong>não pode ser excluído</strong>. Reatribua as obras antes de prosseguir.
                    </div>
                </template>

                <template x-if="!temObras">
                    <p class="text-sm text-slate-600 mb-6">
                        O status <strong x-text="nomeStatus"></strong> será removido permanentemente.
                    </p>
                </template>

                <div class="flex justify-center gap-3">
                    <button @click="modalDelete = false" class="px-4 py-2 border rounded">Cancelar</button>
                    <template x-if="!temObras">
                        <form :action="url" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="px-5 py-2 bg-red-600 text-white rounded">Excluir</button>
                        </form>
                    </template>
                </div>
            </div>
        </div>

    </div>

    <script>
        function statusTable() {
            return {
                modalDelete: false,
                modalAjuda: false,
                url: '',
                nomeStatus: '',
                temObras: false,

                abrirModalDelete(url, nome, qtdObras) {
                    this.url = url;
                    this.nomeStatus = nome;
                    this.temObras = qtdObras > 0;
                    this.modalDelete = true;
                }
            }
        }
    </script>

@endsection
