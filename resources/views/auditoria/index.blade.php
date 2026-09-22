@extends('layouts.app')

@section('title', 'Histórico de Atividades')
@section('subtitle', 'Criação, alteração e exclusão de obras, contratos e medições')

@section('content')

    @php
        $coresAcao = [
            'criou'   => 'bg-green-100 text-green-700',
            'alterou' => 'bg-blue-100 text-blue-700',
            'excluiu' => 'bg-red-100 text-red-700',
        ];
    @endphp

    <div class="space-y-6">

        {{-- =========================================================
        HEADER DA PÁGINA
        ========================================================= --}}
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Histórico de Atividades</h1>
            <p class="text-sm text-slate-500">
                Registro de quem criou, alterou ou excluiu obras, contratos e medições (somente consulta).
            </p>
        </div>

        {{-- =========================================================
        FILTROS
        ========================================================= --}}
        <form method="GET" class="bg-white rounded-xl shadow p-4 grid grid-cols-1 md:grid-cols-6 gap-3 items-end">

            <label class="text-xs text-slate-600 md:col-span-2">
                Usuário
                <select name="usuario" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    @foreach ($usuarios as $u)
                        <option value="{{ $u->id }}" @selected((string) request('usuario') === (string) $u->id)>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="text-xs text-slate-600">
                De
                <input type="date" name="de" value="{{ request('de') }}"
                    class="mt-1 w-full border rounded-lg px-3 py-2 text-sm">
            </label>

            <label class="text-xs text-slate-600">
                Até
                <input type="date" name="ate" value="{{ request('ate') }}"
                    class="mt-1 w-full border rounded-lg px-3 py-2 text-sm">
            </label>

            <label class="text-xs text-slate-600">
                Módulo
                <select name="modulo" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    @foreach (\App\Models\Auditoria::MODULOS as $chave => $modulo)
                        <option value="{{ $chave }}" @selected(request('modulo') === $chave)>{{ $modulo['rotulo'] }}</option>
                    @endforeach
                </select>
            </label>

            <label class="text-xs text-slate-600">
                Ação
                <select name="acao" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="">Todas</option>
                    @foreach (\App\Models\Auditoria::ACOES as $chave => $rotulo)
                        <option value="{{ $chave }}" @selected(request('acao') === $chave)>{{ $rotulo }}</option>
                    @endforeach
                </select>
            </label>

            <div class="md:col-span-6 flex gap-2 justify-end">
                <a href="{{ route('auditoria.index') }}"
                    class="px-4 py-2 text-sm rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">
                    Limpar
                </a>
                <button class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm hover:bg-blue-700">
                    Filtrar
                </button>
            </div>
        </form>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">
                Verifique os filtros informados.
            </div>
        @endif

        {{-- =========================================================
        TABELA
        ========================================================= --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b">
                        <tr class="text-left">
                            <th class="px-4 py-3 whitespace-nowrap">Data/Hora</th>
                            <th class="px-4 py-3">Usuário</th>
                            <th class="px-4 py-3">Ação</th>
                            <th class="px-4 py-3">Módulo</th>
                            <th class="px-4 py-3">Registro</th>
                            <th class="px-4 py-3">Descrição</th>
                            <th class="px-4 py-3 text-right"></th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @forelse ($auditorias as $a)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                    {{ $a->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-slate-800">
                                    {{ $a->usuario?->name ?? 'Sistema / usuário removido' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $coresAcao[$a->acao] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $a->acao_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $a->modulo_label }}</td>
                                <td class="px-4 py-3 text-slate-500">#{{ $a->auditable_id }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $a->descricao }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('auditoria.show', $a) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                        👁 Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 text-slate-500">
                                    Nenhuma atividade encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $auditorias->links() }}
            </div>
        </div>

    </div>

@endsection
