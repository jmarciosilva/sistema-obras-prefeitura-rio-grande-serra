@extends('layouts.app')

@section('title', 'Convênios')
@section('subtitle', 'Gestão de convênios e instrumentos de financiamento')

@section('content')

    <div class="space-y-6" x-data="convenioTable()">

        {{-- HEADER --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">Convênios</h1>
                <p class="text-sm text-slate-500">Gerencie os convênios vinculados às obras municipais.</p>
            </div>
            <div class="flex gap-2">
                <button type="button" @click="modalAjuda = true"
                    class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                    ❓ Ajuda
                </button>
                <a href="{{ route('convenios.create') }}"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    ➕ Novo Convênio
                </a>
            </div>
        </div>

        {{-- BUSCA / FILTROS --}}
        <form method="GET" class="bg-white rounded-xl shadow p-4 flex flex-wrap gap-3">

            <input type="text" name="busca" value="{{ request('busca') }}"
                placeholder="Descrição ou número do convênio..."
                class="flex-1 min-w-48 border rounded-lg px-4 py-2 text-sm">

            <select name="orgao_id" class="border rounded-lg px-4 py-2 text-sm">
                <option value="">Todos os órgãos</option>
                @foreach ($orgaos as $o)
                    <option value="{{ $o->id }}" {{ request('orgao_id') == $o->id ? 'selected' : '' }}>
                        {{ $o->nome }}
                    </option>
                @endforeach
            </select>

            <button class="bg-blue-600 text-white px-5 rounded-lg text-sm">Buscar</button>

            @if (request('busca') || request('orgao_id'))
                <a href="{{ route('convenios.index') }}"
                    class="px-4 py-2 text-sm text-slate-600 border rounded-lg hover:bg-slate-50">
                    ✕ Limpar
                </a>
            @endif

        </form>

        {{-- FEEDBACK --}}
        @if (session('sucesso'))
            <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm">
                ✅ {{ session('sucesso') }}
            </div>
        @endif

        {{-- TABELA --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">

            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left">Convênio / Número</th>
                        <th class="px-4 py-3 text-left">Categoria</th>
                        <th class="px-4 py-3 text-left">Órgão Financiador</th>
                        <th class="px-4 py-3 text-left">Valor Repasse</th>
                        <th class="px-4 py-3 text-left">Vigência</th>
                        <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($convenios as $c)
                        <tr class="hover:bg-slate-50">

                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">
                                    {{ $c->numero_convenio_ano ?? '—' }}
                                </div>
                                <div class="text-xs text-slate-500 max-w-xs truncate">
                                    {{ $c->descricao ?? '—' }}
                                </div>
                            </td>

                            <td class="px-4 py-3 text-slate-600">
                                {{ $c->categoria->nome ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-slate-600">
                                {{ $c->orgaoFinanciador->nome ?? '—' }}
                            </td>

                            <td class="px-4 py-3 font-medium text-slate-800">
                                {{ $c->valor_repasse_contrapartida
                                    ? 'R$ ' . number_format($c->valor_repasse_contrapartida, 2, ',', '.')
                                    : '—' }}
                            </td>

                            <td class="px-4 py-3">
                                @if ($c->vigencia)
                                    @php $vencido = $c->vigencia->isPast(); $breve = !$vencido && $c->vigencia->diffInDays(now()) <= 30; @endphp
                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                        {{ $vencido ? 'bg-red-100 text-red-700' : ($breve ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                                        {{ $c->vigencia->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('convenios.obras', $c) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100"
                                        title="Gerenciar obras vinculadas">
                                        🔗 Obras
                                        @if ($c->obras_count > 0)
                                            <span class="ml-0.5 font-semibold">({{ $c->obras_count }})</span>
                                        @endif
                                    </a>
                                    <a href="{{ route('convenios.show', $c) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                        👁 Ver
                                    </a>
                                    <a href="{{ route('convenios.edit', $c) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100">
                                        ✏️ Editar
                                    </a>
                                    <button type="button"
                                        @click="abrirModalDelete('{{ route('convenios.destroy', $c) }}')"
                                        class="px-3 py-1 text-xs rounded-lg border border-red-200 text-red-700 bg-red-50 hover:bg-red-100">
                                        🗑 Excluir
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-500">
                                Nenhum convênio encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="p-4 border-t">
                {{ $convenios->links() }}
            </div>

        </div>

        {{-- MODAL AJUDA --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Convênios</h3>
                    <p class="text-sm opacity-90">Como utilizar esta tela</p>
                </div>
                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p><strong>➕ Novo Convênio:</strong> Cadastra um instrumento de financiamento com órgão concedente.</p>
                    <p><strong>👁 Visualizar:</strong> Exibe todos os dados e obras vinculadas ao convênio.</p>
                    <p><strong>✏️ Editar:</strong> Atualiza informações do convênio.</p>
                    <p><strong>🗑 Excluir:</strong> Remove o convênio permanentemente.</p>
                    <div class="p-4 bg-slate-50 rounded-lg text-xs">
                        💡 A vigência é destacada em <span class="text-yellow-700 font-medium">amarelo</span> quando vence
                        em até 30 dias e em <span class="text-red-700 font-medium">vermelho</span> quando já venceu.
                    </div>
                </div>
                <div class="px-6 py-4 border-t flex justify-end">
                    <button @click="modalAjuda = false" class="px-5 py-2 bg-blue-600 text-white rounded-lg">
                        Entendi 👍
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL EXCLUSÃO --}}
        <div x-show="modalDelete" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md text-center">
                <h3 class="text-lg font-semibold text-red-600 mb-4">Confirmar exclusão</h3>
                <p class="text-sm text-slate-600 mb-6">Essa ação não poderá ser desfeita.</p>
                <div class="flex justify-center gap-3">
                    <button @click="modalDelete = false" class="px-4 py-2 border rounded">Cancelar</button>
                    <form :action="url" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="px-5 py-2 bg-red-600 text-white rounded">Excluir</button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
        function convenioTable() {
            return {
                modalDelete: false,
                modalAjuda: false,
                url: '',
                abrirModalDelete(url) { this.url = url; this.modalDelete = true; }
            }
        }
    </script>

@endsection