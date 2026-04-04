@extends('layouts.app')

@section('title', 'Editar Obra')
@section('subtitle', 'Atualização dos dados da obra')

@section('content')

    <div class="flex justify-center">
        <div class="w-full max-w-5xl" x-data="{ modalAjuda: false }">

            {{-- BREADCRUMB --}}
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2 text-sm text-slate-600">
                    <a href="{{ route('obras.index') }}" class="hover:text-slate-900">Obras</a>
                    <span>›</span>
                    <a href="{{ route('obras.show', $obra) }}" class="hover:text-slate-900">
                        {{ Str::limit($obra->descricao, 50) }}
                    </a>
                    <span>›</span>
                    <span class="font-medium text-slate-900">Editar</span>
                </div>
                <button type="button" @click="modalAjuda = true"
                    class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                    ❓ Ajuda
                </button>
            </div>

            {{-- VOLTAR --}}
            <div class="mb-6">
                <a href="{{ route('obras.show', $obra) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                       text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-100 transition">
                    ← Voltar para a obra
                </a>
            </div>

            {{-- ERROS GERAIS --}}
            @if ($errors->has('geral'))
                <div class="mb-6 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">
                    ⚠️ {{ $errors->first('geral') }}
                </div>
            @endif

            <div class="bg-white rounded-xl shadow border border-slate-200">

                {{-- HEADER --}}
                <div class="px-8 py-6 border-b border-slate-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-xl shrink-0">✏️
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-xl font-semibold text-slate-900">Editar Obra</h2>
                            <p class="text-sm text-slate-500 mt-1 truncate">{{ $obra->descricao }}</p>
                        </div>
                    </div>
                </div>

                {{-- FORMULÁRIO --}}
                <form method="POST" action="{{ route('obras.update', $obra) }}">
                    @csrf
                    @method('PUT')

                    <div class="px-8 py-6 space-y-6">

                        {{-- ERROS DE VALIDAÇÃO --}}
                        @if ($errors->any() && !$errors->has('geral'))
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                                <strong class="text-sm">Corrija os erros abaixo:</strong>
                                <ul class="mt-2 text-sm list-disc ml-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- DESCRIÇÃO --}}
                        <div class="space-y-2">
                            <label for="descricao" class="block text-sm font-medium text-slate-700">
                                Descrição da Obra *
                            </label>
                            <textarea id="descricao" name="descricao" rows="3" required
                                placeholder="Descreva a obra de forma clara e completa..."
                                class="w-full px-4 py-2.5 rounded-lg border text-sm resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                       {{ $errors->has('descricao') ? 'border-red-400' : 'border-slate-300' }}">{{ old('descricao', $obra->descricao) }}</textarea>
                            @error('descricao')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- ENDEREÇO + STATUS --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div class="space-y-2">
                                <label for="endereco" class="block text-sm font-medium text-slate-700">
                                    Endereço / Localização
                                </label>
                                <input id="endereco" type="text" name="endereco"
                                    value="{{ old('endereco', $obra->endereco) }}"
                                    placeholder="Ex: Rua das Flores, 100 – Centro, Rio Grande da Serra/SP"
                                    class="w-full px-4 py-2.5 rounded-lg border text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                           {{ $errors->has('endereco') ? 'border-red-400' : 'border-slate-300' }}">
                                @error('endereco')
                                    <p class="text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="status_obra_id" class="block text-sm font-medium text-slate-700">
                                    Status *
                                </label>
                                <select id="status_obra_id" name="status_obra_id" required
                                    class="w-full px-4 py-2.5 rounded-lg border text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                           {{ $errors->has('status_obra_id') ? 'border-red-400' : 'border-slate-300' }}">
                                    <option value="">Selecione o status...</option>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->id }}"
                                            {{ old('status_obra_id', $obra->status_obra_id) == $status->id ? 'selected' : '' }}>
                                            {{ $status->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status_obra_id')
                                    <p class="text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        {{-- PROCESSO + DEMANDA --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div class="space-y-2">
                                <label for="processo_execucao" class="block text-sm font-medium text-slate-700">
                                    Processo de Execução
                                </label>
                                <input id="processo_execucao" type="text" name="processo_execucao"
                                    value="{{ old('processo_execucao', $obra->processo_execucao) }}"
                                    placeholder="Ex: 2026/0000123"
                                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div class="space-y-2">
                                <label for="demanda_proposta_id" class="block text-sm font-medium text-slate-700">
                                    Demanda / Proposta
                                    <span class="text-slate-400 font-normal">(opcional)</span>
                                </label>
                                <select id="demanda_proposta_id" name="demanda_proposta_id"
                                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Nenhuma</option>
                                    @foreach ($demandasAbertas as $demanda)
                                        <option value="{{ $demanda->id }}"
                                            {{ old('demanda_proposta_id', $obra->demanda_proposta_id) == $demanda->id ? 'selected' : '' }}>
                                            {{ $demanda->numero_demanda }} — {{ Str::limit($demanda->descricao, 60) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        {{-- OBSERVAÇÕES --}}
                        <div class="space-y-2">
                            <label for="observacoes" class="block text-sm font-medium text-slate-700">
                                Observações
                                <span class="text-slate-400 font-normal">(opcional)</span>
                            </label>
                            <textarea id="observacoes" name="observacoes" rows="4"
                                placeholder="Informações adicionais, histórico, situação atual..."
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 text-sm resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('observacoes', $obra->observacoes) }}</textarea>
                        </div>

                        {{-- CONVÊNIOS --}}
                        @if ($convenios->isNotEmpty())
                            <div class="space-y-3">
                                <label class="block text-sm font-medium text-slate-700">
                                    Convênios vinculados
                                    <span class="text-slate-400 font-normal">(opcional)</span>
                                </label>
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-48 overflow-y-auto border border-slate-200 rounded-lg p-3 bg-slate-50">
                                    @foreach ($convenios as $conv)
                                        <label
                                            class="flex items-center gap-2 text-sm cursor-pointer p-1.5 rounded hover:bg-white transition">
                                            <input type="checkbox" name="convenios[]" value="{{ $conv->id }}"
                                                {{ in_array($conv->id, $conveniosSelecionados) ? 'checked' : '' }}
                                                class="rounded text-blue-600 focus:ring-blue-500">
                                            <span class="text-slate-700 leading-tight">
                                                {{ $conv->numero_convenio_ano ?? 'Convênio #' . $conv->id }}
                                                @if ($conv->descricao)
                                                    <span class="text-slate-400 text-xs">—
                                                        {{ Str::limit($conv->descricao, 40) }}</span>
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="text-xs text-slate-400">
                                    💡 Você também pode gerenciar os vínculos diretamente na tela da obra, aba Convênios.
                                </p>
                            </div>
                        @endif

                    </div>

                    {{-- FOOTER --}}
                    <div
                        class="px-8 py-5 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-between items-center">
                        <a href="{{ route('obras.show', $obra) }}"
                            class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 transition">
                            ← Cancelar
                        </a>
                        <button type="submit"
                            class="px-6 py-2.5 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition">
                            ✔ Salvar Alterações
                        </button>
                    </div>

                </form>
            </div>

            {{-- MODAL AJUDA --}}
            <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                        <h3 class="text-lg font-semibold">📘 Ajuda — Editar Obra</h3>
                    </div>
                    <div class="p-6 space-y-3 text-sm text-slate-700">
                        <p><strong>Descrição:</strong> Nome completo e oficial da obra.</p>
                        <p><strong>Endereço:</strong> Localização completa onde a obra está sendo executada.</p>
                        <p><strong>Status:</strong> Fase atual da obra (Planejamento, Execução, Concluída...).</p>
                        <p><strong>Processo:</strong> Número do processo administrativo de execução.</p>
                        <p><strong>Demanda:</strong> Solicitação que originou esta obra (se houver).</p>
                        <p><strong>Convênios:</strong> Instrumentos de repasse vinculados. Podem ser gerenciados também na
                            aba Convênios da obra.</p>
                    </div>
                    <div class="px-6 py-4 border-t flex justify-end">
                        <button @click="modalAjuda = false" class="px-5 py-2 bg-blue-600 text-white rounded-lg">
                            Entendi 👍
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection
