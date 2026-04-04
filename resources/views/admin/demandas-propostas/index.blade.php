@extends('layouts.app')
@section('title', 'Demandas e Propostas')
@section('subtitle', 'Solicitações que originam obras municipais')
@section('content')
    <div class="space-y-6" x-data="demandaTable()">

        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">Demandas e Propostas</h1>
                <p class="text-sm text-slate-500">Registre as solicitações que originam obras (parlamentares, secretarias,
                    etc.).</p>
            </div>
            <div class="flex gap-2">
                <button type="button" @click="modalAjuda=true"
                    class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">❓
                    Ajuda</button>
                <a href="{{ route('admin.demandas-propostas.create') }}"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">➕ Nova Demanda</a>
            </div>
        </div>

        {{-- FILTROS --}}
        <form method="GET" class="bg-white rounded-xl shadow p-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Número ou descrição..."
                    class="w-full border rounded-lg px-4 py-2 text-sm">
            </div>
            <select name="situacao" class="border rounded-lg px-3 py-2 text-sm">
                <option value="">Todas as situações</option>
                @foreach ($situacoes as $val => $label)
                    <option value="{{ $val }}" {{ request('situacao') === $val ? 'selected' : '' }}>{{ $label }}
                    </option>
                @endforeach
            </select>
            <select name="origem" class="border rounded-lg px-3 py-2 text-sm">
                <option value="">Todas as origens</option>
                @foreach ($origens as $val => $label)
                    <option value="{{ $val }}" {{ request('origem') === $val ? 'selected' : '' }}>{{ $label }}
                    </option>
                @endforeach
            </select>
            <button class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm">Buscar</button>
            @if (request()->hasAny(['busca', 'situacao', 'origem']))
                <a href="{{ route('admin.demandas-propostas.index') }}"
                    class="px-4 py-2 text-sm text-slate-500 border rounded-lg hover:bg-slate-50">✕ Limpar</a>
            @endif
        </form>

        @if (session('sucesso'))
            <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm">✅
                {{ session('sucesso') }}</div>
        @endif
        @if ($errors->has('geral'))
            <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">⚠️
                {{ $errors->first('geral') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs text-slate-500 font-semibold">Número / Descrição</th>
                        <th class="px-4 py-3 text-left text-xs text-slate-500 font-semibold">Origem</th>
                        <th class="px-4 py-3 text-left text-xs text-slate-500 font-semibold">Solicitante</th>
                        <th class="px-4 py-3 text-center text-xs text-slate-500 font-semibold">Situação</th>
                        <th class="px-4 py-3 text-center text-xs text-slate-500 font-semibold">Obras</th>
                        <th class="px-4 py-3 text-right text-xs text-slate-500 font-semibold">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($demandas as $d)
                        @php
                            $sitCor =
                                [
                                    'aprovada' => 'bg-green-100 text-green-700',
                                    'rejeitada' => 'bg-red-100 text-red-700',
                                    'em_andamento' => 'bg-blue-100 text-blue-700',
                                    'pendente' => 'bg-yellow-100 text-yellow-700',
                                ][$d->situacao] ?? 'bg-slate-100 text-slate-600';
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-800 font-mono text-xs">{{ $d->numero_demanda }}</p>
                                <p class="text-slate-500 text-xs mt-0.5">{{ Str::limit($d->descricao, 60) }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-600 text-xs">{{ $d->origem_label }}</td>
                            <td class="px-4 py-3 text-slate-600 text-xs">{{ $d->solicitante ?? '—' }}</td>
                            <td class="px-4 py-3 text-center"><span
                                    class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sitCor }}">{{ $d->situacao_label }}</span>
                            </td>
                            <td class="px-4 py-3 text-center"><span
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-semibold {{ $d->obras_count > 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-400' }}">{{ $d->obras_count }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.demandas-propostas.show', $d) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100">👁
                                        Ver</a>
                                    <a href="{{ route('admin.demandas-propostas.edit', $d) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100">✏️
                                        Editar</a>
                                    <button type="button"
                                        @click="abrirModalDelete('{{ route('admin.demandas-propostas.destroy', $d) }}','{{ addslashes($d->numero_demanda) }}',{{ $d->obras_count }})"
                                        class="px-3 py-1 text-xs rounded-lg border border-red-200 text-red-700 bg-red-50 hover:bg-red-100">🗑
                                        Excluir</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                <p class="text-2xl mb-2">📋</p>
                                <p class="text-sm">Nenhuma demanda cadastrada.</p><a
                                    href="{{ route('admin.demandas-propostas.create') }}"
                                    class="inline-block mt-3 text-xs text-blue-600 hover:underline">➕ Criar primeira
                                    demanda</a>
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
                    <h3 class="text-lg font-semibold">📘 Ajuda — Demandas e Propostas</h3>
                </div>
                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p><strong>Demandas</strong> registram a origem de cada obra — um vereador que solicitou, uma secretaria
                        que identificou necessidade, um programa federal, etc.</p>
                    <p><strong>Situação:</strong> Pendente → Em Andamento → Aprovada (vira obra) ou Rejeitada.</p>
                    <p><strong>Obras:</strong> Quantidade de obras originadas por esta demanda. Demandas com obras não podem
                        ser excluídas.</p>
                    <div class="bg-slate-50 p-4 rounded text-xs">💡 O número de demanda deve ser único (ex: DEM-2024-001) e
                        serve como referência documental.</div>
                </div>
                <div class="px-6 py-4 border-t flex justify-end"><button @click="modalAjuda=false"
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg">Entendi 👍</button></div>
            </div>
        </div>

        {{-- MODAL DELETE --}}
        <div x-show="modalDelete" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md text-center">
                <h3 class="text-lg font-semibold text-red-600 mb-4">Confirmar exclusão</h3>
                <template x-if="temObras">
                    <div
                        class="mb-4 bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-lg text-sm text-left">
                        ⚠️ A demanda <strong x-text="nomeDemanda"></strong> possui obras vinculadas e <strong>não pode ser
                            excluída</strong>.</div>
                </template>
                <template x-if="!temObras">
                    <p class="text-sm text-slate-600 mb-6">A demanda <strong x-text="nomeDemanda"></strong> será removida
                        permanentemente.</p>
                </template>
                <div class="flex justify-center gap-3">
                    <button @click="modalDelete=false" class="px-4 py-2 border rounded">Cancelar</button>
                    <template x-if="!temObras">
                        <form :action="url" method="POST">@csrf @method('DELETE')<button
                                class="px-5 py-2 bg-red-600 text-white rounded">Excluir</button></form>
                    </template>
                </div>
            </div>
        </div>
    </div>
    <script>
        function demandaTable() {
            return {
                modalDelete: false,
                modalAjuda: false,
                url: '',
                nomeDemanda: '',
                temObras: false,
                abrirModalDelete(url, nome, qtd) {
                    this.url = url;
                    this.nomeDemanda = nome;
                    this.temObras = qtd > 0;
                    this.modalDelete = true;
                }
            }
        }
    </script>
@endsection
