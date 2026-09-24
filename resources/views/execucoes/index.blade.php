@extends('layouts.app')

@section('title', 'Medições da Obra')
@section('subtitle', 'Histórico completo de execução financeira')

@section('content')

    <div class="space-y-6" x-data="{ modalAjuda: false }">

        {{-- BREADCRUMB --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('obras.index') }}" class="hover:text-slate-900">Obras</a>
                <span>›</span>
                <a href="{{ route('obras.show', $obra) }}" class="hover:text-slate-900">
                    {{ Str::limit($obra->descricao, 50) }}
                </a>
                <span>›</span>
                <span class="text-slate-900 font-medium">Medições</span>
            </div>
            <div class="flex gap-2">
                <button type="button" @click="modalAjuda = true"
                    class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                    ❓ Ajuda
                </button>
                @if (auth()->user()->perfil !== 'operador')
                    <a href="{{ route('obras.execucoes.create', $obra) }}"
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700">
                        ➕ Nova Medição
                    </a>
                @endif
            </div>
        </div>

        <div class="mb-2">
            <a href="{{ route('obras.show', $obra) }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                   text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-100 transition">
                ← Voltar à Obra
            </a>
        </div>

        {{-- FEEDBACK --}}
        @if (session('sucesso'))
            <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm">
                ✅ {{ session('sucesso') }}
            </div>
        @endif

        {{-- KPIs CONSOLIDADOS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <p class="text-xs text-slate-500 mb-1">Valor Contratado</p>
                <p class="text-lg font-bold text-slate-800">
                    R$ {{ number_format($valorContrato, 2, ',', '.') }}
                </p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <p class="text-xs text-slate-500 mb-1">Total Medido</p>
                <p class="text-lg font-bold text-green-700">
                    R$ {{ number_format($totalMedido, 2, ',', '.') }}
                </p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <p class="text-xs text-slate-500 mb-1">Saldo</p>
                <p class="text-lg font-bold {{ $saldoGeral <= 0 ? 'text-red-600' : 'text-blue-700' }}">
                    R$ {{ number_format($saldoGeral, 2, ',', '.') }}
                </p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <p class="text-xs text-slate-500 mb-1">% Executado</p>
                <p class="text-lg font-bold text-indigo-700">
                    {{ number_format($percentualGeral, 1) }}%
                </p>
                <div class="mt-2 w-full bg-slate-200 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full
                        {{ $percentualGeral >= 80 ? 'bg-green-500' : ($percentualGeral >= 40 ? 'bg-yellow-500' : 'bg-blue-600') }}"
                        style="width: {{ min($percentualGeral, 100) }}%">
                    </div>
                </div>
            </div>

        </div>

        {{-- TABELA DE MEDIÇÕES --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">
                    📊 Histórico de Medições
                    <span class="ml-2 text-sm font-normal text-slate-500">
                        ({{ $execucoes->total() }} registro(s))
                    </span>
                </h3>
            </div>

            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs text-slate-500 font-semibold">#</th>
                        <th class="px-4 py-3 text-left text-xs text-slate-500 font-semibold">Data</th>
                        <th class="px-4 py-3 text-left text-xs text-slate-500 font-semibold">Contrato / Empresa</th>
                        <th class="px-4 py-3 text-right text-xs text-slate-500 font-semibold">Valor Medido</th>
                        <th class="px-4 py-3 text-right text-xs text-slate-500 font-semibold">Saldo após</th>
                        <th class="px-4 py-3 text-center text-xs text-slate-500 font-semibold">% Acum.</th>
                        <th class="px-4 py-3 text-left text-xs text-slate-500 font-semibold">Observação</th>
                        @if (in_array(auth()->user()->perfil, ['admin', 'tecnico']))
                            <th class="px-4 py-3 text-right text-xs text-slate-500 font-semibold">Ações</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($execucoes as $i => $ex)
                        <tr class="hover:bg-slate-50">

                            <td class="px-4 py-3 text-slate-400 text-xs">
                                {{ $execucoes->total() - ($execucoes->currentPage() - 1) * $execucoes->perPage() - $i }}ª
                            </td>

                            <td class="px-4 py-3 font-medium text-slate-800 whitespace-nowrap">
                                {{ $ex->data_medicao->format('d/m/Y') }}
                            </td>

                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-700">
                                    {{ $ex->contrato->numero_contrato_ano ?? 'Contrato #' . $ex->contrato_id }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ $ex->contrato->empresa->nomeExibicao() }}
                                </p>
                            </td>

                            <td class="px-4 py-3 text-right font-semibold text-green-700 whitespace-nowrap">
                                R$ {{ number_format($ex->valor_medido, 2, ',', '.') }}
                            </td>

                            <td
                                class="px-4 py-3 text-right whitespace-nowrap
                                {{ ($ex->saldo_contratual ?? 0) <= 0 ? 'text-red-600 font-semibold' : 'text-slate-600' }}">
                                @if ($ex->saldo_contratual !== null)
                                    R$ {{ number_format($ex->saldo_contratual, 2, ',', '.') }}
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-center">
                                @if ($ex->percentual_executado !== null)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $ex->percentual_executado >= 80
                                            ? 'bg-green-100 text-green-700'
                                            : ($ex->percentual_executado >= 40
                                                ? 'bg-yellow-100 text-yellow-700'
                                                : 'bg-blue-100 text-blue-700') }}">
                                        {{ number_format($ex->percentual_executado, 1) }}%
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-slate-500 text-xs max-w-xs">
                                {{ Str::limit($ex->observacao ?? '—', 60) }}
                            </td>

                            @if (in_array(auth()->user()->perfil, ['admin', 'tecnico']))
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('obras.execucoes.edit', [$obra, $ex]) }}" title="Corrigir medição"
                                            class="px-2 py-1.5 text-xs rounded border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100">
                                            ✏️
                                        </a>
                                        @if (auth()->user()->perfil === 'admin')
                                        <button type="button" title="Excluir medição"
                                            @click="$dispatch('excluir-medicao', { url: '{{ route('obras.execucoes.destroy', [$obra, $ex]) }}', resumo: '{{ $ex->data_medicao?->format('d/m/Y') }} — R$ {{ number_format($ex->valor_medido, 2, ',', '.') }}' })"
                                            class="px-2 py-1.5 text-xs rounded border border-red-200 text-red-700 bg-red-50 hover:bg-red-100">
                                            🗑
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            @endif

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-slate-400">
                                <p class="text-3xl mb-2">📊</p>
                                <p class="text-sm">Nenhuma medição registrada para esta obra.</p>
                                @if (auth()->user()->perfil !== 'operador')
                                    <a href="{{ route('obras.execucoes.create', $obra) }}"
                                        class="inline-block mt-3 text-sm text-green-600 hover:underline">
                                        ➕ Registrar primeira medição
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($execucoes->hasPages())
                <div class="p-4 border-t">
                    {{ $execucoes->links() }}
                </div>
            @endif

        </div>

        {{-- MODAL AJUDA --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Medições</h3>
                    <p class="text-sm opacity-90">Entenda o histórico de execução</p>
                </div>
                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p><strong>Valor Medido:</strong> Valor financeiro desta parcela de execução.</p>
                    <p><strong>Saldo após:</strong> Saldo contratual restante após esta medição ter sido registrada.</p>
                    <p><strong>% Acum.:</strong> Percentual acumulado da execução do contrato até esta medição.</p>
                    <div class="bg-slate-50 p-4 rounded text-xs">
                        💡 Cada medição é acumulativa — cada medição é um novo registro no histórico. Medições lançadas
                        erradas podem ser corrigidas ou excluídas por administradores e técnicos (exclusão somente administradores), com motivo obrigatório
                        registrado no Histórico de Atividades.
                    </div>
                </div>
                <div class="px-6 py-4 border-t flex justify-end">
                    <button @click="modalAjuda = false" class="px-5 py-2 bg-green-600 text-white rounded-lg">
                        Entendi 👍
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL EXCLUSÃO (motivo obrigatório → auditoria) --}}
        @include('execucoes._modal-excluir')

    </div>

@endsection
