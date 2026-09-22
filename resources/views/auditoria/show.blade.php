@extends('layouts.app')

@section('title', 'Histórico de Atividades')
@section('subtitle', 'Detalhe da atividade')

@section('content')

    @php
        $coresAcao = [
            'criou'   => 'bg-green-100 text-green-700',
            'alterou' => 'bg-blue-100 text-blue-700',
            'excluiu' => 'bg-red-100 text-red-700',
        ];
        $alteracao = $auditoria->acao === \App\Models\Auditoria::ALTEROU;
        $criacao   = $auditoria->acao === \App\Models\Auditoria::CRIOU;
    @endphp

    <div class="space-y-6 max-w-5xl">

        <div class="flex justify-between items-center gap-4">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">{{ $auditoria->descricao ?? 'Atividade' }}</h1>
                <p class="text-sm text-slate-500">Registro de auditoria #{{ $auditoria->id }} — somente consulta</p>
            </div>
            <a href="{{ route('auditoria.index') }}"
                class="px-4 py-2 text-sm rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 whitespace-nowrap">
                ← Voltar
            </a>
        </div>

        {{-- =========================================================
        RESUMO
        ========================================================= --}}
        <div class="bg-white rounded-xl shadow p-5 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <div class="text-xs text-slate-500">Usuário</div>
                <div class="font-medium text-slate-800">{{ $auditoria->usuario?->name ?? 'Sistema / usuário removido' }}</div>
                @if ($auditoria->usuario?->email)
                    <div class="text-xs text-slate-500">{{ $auditoria->usuario->email }}</div>
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500">Data/Hora</div>
                <div class="font-medium text-slate-800">{{ $auditoria->created_at->format('d/m/Y H:i:s') }}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500">Ação</div>
                <span class="inline-block mt-0.5 px-2 py-1 rounded-full text-xs font-medium {{ $coresAcao[$auditoria->acao] ?? 'bg-slate-100 text-slate-600' }}">
                    {{ $auditoria->acao_label }}
                </span>
            </div>
            <div>
                <div class="text-xs text-slate-500">Módulo</div>
                <div class="font-medium text-slate-800">{{ $auditoria->modulo_label }}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500">Registro</div>
                <div class="font-medium text-slate-800">#{{ $auditoria->auditable_id }}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500">IP</div>
                <div class="font-medium text-slate-800">{{ $auditoria->ip_address ?? '—' }}</div>
            </div>
            @if ($auditoria->user_agent)
                <div class="md:col-span-3">
                    <div class="text-xs text-slate-500">Navegador</div>
                    <div class="text-xs text-slate-600 break-all">{{ $auditoria->user_agent }}</div>
                </div>
            @endif
        </div>

        {{-- =========================================================
        DADOS (antes / depois)
        ========================================================= --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-5 py-3 border-b bg-slate-50 text-sm font-semibold text-slate-700">
                @if ($alteracao)
                    Campos alterados
                @elseif ($criacao)
                    Dados cadastrados
                @else
                    Dados do registro antes da exclusão
                @endif
            </div>

            <table class="w-full text-sm">
                <thead class="border-b">
                    <tr class="text-left text-slate-500">
                        <th class="px-5 py-2 w-1/4">Campo</th>
                        @if ($alteracao)
                            <th class="px-5 py-2">Antes</th>
                            <th class="px-5 py-2">Depois</th>
                        @else
                            <th class="px-5 py-2">Valor</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($linhas as $linha)
                        <tr class="align-top">
                            <td class="px-5 py-2 font-medium text-slate-700">{{ $linha['rotulo'] }}</td>
                            @if ($alteracao)
                                <td class="px-5 py-2 text-red-700 bg-red-50 whitespace-pre-line">{{ $linha['antes'] ?? '—' }}</td>
                                <td class="px-5 py-2 text-green-700 bg-green-50 whitespace-pre-line">{{ $linha['depois'] ?? '—' }}</td>
                            @else
                                <td class="px-5 py-2 text-slate-800 whitespace-pre-line">{{ ($criacao ? $linha['depois'] : $linha['antes']) ?? '—' }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-6 text-center text-slate-500">Sem dados registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

@endsection
