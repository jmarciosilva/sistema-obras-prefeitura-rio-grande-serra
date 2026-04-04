@extends('layouts.app')

@section('title', 'Novo Status')
@section('subtitle', 'Cadastro de status de obra')

@section('content')

    <div x-data="statusForm()">

        {{-- BREADCRUMB --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('admin.status-obras.index') }}" class="hover:text-slate-900">Status de Obras</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-slate-900 font-medium">Novo Status</span>
            </div>
            <button type="button" @click="modalAjuda = true"
                class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                ❓ Ajuda
            </button>
        </div>

        <div class="mb-6">
            <a href="{{ route('admin.status-obras.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                   text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-100 transition">
                ← Voltar
            </a>
        </div>

        <div class="flex justify-center">
            <div class="w-full max-w-2xl">

                <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                    {{-- HEADER --}}
                    <div class="px-8 py-6 border-b border-slate-200">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl transition-all"
                                :style="'background-color: ' + cor + '33'">
                                🏷️
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">Novo Status de Obra</h2>
                                <p class="text-sm text-slate-600 mt-1">Defina nome, cor e ordem de exibição</p>
                            </div>
                        </div>
                    </div>

                    {{-- ERROS --}}
                    @if ($errors->any())
                        <div class="mx-8 mt-6 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg">
                            <strong>Erro ao salvar:</strong>
                            <ul class="mt-2 text-sm list-disc ml-6">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.status-obras.store') }}">
                        @csrf

                        <div class="px-8 py-6 space-y-6">

                            {{-- NOME --}}
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Nome do Status *</label>
                                <input type="text" name="nome" value="{{ old('nome') }}" required
                                    placeholder="Ex: Em Execução, Concluída, Paralisada..."
                                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                            </div>

                            {{-- COR --}}
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Cor do Badge *</label>

                                {{-- PALETA DE CORES RÁPIDAS --}}
                                <div class="flex flex-wrap gap-2 mb-3">
                                    @php
                                        $cores = [
                                            '#6c757d' => 'Cinza',
                                            '#0d6efd' => 'Azul',
                                            '#198754' => 'Verde',
                                            '#dc3545' => 'Vermelho',
                                            '#ffc107' => 'Amarelo',
                                            '#fd7e14' => 'Laranja',
                                            '#6f42c1' => 'Roxo',
                                            '#0dcaf0' => 'Ciano',
                                            '#20c997' => 'Teal',
                                            '#d63384' => 'Rosa',
                                        ];
                                    @endphp
                                    @foreach ($cores as $hex => $label)
                                        <button type="button" @click="cor = '{{ $hex }}'"
                                            :class="cor === '{{ $hex }}' ? 'ring-2 ring-offset-2 ring-slate-400' : ''"
                                            class="w-8 h-8 rounded-full transition-all hover:scale-110"
                                            style="background-color: {{ $hex }}" title="{{ $label }}">
                                        </button>
                                    @endforeach
                                </div>

                                {{-- COLOR PICKER + HEX MANUAL --}}
                                <div class="flex items-center gap-3">
                                    <input type="color" x-model="cor"
                                        class="w-12 h-10 rounded-lg border border-slate-300 cursor-pointer p-0.5">
                                    <input type="text" name="cor" x-model="cor" placeholder="#000000" maxlength="7"
                                        @input="validarHex($event)"
                                        class="flex-1 px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 font-mono text-sm">
                                </div>

                                {{-- PRÉVIA DO BADGE --}}
                                <div class="mt-3 flex items-center gap-3">
                                    <span class="text-xs text-slate-500">Prévia:</span>
                                    <span class="px-3 py-1 rounded-full text-sm font-medium text-white transition-all"
                                        :style="'background-color: ' + cor">
                                        {{ old('nome') ?: 'Nome do Status' }}
                                    </span>
                                </div>
                            </div>

                            {{-- ORDEM --}}
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Ordem de Exibição *</label>
                                <input type="number" name="ordem" value="{{ old('ordem', $proximaOrdem) }}"
                                    min="0" max="255" required
                                    class="w-32 px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-slate-400">
                                    Números menores aparecem primeiro nos selects e filtros. Sugerido: {{ $proximaOrdem }}.
                                </p>
                            </div>

                        </div>

                        {{-- FOOTER --}}
                        <div class="px-8 py-5 border-t bg-slate-50 rounded-b-xl flex justify-between">
                            <a href="{{ route('admin.status-obras.index') }}"
                                class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-700 bg-white border">
                                ← Cancelar
                            </a>
                            <button type="submit"
                                class="px-6 py-2.5 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                ✔ Salvar Status
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL AJUDA --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Novo Status</h3>
                </div>
                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p><strong>Nome:</strong> Label exibido no badge da obra (ex: "Em Execução").</p>
                    <p><strong>Cor:</strong> Clique na paleta ou no seletor de cor. A prévia atualiza em tempo real.</p>
                    <p><strong>Ordem:</strong> Define a posição nos selects. Status com ordem 1 aparece antes do de ordem 2.
                    </p>
                    <div class="bg-slate-50 p-3 rounded text-xs">
                        💡 Sugestão: use ordens espaçadas (10, 20, 30...) para facilitar a inserção de novos status no
                        futuro.
                    </div>
                </div>
                <div class="px-6 py-4 border-t flex justify-end">
                    <button @click="modalAjuda = false" class="px-5 py-2 bg-blue-600 text-white rounded-lg">
                        Entendi 👍
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
        function statusForm(corInicial = '#6c757d') {
            return {
                modalAjuda: false,
                cor: corInicial,

                validarHex(e) {
                    let v = e.target.value;
                    if (!v.startsWith('#')) v = '#' + v.replace('#', '');
                    e.target.value = v;
                    if (/^#[0-9A-Fa-f]{6}$/.test(v)) {
                        this.cor = v;
                    }
                },
            }
        }
    </script>

@endsection
