@extends('layouts.app')

@section('title', 'Visualizar Status')
@section('subtitle', 'Detalhes do status de obra')

@section('content')

    <div x-data="{ modalAjuda: false }">

        {{-- BREADCRUMB --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('admin.status-obras.index') }}" class="hover:text-slate-900">Status de Obras</a>
                <span>›</span>
                <span class="text-slate-900 font-medium">{{ $status->nome }}</span>
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

        {{-- FEEDBACK --}}
        @if (session('sucesso'))
            <div class="mb-6 bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm">
                ✅ {{ session('sucesso') }}
            </div>
        @endif

        <div class="flex justify-center">
            <div class="w-full max-w-2xl space-y-6">

                {{-- CARD PRINCIPAL --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                    <div class="px-8 py-6 border-b border-slate-200">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl"
                                style="background-color: {{ $status->cor }}33">
                                🏷️
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">{{ $status->nome }}</h2>
                                <div class="mt-2">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium text-white"
                                        style="background-color: {{ $status->cor }}">
                                        {{ $status->nome }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-8 py-6 space-y-6">

                        {{-- COR E ORDEM --}}
                        <div class="grid grid-cols-2 gap-6 text-sm">

                            <div>
                                <p class="text-slate-500 mb-2">Cor do Badge</p>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg border border-slate-200"
                                        style="background-color: {{ $status->cor }}"></div>
                                    <code class="text-sm font-mono text-slate-700">{{ $status->cor }}</code>
                                </div>
                            </div>

                            <div>
                                <p class="text-slate-500 mb-2">Ordem de Exibição</p>
                                <span
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 text-slate-700 font-bold text-lg">
                                    {{ $status->ordem }}
                                </span>
                            </div>

                        </div>

                        {{-- OBRAS --}}
                        <div>
                            <p class="text-slate-500 text-sm mb-2">Obras com este status</p>
                            <div class="flex items-center gap-3">
                                <span
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-full text-sm font-bold
                                    {{ $status->obras_count > 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $status->obras_count }}
                                </span>
                                @if ($status->obras_count > 0)
                                    <span class="text-sm text-slate-600">
                                        obra(s) utilizam este status atualmente
                                    </span>
                                @else
                                    <span class="text-sm text-slate-400 italic">
                                        Nenhuma obra com este status
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- METADADOS --}}
                        <div>
                            <p class="text-slate-500 text-sm mb-3">Informações do Sistema</p>
                            <div class="grid grid-cols-2 gap-6 text-sm">
                                <div>
                                    <p class="text-slate-500">Criado em</p>
                                    <p class="font-medium text-slate-800">
                                        {{ $status->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Última atualização</p>
                                    <p class="font-medium text-slate-800">
                                        {{ $status->updated_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="px-8 py-5 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-end">
                        <a href="{{ route('admin.status-obras.edit', $status) }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium
                               text-white bg-blue-600 hover:bg-blue-700 transition">
                            ✏️ Editar Status
                        </a>
                    </div>

                </div>

            </div>
        </div>

        {{-- MODAL AJUDA --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Visualização de Status</h3>
                </div>
                <div class="p-6 space-y-3 text-sm text-slate-700">
                    <p><strong>Cor:</strong> Exibida nos badges de toda a listagem e tela de detalhe de obras.</p>
                    <p><strong>Ordem:</strong> Posição nos selects e filtros do sistema.</p>
                    <p><strong>Obras:</strong> Quantidade de obras atualmente com este status. Status com obras não podem
                        ser excluídos.</p>
                </div>
                <div class="px-6 py-4 border-t flex justify-end">
                    <button @click="modalAjuda = false" class="px-5 py-2 bg-blue-600 text-white rounded-lg">
                        Entendi 👍
                    </button>
                </div>
            </div>
        </div>

    </div>

@endsection
