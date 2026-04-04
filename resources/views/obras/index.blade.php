@extends('layouts.app')

@section('title', 'Obras')
@section('subtitle', 'Gestão e acompanhamento das obras do município')

@section('content')

    <div class="space-y-6" x-data="obraTable()">

        {{-- =========================================================
    HEADER
    ========================================================= --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">Obras</h1>
                <p class="text-sm text-slate-500">
                    Gerencie e acompanhe todas as obras do município.
                </p>
            </div>

            <div class="flex gap-2">

                {{-- AJUDA --}}
                <button @click="modalAjuda = true"
                    class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg border border-amber-200 hover:bg-amber-200">
                    ❓ Ajuda
                </button>

                {{-- NOVA OBRA --}}
                @if (auth()->user()->perfil !== 'operador')
                    <a href="{{ route('obras.create') }}"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        ➕ Nova Obra
                    </a>
                @endif

            </div>
        </div>

        {{-- =========================================================
    BUSCA
    ========================================================= --}}
        <form method="GET" class="bg-white rounded-xl shadow p-4 flex gap-3">

            <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Digite descrição da obra..."
                class="flex-1 border rounded-lg px-4 py-2 text-sm">

            <button class="bg-blue-600 text-white px-5 rounded-lg text-sm">
                Buscar
            </button>

        </form>

        {{-- =========================================================
    TABELA
    ========================================================= --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">

            <table class="w-full text-sm">

                {{-- HEADER --}}
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left">Obra</th>
                        <th>Status</th>
                        <th class="text-center">% Execução</th>
                        <th class="text-center">Valor Medido</th>
                        <th class="text-center">Saldo</th>
                        <th class="text-right px-4">Ações</th>
                    </tr>
                </thead>

                {{-- BODY --}}
                <tbody class="divide-y">

                    @forelse($obras as $obra)
                        <tr class="hover:bg-slate-50">

                            {{-- OBRA --}}
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">
                                    {{ $obra->descricao }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $obra->endereco ?? '—' }}
                                </div>
                            </td>

                            {{-- STATUS --}}
                            <td>
                                <span class="px-2 py-1 rounded-full text-xs font-medium text-white"
                                    style="background-color: {{ $obra->status->cor ?? '#64748b' }}">
                                    {{ $obra->status->nome ?? '—' }}
                                </span>
                            </td>

                            {{-- EXECUÇÃO --}}
                            <td class="text-center">

                                @php
                                    $percentual = $obra->percentual_executado ?? 0;
                                    $cor =
                                        $percentual >= 80
                                            ? 'bg-green-600'
                                            : ($percentual >= 40
                                                ? 'bg-yellow-500'
                                                : 'bg-red-500');
                                @endphp

                                <div class="w-full bg-slate-200 rounded-full h-2 mb-1">
                                    <div class="{{ $cor }} h-2 rounded-full" style="width: {{ $percentual }}%">
                                    </div>
                                </div>

                                <span class="text-xs text-slate-600">
                                    {{ number_format($percentual, 1) }}%
                                </span>

                            </td>

                            {{-- VALOR --}}
                            <td class="text-center">
                                R$ {{ number_format($obra->valor_medido ?? 0, 2, ',', '.') }}
                            </td>

                            {{-- SALDO --}}
                            <td class="text-center text-red-600">
                                R$ {{ number_format($obra->saldo_contratual ?? 0, 2, ',', '.') }}
                            </td>

                            {{-- AÇÕES --}}
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">

                                    {{-- VER --}}
                                    <a href="{{ route('obras.show', $obra) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                        👁 Ver Obra
                                    </a>

                                    {{-- EDITAR --}}
                                    @if (auth()->user()->perfil !== 'operador')
                                        <a href="{{ route('obras.edit', $obra) }}"
                                            class="px-3 py-1 text-xs rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100">
                                            ✏️ Editar
                                        </a>
                                    @endif

                                    {{-- EXCLUIR --}}
                                    @if (auth()->user()->perfil === 'admin')
                                        <button @click="abrirModalDelete('{{ route('obras.destroy', $obra) }}')"
                                            class="px-3 py-1 text-xs rounded-lg border border-red-200 text-red-700 bg-red-50 hover:bg-red-100">
                                            🗑 Excluir
                                        </button>
                                    @endif

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-500">
                                Nenhuma obra encontrada.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

            {{-- PAGINAÇÃO --}}
            <div class="p-4 border-t">
                {{ $obras->links() }}
            </div>

        </div>

        {{-- =========================================================
    MODAL AJUDA
    ========================================================= --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">

                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Obras</h3>
                    <p class="text-sm opacity-90">Como utilizar esta tela</p>
                </div>

                <div class="p-6 space-y-3 text-sm text-slate-700">
                    <p><strong>➕ Nova Obra:</strong> Cadastra uma nova obra.</p>
                    <p><strong>👁 Ver:</strong> Visualiza detalhes completos.</p>
                    <p><strong>✏️ Editar:</strong> Atualiza informações.</p>
                    <p><strong>🗑 Excluir:</strong> Remove a obra.</p>
                    <div class="mt-3 p-3 bg-slate-50 rounded text-xs">
                        💡 Use a busca para localizar rapidamente.
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
    MODAL DELETE
    ========================================================= --}}
        <div x-show="modalDelete" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center">

            <div class="bg-white rounded-xl p-6 w-full max-w-md text-center">

                <h3 class="text-lg font-semibold text-red-600 mb-4">
                    Confirmar exclusão
                </h3>

                <p class="text-sm text-slate-600 mb-6">
                    Essa ação não poderá ser desfeita.
                </p>

                <div class="flex justify-center gap-3">
                    <button @click="modalDelete = false" class="px-4 py-2 border rounded">
                        Cancelar
                    </button>

                    <form :action="url" method="POST">
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

    {{-- =========================================================
ALPINE
========================================================= --}}
    <script>
        function obraTable() {
            return {
                modalAjuda: false,
                modalDelete: false,
                url: '',

                abrirModalDelete(url) {
                    this.url = url;
                    this.modalDelete = true;
                }
            }
        }
    </script>

@endsection
