@extends('layouts.app')

@section('title', 'Visualizar Contrato')
@section('subtitle', 'Detalhes do contrato')

@section('content')

    <div x-data="{ modalAjuda: false, modalDelete: false }">

        {{-- =========================================================
        BREADCRUMB + AJUDA
        ========================================================= --}}
        <div class="flex items-center justify-between mb-6">

            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('contratos.index') }}" class="hover:text-slate-900">Contratos</a>
                <span>›</span>
                <span class="text-slate-900 font-medium">
                    {{ $contrato->numero_contrato_ano ?? 'Contrato #' . $contrato->id }}
                </span>
            </div>

            <button type="button" @click="modalAjuda = true"
                class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                ❓ Ajuda
            </button>

        </div>

        {{-- BOTÃO VOLTAR --}}
        <div class="mb-6 flex items-center gap-2">
            <a href="{{ route('contratos.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                   text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-100 transition">
                ← Voltar
            </a>

            @if ($contrato->obra)
                <a href="{{ route('obras.show', $contrato->obra) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                       text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition">
                    🏗️ Ver Obra
                </a>
            @endif
        </div>

        {{-- =========================================================
        ALERTA VIGÊNCIA
        ========================================================= --}}
        @if ($contrato->estaVencido())
            <div class="mb-6 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">
                ⚠️ <strong>Vigência vencida.</strong> Este contrato expirou em
                {{ $contrato->vigencia_contrato->format('d/m/Y') }}.
            </div>
        @elseif ($contrato->venceEm(30))
            <div class="mb-6 bg-yellow-50 border border-yellow-300 text-yellow-700 px-4 py-3 rounded-lg text-sm">
                ⚠️ <strong>Atenção:</strong> Este contrato vence em
                {{ now()->diffInDays($contrato->vigencia_contrato) }} dias
                ({{ $contrato->vigencia_contrato->format('d/m/Y') }}).
            </div>
        @endif

        <div class="flex justify-center">
            <div class="w-full max-w-4xl space-y-6">

                {{-- =========================================================
                CARD — DADOS DO CONTRATO
                ========================================================= --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                    {{-- HEADER --}}
                    <div class="px-8 py-6 border-b border-slate-200">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-2xl">
                                📋
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">
                                    {{ $contrato->numero_contrato_ano ?? 'Contrato sem número' }}
                                </h2>
                                <p class="text-sm text-slate-600 mt-1">
                                    Licitação: {{ $contrato->processo_licitacao ?? '—' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- DADOS --}}
                    <div class="px-8 py-6 space-y-6">

                        {{-- VÍNCULO --}}
                        <div>
                            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">
                                Vínculo
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">

                                <div>
                                    <p class="text-slate-500">Obra</p>
                                    <a href="{{ route('obras.show', $contrato->obra) }}"
                                        class="font-medium text-blue-600 hover:underline">
                                        {{ $contrato->obra->descricao }}
                                    </a>
                                    @if ($contrato->obra->status)
                                        <span class="ml-2 px-2 py-0.5 rounded text-xs text-white"
                                            style="background-color: {{ $contrato->obra->status->cor ?? '#64748b' }}">
                                            {{ $contrato->obra->status->nome }}
                                        </span>
                                    @endif
                                </div>

                                <div>
                                    <p class="text-slate-500">Empresa Contratada</p>
                                    <p class="font-medium text-slate-800">
                                        {{ $contrato->empresa->nomeExibicao() }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        CNPJ: {{ $contrato->empresa->cnpj }}
                                    </p>
                                </div>

                            </div>
                        </div>

                        {{-- VALOR --}}
                        <div>
                            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">
                                Valor Contratual
                            </h3>
                            <div class="bg-slate-50 rounded-lg p-4 inline-block">
                                <p class="text-xs text-slate-500 mb-1">Valor total</p>
                                <p class="text-2xl font-bold text-slate-800">
                                    R$ {{ $contrato->valor_contrato ? number_format($contrato->valor_contrato, 2, ',', '.') : '—' }}
                                </p>
                            </div>
                        </div>

                        {{-- DATAS --}}
                        <div>
                            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">
                                Datas
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">

                                <div>
                                    <p class="text-slate-500">Assinatura</p>
                                    <p class="font-medium text-slate-800">
                                        {{ $contrato->data_assinatura?->format('d/m/Y') ?? '—' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-slate-500">Ordem de Início</p>
                                    <p class="font-medium text-slate-800">
                                        {{ $contrato->ordem_inicio?->format('d/m/Y') ?? '—' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-slate-500">Vigência</p>
                                    <p class="font-medium
                                        {{ $contrato->estaVencido() ? 'text-red-600' : ($contrato->venceEm(30) ? 'text-yellow-600' : 'text-slate-800') }}">
                                        {{ $contrato->vigencia_contrato?->format('d/m/Y') ?? '—' }}
                                        @if ($contrato->estaVencido())
                                            <span class="text-xs">(vencido)</span>
                                        @elseif ($contrato->venceEm(30))
                                            <span class="text-xs">(vence em breve)</span>
                                        @endif
                                    </p>
                                </div>

                            </div>
                        </div>

                        {{-- METADADOS --}}
                        <div>
                            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">
                                Informações do Sistema
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                                <div>
                                    <p class="text-slate-500">Cadastrado em</p>
                                    <p class="font-medium text-slate-800">
                                        {{ $contrato->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Última atualização</p>
                                    <p class="font-medium text-slate-800">
                                        {{ $contrato->updated_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- AÇÕES --}}
                    <div class="px-8 py-5 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-between items-center">

                        <button type="button" @click="modalDelete = true"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium
                               text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 transition">
                            🗑 Excluir Contrato
                        </button>

                        <a href="{{ route('contratos.edit', $contrato) }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium
                               text-white bg-blue-600 hover:bg-blue-700 transition">
                            ✏️ Editar Contrato
                        </a>

                    </div>

                </div>

                {{-- =========================================================
                CARD — EXECUÇÕES / MEDIÇÕES
                ========================================================= --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                    <div class="px-8 py-5 border-b flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-800">📊 Execuções (Medições)</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Histórico de medições vinculadas a este contrato
                            </p>
                        </div>
                        <a href="{{ route('obras.execucoes.create', $contrato->obra) }}"
                            class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700">
                            ➕ Nova Medição
                        </a>
                    </div>

                    <div class="p-6">
                        @forelse($contrato->execucoes as $ex)
                            <div class="flex items-center justify-between py-3 border-b last:border-0 text-sm">
                                <div>
                                    <p class="font-medium text-slate-800">
                                        {{ $ex->data_medicao?->format('d/m/Y') ?? '—' }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ $ex->descricao ?? 'Sem descrição' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-green-700">
                                        R$ {{ number_format($ex->valor_medido ?? 0, 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 text-center py-6">
                                Nenhuma medição registrada para este contrato.
                            </p>
                        @endforelse
                    </div>

                </div>

            </div>
        </div>

        {{-- =========================================================
        MODAL AJUDA
        ========================================================= --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">

                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Visualização de Contrato</h3>
                    <p class="text-sm opacity-90">Entenda os dados exibidos</p>
                </div>

                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p><strong>Vínculo:</strong> Obra e empresa relacionadas ao contrato.</p>
                    <p><strong>Valor Contratual:</strong> Total acordado no contrato.</p>
                    <p><strong>Datas:</strong> Assinatura, início e prazo de vigência.</p>
                    <p><strong>Execuções:</strong> Medições registradas ao longo da execução da obra.</p>

                    <div class="bg-slate-50 p-4 rounded text-xs">
                        💡 Use "Nova Medição" para registrar o avanço financeiro da obra.
                    </div>
                </div>

                <div class="px-6 py-4 border-t flex justify-end">
                    <button @click="modalAjuda = false" class="px-5 py-2 bg-blue-600 text-white rounded-lg">
                        Entendi 👍
                    </button>
                </div>

            </div>
        </div>

        {{-- =========================================================
        MODAL EXCLUSÃO
        ========================================================= --}}
        <div x-show="modalDelete" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md text-center">

                <h3 class="text-lg font-semibold text-red-600 mb-4">
                    Confirmar exclusão
                </h3>

                <p class="text-sm text-slate-600 mb-6">
                    Essa ação é irreversível. As execuções vinculadas a este contrato também serão removidas.
                </p>

                <div class="flex justify-center gap-3">
                    <button @click="modalDelete = false" class="px-4 py-2 border rounded">
                        Cancelar
                    </button>

                    <form action="{{ route('contratos.destroy', $contrato) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="px-5 py-2 bg-red-600 text-white rounded">
                            Excluir
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>

@endsection