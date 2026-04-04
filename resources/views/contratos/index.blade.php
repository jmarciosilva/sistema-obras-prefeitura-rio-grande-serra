@extends('layouts.app')

@section('title', 'Contratos')
@section('subtitle', 'Gestão de contratos de obras públicas')

@section('content')

    <div class="space-y-6" x-data="contratoTable()">

        {{-- =========================================================
        HEADER DA PÁGINA
        ========================================================= --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">Contratos</h1>
                <p class="text-sm text-slate-500">
                    Gerencie contratos vinculados às obras municipais.
                </p>
            </div>

            <div class="flex gap-2">

                <button type="button" @click="modalAjuda = true"
                    class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                    ❓ Ajuda
                </button>

                <a href="{{ route('contratos.create') }}"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    ➕ Novo Contrato
                </a>

            </div>
        </div>

        {{-- =========================================================
        FORMULÁRIO DE BUSCA
        ========================================================= --}}
        <form method="GET" class="bg-white rounded-xl shadow p-4 flex gap-3">

            <input type="text" name="busca" value="{{ request('busca') }}"
                placeholder="Digite número do contrato, obra ou empresa..."
                class="flex-1 border rounded-lg px-4 py-2 text-sm">

            <button class="bg-blue-600 text-white px-5 rounded-lg text-sm">
                Buscar
            </button>

        </form>

        {{-- =========================================================
        MENSAGEM DE SUCESSO
        ========================================================= --}}
        @if (session('sucesso'))
            <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm">
                ✅ {{ session('sucesso') }}
            </div>
        @endif

        {{-- =========================================================
        TABELA DE CONTRATOS
        ========================================================= --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">

            <table class="w-full text-sm">

                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left">Contrato / Licitação</th>
                        <th class="px-4 py-3 text-left">Obra</th>
                        <th class="px-4 py-3 text-left">Empresa</th>
                        <th class="px-4 py-3 text-left">Valor</th>
                        <th class="px-4 py-3 text-left">Vigência</th>
                        <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                </thead>

                <tbody class="divide-y">

                    @forelse($contratos as $c)
                        <tr class="hover:bg-slate-50">

                            {{-- CONTRATO --}}
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">
                                    {{ $c->numero_contrato_ano ?? '—' }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    Licit.: {{ $c->processo_licitacao ?? '—' }}
                                </div>
                            </td>

                            {{-- OBRA --}}
                            <td class="px-4 py-3 text-slate-700">
                                <a href="{{ route('obras.show', $c->obra) }}" class="hover:text-blue-600 hover:underline">
                                    {{ Str::limit($c->obra->descricao, 50) }}
                                </a>
                            </td>

                            {{-- EMPRESA --}}
                            <td class="px-4 py-3 text-slate-700">
                                {{ $c->empresa->nomeExibicao() }}
                            </td>

                            {{-- VALOR --}}
                            <td class="px-4 py-3 text-slate-800 font-medium">
                                R$ {{ $c->valor_contrato ? number_format($c->valor_contrato, 2, ',', '.') : '—' }}
                            </td>

                            {{-- VIGÊNCIA --}}
                            <td class="px-4 py-3">
                                @if ($c->vigencia_contrato)
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-medium
                                        {{ $c->estaVencido()
                                            ? 'bg-red-100 text-red-700'
                                            : ($c->venceEm(30)
                                                ? 'bg-yellow-100 text-yellow-700'
                                                : 'bg-green-100 text-green-700') }}">
                                        {{ $c->vigencia_contrato->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            {{-- AÇÕES --}}
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">

                                    <a href="{{ route('contratos.show', $c) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                        👁 Ver
                                    </a>

                                    <a href="{{ route('contratos.edit', $c) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100">
                                        ✏️ Editar
                                    </a>

                                    <button type="button"
                                        @click="abrirModalDelete('{{ route('contratos.destroy', $c) }}')"
                                        class="px-3 py-1 text-xs rounded-lg border border-red-200 text-red-700 bg-red-50 hover:bg-red-100">
                                        🗑 Excluir
                                    </button>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-500">
                                Nenhum contrato encontrado.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

            <div class="p-4 border-t">
                {{ $contratos->links() }}
            </div>

        </div>

        {{-- =========================================================
        MODAL AJUDA
        ========================================================= --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">

                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Contratos</h3>
                    <p class="text-sm opacity-90">Como utilizar esta tela</p>
                </div>

                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p><strong>➕ Novo Contrato:</strong> Cadastra um contrato vinculado a uma obra e empresa.</p>
                    <p><strong>👁 Visualizar:</strong> Exibe todos os dados e execuções do contrato.</p>
                    <p><strong>✏️ Editar:</strong> Atualiza informações do contrato.</p>
                    <p><strong>🗑 Excluir:</strong> Remove o contrato definitivamente.</p>

                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-xs">
                        ⚠️ Para criar um contrato, a <strong>empresa</strong> deve estar previamente cadastrada.
                        Acesse <em>Administração → Empresas</em> ou utilize o botão "Nova Empresa" no formulário de
                        cadastro.
                    </div>

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

        {{-- =========================================================
        MODAL EXCLUSÃO
        ========================================================= --}}
        <div x-show="modalDelete" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md text-center">

                <h3 class="text-lg font-semibold text-red-600 mb-4">
                    Confirmar exclusão
                </h3>

                <p class="text-sm text-slate-600 mb-6">
                    Essa ação não poderá ser desfeita. Execuções vinculadas também serão removidas.
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

    <script>
        function contratoTable() {
            return {
                modalDelete: false,
                modalAjuda: false,
                url: '',

                abrirModalDelete(url) {
                    this.url = url;
                    this.modalDelete = true;
                }
            }
        }
    </script>

@endsection
