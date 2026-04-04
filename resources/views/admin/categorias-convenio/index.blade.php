@extends('layouts.app')

@section('title', 'Categorias de Convênio')
@section('subtitle', 'Gerencie as categorias que classificam os convênios municipais')

@section('content')

    <div class="space-y-6" x-data="catTable()">

        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">Categorias de Convênio</h1>
                <p class="text-sm text-slate-500">Classifique os convênios por tipo (ex: Pavimentação, Saúde, Saneamento).
                </p>
            </div>
            <div class="flex gap-2">
                <button type="button" @click="modalAjuda = true"
                    class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                    ❓ Ajuda
                </button>
                <a href="{{ route('admin.categorias-convenio.create') }}"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    ➕ Nova Categoria
                </a>
            </div>
        </div>

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

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs text-slate-500 font-semibold">Categoria</th>
                        <th class="px-4 py-3 text-left text-xs text-slate-500 font-semibold">Descrição</th>
                        <th class="px-4 py-3 text-center text-xs text-slate-500 font-semibold">Convênios</th>
                        <th class="px-4 py-3 text-right text-xs text-slate-500 font-semibold">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($categorias as $cat)
                        <tr class="hover:bg-slate-50">

                            <td class="px-4 py-3 font-medium text-slate-800">
                                {{ $cat->nome }}
                            </td>

                            <td class="px-4 py-3 text-slate-500 text-xs max-w-sm">
                                {{ $cat->descricao ? Str::limit($cat->descricao, 80) : '—' }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-semibold
                                    {{ $cat->convenios_count > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $cat->convenios_count }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.categorias-convenio.show', $cat) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                        👁 Ver
                                    </a>
                                    <a href="{{ route('admin.categorias-convenio.edit', $cat) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100">
                                        ✏️ Editar
                                    </a>
                                    <button type="button"
                                        @click="abrirModalDelete('{{ route('admin.categorias-convenio.destroy', $cat) }}', '{{ addslashes($cat->nome) }}', {{ $cat->convenios_count }})"
                                        class="px-3 py-1 text-xs rounded-lg border border-red-200 text-red-700 bg-red-50 hover:bg-red-100">
                                        🗑 Excluir
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-12 text-slate-400">
                                <p class="text-2xl mb-2">🗂️</p>
                                <p class="text-sm">Nenhuma categoria cadastrada.</p>
                                <a href="{{ route('admin.categorias-convenio.create') }}"
                                    class="inline-block mt-3 text-xs text-blue-600 hover:underline">
                                    ➕ Criar primeira categoria
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
                    <h3 class="text-lg font-semibold">📘 Ajuda — Categorias de Convênio</h3>
                    <p class="text-sm opacity-90">Como utilizar esta tela</p>
                </div>
                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p><strong>Categorias</strong> classificam os convênios por tipo de obra ou área (ex: Pavimentação,
                        Saneamento Básico, Saúde, Educação).</p>
                    <p>Ao cadastrar um convênio, a categoria é obrigatória e aparece como filtro na listagem de convênios.
                    </p>
                    <p>Categorias com convênios vinculados <strong>não podem ser excluídas</strong>.</p>
                    <div class="bg-slate-50 p-4 rounded text-xs">
                        💡 Exemplos: Pavimentação · Saneamento Básico · Saúde · Educação · Habitação · Meio Ambiente ·
                        Esporte e Lazer
                    </div>
                </div>
                <div class="px-6 py-4 border-t flex justify-end">
                    <button @click="modalAjuda = false" class="px-5 py-2 bg-blue-600 text-white rounded-lg">Entendi
                        👍</button>
                </div>
            </div>
        </div>

        {{-- MODAL EXCLUSÃO --}}
        <div x-show="modalDelete" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md text-center">
                <h3 class="text-lg font-semibold text-red-600 mb-4">Confirmar exclusão</h3>

                <template x-if="temConvenios">
                    <div
                        class="mb-4 bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-lg text-sm text-left">
                        ⚠️ A categoria <strong x-text="nomeCategoria"></strong> possui convênios vinculados e
                        <strong>não pode ser excluída</strong>.
                    </div>
                </template>

                <template x-if="!temConvenios">
                    <p class="text-sm text-slate-600 mb-6">
                        A categoria <strong x-text="nomeCategoria"></strong> será removida permanentemente.
                    </p>
                </template>

                <div class="flex justify-center gap-3">
                    <button @click="modalDelete = false" class="px-4 py-2 border rounded">Cancelar</button>
                    <template x-if="!temConvenios">
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
        function catTable() {
            return {
                modalDelete: false,
                modalAjuda: false,
                url: '',
                nomeCategoria: '',
                temConvenios: false,
                abrirModalDelete(url, nome, qtd) {
                    this.url = url;
                    this.nomeCategoria = nome;
                    this.temConvenios = qtd > 0;
                    this.modalDelete = true;
                }
            }
        }
    </script>

@endsection
