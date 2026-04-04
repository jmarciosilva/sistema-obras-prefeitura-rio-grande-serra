@extends('layouts.app')
@section('title', 'Nova Demanda')
@section('content')
    <div x-data="{ modalAjuda: false }">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('admin.demandas-propostas.index') }}" class="hover:text-slate-900">Demandas e Propostas</a>
                <span>›</span><span class="text-slate-900 font-medium">Nova Demanda</span>
            </div>
            <button type="button" @click="modalAjuda=true"
                class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">❓
                Ajuda</button>
        </div>
        <div class="mb-6"><a href="{{ route('admin.demandas-propostas.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-100 transition">←
                Voltar</a></div>

        <div class="flex justify-center">
            <div class="w-full max-w-3xl">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="px-8 py-6 border-b">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-xl">📋</div>
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">Nova Demanda / Proposta</h2>
                                <p class="text-sm text-slate-600 mt-1">Registre a solicitação que origina a obra</p>
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

                    <form method="POST" action="{{ route('admin.demandas-propostas.store') }}">
                        @csrf
                        <div class="px-8 py-6 space-y-6">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Número da Demanda *</label>
                                    <input type="text" name="numero_demanda" value="{{ old('numero_demanda') }}" required
                                        placeholder="Ex: DEM-2024-001"
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 font-mono">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Data da Solicitação</label>
                                    <input type="date" name="data_solicitacao" value="{{ old('data_solicitacao') }}"
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">Descrição *</label>
                                <textarea name="descricao" rows="2" required placeholder="Descreva brevemente o que está sendo solicitado..."
                                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 resize-none">{{ old('descricao') }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Origem *</label>
                                    <select name="origem" required
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                        @foreach ($origens as $val => $label)
                                            <option value="{{ $val }}" {{ old('origem') === $val ? 'selected' : '' }}>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Situação *</label>
                                    <select name="situacao" required
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                        @foreach ($situacoes as $val => $label)
                                            <option value="{{ $val }}"
                                                {{ old('situacao', 'pendente') === $val ? 'selected' : '' }}>{{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Solicitante</label>
                                    <input type="text" name="solicitante" value="{{ old('solicitante') }}"
                                        placeholder="Nome ou órgão"
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">Observações <span
                                        class="text-slate-400 font-normal">(opcional)</span></label>
                                <textarea name="observacoes" rows="3" placeholder="Informações adicionais, histórico, justificativa..."
                                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 resize-none">{{ old('observacoes') }}</textarea>
                            </div>

                        </div>
                        <div class="px-8 py-5 border-t bg-slate-50 rounded-b-xl flex justify-between">
                            <a href="{{ route('admin.demandas-propostas.index') }}"
                                class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-700 bg-white border">←
                                Cancelar</a>
                            <button type="submit"
                                class="px-6 py-2.5 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">✔
                                Salvar Demanda</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Nova Demanda</h3>
                </div>
                <div class="p-6 space-y-3 text-sm text-slate-700">
                    <p><strong>Número:</strong> Identificador único da demanda (ex: DEM-2024-001).</p>
                    <p><strong>Origem:</strong> Quem originou a demanda (Secretaria, Vereador, Estado, Federal, Outros).</p>
                    <p><strong>Situação:</strong> Pendente ao criar. Mude para "Aprovada" quando virar uma obra formal.</p>
                </div>
                <div class="px-6 py-4 border-t flex justify-end"><button @click="modalAjuda=false"
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg">Entendi 👍</button></div>
            </div>
        </div>
    </div>
@endsection
