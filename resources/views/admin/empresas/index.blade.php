@extends('layouts.app')

@section('title', 'Empresas')
@section('subtitle', 'Gestão de empresas contratadas')

@section('content')

    <div class="space-y-6" x-data="empresaTable()">

        {{-- HEADER --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">Empresas</h1>
                <p class="text-sm text-slate-500">Gerencie as empresas contratadas para obras municipais.</p>
            </div>
            <div class="flex gap-2">
                <button type="button" @click="modalAjuda = true"
                    class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                    ❓ Ajuda
                </button>
                <a href="{{ route('admin.empresas.create') }}"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    ➕ Nova Empresa
                </a>
            </div>
        </div>

        {{-- BUSCA --}}
        <form method="GET" class="bg-white rounded-xl shadow p-4 flex gap-3">
            <input type="text" name="busca" value="{{ request('busca') }}"
                placeholder="Razão social, nome fantasia ou CNPJ..."
                class="flex-1 border rounded-lg px-4 py-2 text-sm">
            <button class="bg-blue-600 text-white px-5 rounded-lg text-sm">Buscar</button>
        </form>

        {{-- FEEDBACK --}}
        @if (session('sucesso'))
            <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm">
                ✅ {{ session('sucesso') }}
            </div>
        @endif

        @if ($errors->has('geral'))
            <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">
                ⚠️ {{ $errors->first('geral') }}
            </div>
        @endif

        {{-- TABELA --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">

            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left">Empresa</th>
                        <th class="px-4 py-3 text-left">CNPJ</th>
                        <th class="px-4 py-3 text-left">Responsável</th>
                        <th class="px-4 py-3 text-left">Contato</th>
                        <th class="px-4 py-3 text-center">Contratos</th>
                        <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($empresas as $e)
                        <tr class="hover:bg-slate-50">

                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">{{ $e->nomeExibicao() }}</div>
                                @if ($e->nome_fantasia && $e->nome_fantasia !== $e->razao_social)
                                    <div class="text-xs text-slate-500">{{ $e->razao_social }}</div>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-slate-600 font-mono text-xs">
                                {{ $e->cnpj }}
                            </td>

                            <td class="px-4 py-3 text-slate-600">
                                {{ $e->responsavel ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-xs text-slate-600">
                                <div class="flex flex-col gap-0.5">
                                    <span>📞 {{ $e->telefone ?? '—' }}</span>
                                    @if ($e->email)
                                        <span>✉️ {{ $e->email }}</span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-semibold
                                    {{ $e->contratos_count > 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $e->contratos_count }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.empresas.show', $e) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                        👁 Ver
                                    </a>
                                    <a href="{{ route('admin.empresas.edit', $e) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100">
                                        ✏️ Editar
                                    </a>
                                    <button type="button"
                                        @click="abrirModalDelete('{{ route('admin.empresas.destroy', $e) }}', {{ $e->contratos_count }})"
                                        class="px-3 py-1 text-xs rounded-lg border border-red-200 text-red-700 bg-red-50 hover:bg-red-100">
                                        🗑 Excluir
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-500">
                                Nenhuma empresa encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="p-4 border-t">
                {{ $empresas->links() }}
            </div>

        </div>

        {{-- MODAL AJUDA --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Empresas</h3>
                    <p class="text-sm opacity-90">Como utilizar esta tela</p>
                </div>
                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p><strong>➕ Nova Empresa:</strong> Cadastra uma nova empresa contratada.</p>
                    <p><strong>👁 Visualizar:</strong> Exibe dados completos e contratos da empresa.</p>
                    <p><strong>✏️ Editar:</strong> Atualiza informações cadastrais.</p>
                    <p><strong>🗑 Excluir:</strong> Remove a empresa. Não é possível excluir empresas com contratos vinculados.</p>
                    <div class="bg-slate-50 p-4 rounded text-xs">
                        💡 O número em azul na coluna "Contratos" indica quantos contratos a empresa possui no sistema.
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

                <template x-if="temContratos">
                    <div class="mb-4 bg-yellow-50 border border-yellow-300 text-yellow-700 px-4 py-3 rounded-lg text-sm text-left">
                        ⚠️ Esta empresa possui contratos vinculados e <strong>não pode ser excluída</strong>.
                        Remova os contratos antes de prosseguir.
                    </div>
                </template>

                <template x-if="!temContratos">
                    <p class="text-sm text-slate-600 mb-6">Essa ação não poderá ser desfeita.</p>
                </template>

                <div class="flex justify-center gap-3">
                    <button @click="modalDelete = false" class="px-4 py-2 border rounded">Cancelar</button>

                    <template x-if="!temContratos">
                        <form :action="url" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="px-5 py-2 bg-red-600 text-white rounded">Excluir</button>
                        </form>
                    </template>
                </div>
            </div>
        </div>

    </div>

    <script>
        function empresaTable() {
            return {
                modalDelete: false,
                modalAjuda: false,
                temContratos: false,
                url: '',

                abrirModalDelete(url, qtdContratos) {
                    this.url = url;
                    this.temContratos = qtdContratos > 0;
                    this.modalDelete = true;
                }
            }
        }
    </script>

@endsection