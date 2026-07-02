@extends('layouts.app')

@section('title', 'Responsáveis Técnicos')
@section('subtitle', 'Engenheiros e arquitetos vinculados a processos administrativos')

@section('content')

    <div class="space-y-6" x-data="{ modalAjuda: false, modalDelete: false, url: '', nome: '', temProcessos: false }">

        {{-- HEADER --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">Responsáveis Técnicos</h1>
                <p class="text-sm text-slate-500">
                    Gerencie os engenheiros/arquitetos que podem ser vinculados a processos administrativos.
                </p>
            </div>
            <div class="flex gap-2">
                <button type="button" @click="modalAjuda = true"
                    class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                    ❓ Ajuda
                </button>
                <a href="{{ route('admin.responsaveis-tecnicos.create') }}"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    ➕ Novo Responsável
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

        {{-- BUSCA --}}
        <form method="GET" class="bg-white rounded-xl shadow p-4 flex gap-3">
            <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Nome ou registro (CREA/CAU)..."
                class="flex-1 border rounded-lg px-4 py-2 text-sm">
            <button class="bg-blue-600 text-white px-5 rounded-lg text-sm">Buscar</button>
        </form>

        {{-- TABELA --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">

            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs text-slate-500 font-semibold">Nome</th>
                        <th class="px-4 py-3 text-left text-xs text-slate-500 font-semibold">Registro</th>
                        <th class="px-4 py-3 text-left text-xs text-slate-500 font-semibold">Telefone</th>
                        <th class="px-4 py-3 text-center text-xs text-slate-500 font-semibold">Processos</th>
                        <th class="px-4 py-3 text-right text-xs text-slate-500 font-semibold">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($responsaveis as $resp)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $resp->nome }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $resp->registro ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $resp->telefone ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-semibold
                                    {{ $resp->processos_count > 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $resp->processos_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.responsaveis-tecnicos.edit', $resp) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100">
                                        ✏️ Editar
                                    </a>
                                    <button type="button"
                                        @click="url = '{{ route('admin.responsaveis-tecnicos.destroy', $resp) }}'; nome = '{{ $resp->nome }}'; temProcessos = {{ $resp->processos_count }} > 0; modalDelete = true"
                                        class="px-3 py-1 text-xs rounded-lg border border-red-200 text-red-700 bg-red-50 hover:bg-red-100">
                                        🗑 Excluir
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-slate-400">
                                <p class="text-2xl mb-2">👷</p>
                                <p class="text-sm">Nenhum responsável técnico cadastrado.</p>
                                <a href="{{ route('admin.responsaveis-tecnicos.create') }}"
                                    class="inline-block mt-3 text-xs text-blue-600 hover:underline">
                                    ➕ Cadastrar o primeiro
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="p-4 border-t">
                {{ $responsaveis->links() }}
            </div>

        </div>

        {{-- MODAL AJUDA --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Responsáveis Técnicos</h3>
                    <p class="text-sm opacity-90">Como utilizar esta tela</p>
                </div>
                <div class="p-6 space-y-3 text-sm text-slate-700">
                    <p>Cadastre aqui os engenheiros/arquitetos (CREA/CAU) que podem ser vinculados como responsáveis
                        técnicos de processos administrativos.</p>
                    <p>Um responsável técnico só pode ser excluído se não houver processos vinculados a ele.</p>
                    <div class="mt-3 p-3 bg-slate-50 rounded text-xs">
                        💡 Também é possível cadastrar um novo responsável diretamente pelo formulário de criação de
                        processo, clicando em "➕ Novo Responsável".
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

                <template x-if="temProcessos">
                    <div
                        class="mb-4 bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-lg text-sm text-left">
                        ⚠️ <strong x-text="nome"></strong> possui processos vinculados e
                        <strong>não pode ser excluído</strong>.
                    </div>
                </template>

                <template x-if="!temProcessos">
                    <p class="text-sm text-slate-600 mb-6">
                        <strong x-text="nome"></strong> será removido permanentemente.
                    </p>
                </template>

                <div class="flex justify-center gap-3">
                    <button @click="modalDelete = false" class="px-4 py-2 border rounded">Cancelar</button>
                    <template x-if="!temProcessos">
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

@endsection
