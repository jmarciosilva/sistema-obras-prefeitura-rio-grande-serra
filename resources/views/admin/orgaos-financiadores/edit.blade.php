@extends('layouts.app')
@section('title', 'Editar Órgão Financiador')
@section('content')
    <div x-data="{ modalAjuda: false }">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('admin.orgaos-financiadores.index') }}" class="hover:text-slate-900">Órgãos
                    Financiadores</a>
                <span>›</span><span class="text-slate-900 font-medium">Editar: {{ $orgao->nome }}</span>
            </div>
            <button type="button" @click="modalAjuda=true"
                class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">❓
                Ajuda</button>
        </div>
        <div class="mb-6"><a href="{{ route('admin.orgaos-financiadores.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-100 transition">←
                Voltar</a></div>

        <div class="flex justify-center">
            <div class="w-full max-w-2xl">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="px-8 py-6 border-b">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-xl">✏️</div>
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">Editar Órgão</h2>
                                <p class="text-sm text-slate-600 mt-1">{{ $orgao->nome_com_sigla }}</p>
                            </div>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="mx-8 mt-6 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg">
                            <strong>Erro:</strong>
                            <ul class="mt-2 text-sm list-disc ml-6">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.orgaos-financiadores.update', $orgao) }}">
                        @csrf @method('PUT')
                        <div class="px-8 py-6 space-y-6">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Nome do Órgão *</label>
                                <input type="text" name="nome" value="{{ old('nome', $orgao->nome) }}" required
                                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Sigla</label>
                                    <input type="text" name="sigla" value="{{ old('sigla', $orgao->sigla) }}"
                                        placeholder="Ex: CEF"
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 font-mono">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Esfera *</label>
                                    <select name="esfera" required
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                        @foreach ($esferas as $val => $label)
                                            <option value="{{ $val }}"
                                                {{ old('esfera', $orgao->esfera) === $val ? 'selected' : '' }}>{{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="px-8 py-5 border-t bg-slate-50 rounded-b-xl flex justify-between">
                            <a href="{{ route('admin.orgaos-financiadores.index') }}"
                                class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-700 bg-white border">←
                                Cancelar</a>
                            <button type="submit"
                                class="px-6 py-2.5 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">✔
                                Atualizar Órgão</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda</h3>
                </div>
                <div class="p-6 text-sm text-slate-700">
                    <p>Atualize nome, sigla e esfera. A alteração reflete em todos os convênios vinculados a este órgão.</p>
                </div>
                <div class="px-6 py-4 border-t flex justify-end"><button @click="modalAjuda=false"
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg">Entendi 👍</button></div>
            </div>
        </div>
    </div>
@endsection
