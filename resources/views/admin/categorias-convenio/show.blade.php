@extends('layouts.app')

@section('title', 'Visualizar Categoria')
@section('subtitle', 'Detalhes da categoria de convênio')

@section('content')

    <div x-data="{ modalAjuda: false }">

        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('admin.categorias-convenio.index') }}" class="hover:text-slate-900">Categorias de
                    Convênio</a>
                <span>›</span>
                <span class="text-slate-900 font-medium">{{ $categoria->nome }}</span>
            </div>
            <button type="button" @click="modalAjuda = true"
                class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                ❓ Ajuda
            </button>
        </div>

        <div class="mb-6">
            <a href="{{ route('admin.categorias-convenio.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                   text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-100 transition">
                ← Voltar
            </a>
        </div>

        @if (session('sucesso'))
            <div class="mb-6 bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm">
                ✅ {{ session('sucesso') }}
            </div>
        @endif

        <div class="flex justify-center">
            <div class="w-full max-w-2xl">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                    <div class="px-8 py-6 border-b">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center text-2xl">🗂️
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">{{ $categoria->nome }}</h2>
                                <p class="text-sm text-indigo-600 mt-0.5">Categoria de Convênio</p>
                            </div>
                        </div>
                    </div>

                    <div class="px-8 py-6 space-y-6">

                        <div>
                            <p class="text-xs text-slate-500 uppercase font-semibold mb-2">Descrição</p>
                            <p class="text-sm text-slate-700 leading-relaxed">
                                {{ $categoria->descricao ?? 'Nenhuma descrição cadastrada.' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-500 uppercase font-semibold mb-2">Convênios nesta categoria</p>
                            <div class="flex items-center gap-3">
                                <span
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-full text-sm font-bold
                                    {{ $categoria->convenios_count > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $categoria->convenios_count }}
                                </span>
                                <span
                                    class="text-sm {{ $categoria->convenios_count > 0 ? 'text-slate-600' : 'text-slate-400 italic' }}">
                                    {{ $categoria->convenios_count > 0
                                        ? $categoria->convenios_count . ' convênio(s) utilizam esta categoria'
                                        : 'Nenhum convênio vinculado' }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs text-slate-500 uppercase font-semibold mb-3">Informações do Sistema</p>
                            <div class="grid grid-cols-2 gap-6 text-sm">
                                <div>
                                    <p class="text-slate-500">Criado em</p>
                                    <p class="font-medium text-slate-800">{{ $categoria->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Última atualização</p>
                                    <p class="font-medium text-slate-800">{{ $categoria->updated_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="px-8 py-5 border-t bg-slate-50 rounded-b-xl flex justify-end">
                        <a href="{{ route('admin.categorias-convenio.edit', $categoria) }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium
                               text-white bg-blue-600 hover:bg-blue-700 transition">
                            ✏️ Editar Categoria
                        </a>
                    </div>

                </div>
            </div>
        </div>

        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda</h3>
                </div>
                <div class="p-6 space-y-3 text-sm text-slate-700">
                    <p><strong>Convênios:</strong> Quantidade de convênios que usam esta categoria. Categorias com convênios
                        não podem ser excluídas.</p>
                </div>
                <div class="px-6 py-4 border-t flex justify-end">
                    <button @click="modalAjuda = false" class="px-5 py-2 bg-blue-600 text-white rounded-lg">Entendi
                        👍</button>
                </div>
            </div>
        </div>

    </div>

@endsection
