@extends('layouts.app')

@section('title', 'Visão Geral')
@section('subtitle', 'Painel de acompanhamento de obras municipais')

@section('content')

    {{-- ── KPIs ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

        <div class="bg-white rounded-xl p-4 border shadow-sm">
            <p class="text-xs text-slate-500">Total de Obras</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $totalObras }}</p>
        </div>

        <div class="bg-white rounded-xl p-4 border shadow-sm">
            <p class="text-xs text-slate-500">Em Execução</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $obrasEmExecucao }}</p>
        </div>

        <div class="bg-white rounded-xl p-4 border shadow-sm">
            <p class="text-xs text-slate-500">Concluídas</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $obrasConcluidas }}</p>
        </div>

        <div class="bg-white rounded-xl p-4 border shadow-sm">
            <p class="text-xs text-slate-500">Convênios</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $totalConvenios }}</p>
        </div>

    </div>

    {{-- ── Gráfico + Obras recentes ─────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Gráfico por status --}}
        <div class="bg-white p-5 rounded-lg shadow border">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">
                Obras por Status
            </h3>
            <canvas id="graficoStatus" height="220"></canvas>
        </div>

        {{-- Obras recentes --}}
        <div class="bg-white rounded-lg shadow border overflow-hidden">
            <div class="px-5 py-4 border-b flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">Obras Recentes</h3>
                <a href="{{ route('obras.index') }}" class="text-xs text-blue-600 hover:underline">Ver todas →</a>
            </div>

            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Obra</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold text-slate-500 uppercase"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($ultimasObras as $obra)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800 truncate max-w-xs">
                                    {{ $obra->descricao }}
                                </div>
                                <div class="text-xs text-slate-500">{{ $obra->endereco ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium text-white"
                                    style="background-color: {{ $obra->status->cor ?? '#6c757d' }}">
                                    {{ $obra->status->nome }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('obras.show', $obra) }}" class="text-xs text-blue-600 hover:underline">Ver
                                    →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-8 text-slate-400 text-sm">
                                Nenhuma obra cadastrada ainda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            new Chart(document.getElementById('graficoStatus'), {
                type: 'bar',
                data: {
                    labels: @json($obrasPorStatus->pluck('nome')),
                    datasets: [{
                        label: 'Obras',
                        data: @json($obrasPorStatus->pluck('obras_count')),
                        backgroundColor: @json($obrasPorStatus->pluck('cor')),
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush
