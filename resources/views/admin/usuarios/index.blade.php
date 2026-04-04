@extends('layouts.app')

@section('title', 'Usuários')
@section('subtitle', 'Gestão de usuários, estrutura organizacional e permissões')

@section('content')

    <div class="space-y-6" x-data="userTable()">

        {{-- =========================================================
    HEADER DA PÁGINA
    ========================================================= --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">Usuários</h1>
                <p class="text-sm text-slate-500">
                    Gerencie usuários e permissões do sistema.
                </p>
            </div>

            {{-- AÇÕES DO HEADER --}}
            <div class="flex gap-2">

                {{-- BOTÃO DE AJUDA --}}
                <button type="button" @click="modalAjuda = true"
                    class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                    ❓ Ajuda
                </button>

                {{-- BOTÃO NOVO USUÁRIO --}}
                <a href="{{ route('admin.usuarios.create') }}"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    ➕ Novo Usuário
                </a>

            </div>
        </div>

        {{-- =========================================================
    FORMULÁRIO DE BUSCA
    ========================================================= --}}
        <form method="GET" class="bg-white rounded-xl shadow p-4 flex gap-3">

            <input type="text" name="busca" value="{{ request('busca') }}"
                placeholder="Digite nome, email ou perfil..." class="flex-1 border rounded-lg px-4 py-2 text-sm">

            <button class="bg-blue-600 text-white px-5 rounded-lg text-sm">
                Buscar
            </button>

        </form>

        {{-- =========================================================
    TABELA DE USUÁRIOS
    ========================================================= --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">

            <table class="w-full text-sm">

                {{-- CABEÇALHO --}}
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left">Nome</th>
                        <th>Email</th>
                        <th>Contato</th>
                        <th>Perfil</th>
                        <th>Status</th>
                        <th class="text-right px-4">Ações</th>
                    </tr>
                </thead>

                {{-- CORPO --}}
                <tbody class="divide-y">

                    @forelse($usuarios as $u)
                        <tr class="hover:bg-slate-50">

                            {{-- NOME --}}
                            <td class="px-4 py-3 font-medium text-slate-800">
                                {{ $u->name }}
                            </td>

                            {{-- EMAIL --}}
                            <td class="text-slate-600">
                                {{ $u->email }}
                            </td>

                            {{-- CONTATO (telefone, ramal, whatsapp) --}}
                            <td class="text-xs text-slate-600">
                                <div class="flex flex-col gap-0.5">

                                    <span>📞 {{ $u->telefone ?? '—' }}</span>
                                    <span>☎️ Ramal: {{ $u->ramal ?? '—' }}</span>
                                    <span>📱 {{ $u->whatsapp ?? '—' }}</span>

                                </div>
                            </td>

                            {{-- PERFIL --}}
                            <td>
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $u->perfil == 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $u->labelPerfil() }}
                                </span>
                            </td>

                            {{-- STATUS --}}
                            <td>
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $u->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $u->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>

                            {{-- AÇÕES --}}
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">

                                    {{-- VISUALIZAR --}}
                                    <a href="{{ route('admin.usuarios.show', $u) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                        👁 Ver
                                    </a>

                                    {{-- EDITAR --}}
                                    <a href="{{ route('admin.usuarios.edit', $u) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100">
                                        ✏️ Editar
                                    </a>

                                    {{-- ATIVAR / INATIVAR --}}
                                    <button type="button"
                                        @click="abrirModalToggle('{{ route('admin.usuarios.toggle-ativo', $u) }}')"
                                        class="px-3 py-1 text-xs rounded-lg border
                                    {{ $u->ativo
                                        ? 'border-yellow-200 text-yellow-700 bg-yellow-50 hover:bg-yellow-100'
                                        : 'border-green-200 text-green-700 bg-green-50 hover:bg-green-100' }}">
                                        {{ $u->ativo ? '⛔ Inativar' : '✔ Ativar' }}
                                    </button>

                                    {{-- EXCLUIR --}}
                                    <button type="button"
                                        @click="abrirModalDelete('{{ route('admin.usuarios.destroy', $u) }}')"
                                        class="px-3 py-1 text-xs rounded-lg border border-red-200 text-red-700 bg-red-50 hover:bg-red-100">
                                        🗑 Excluir
                                    </button>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-500">
                                Nenhum usuário encontrado.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

            {{-- PAGINAÇÃO --}}
            <div class="p-4 border-t">
                {{ $usuarios->links() }}
            </div>

        </div>

        {{-- =========================================================
    MODAL AJUDA
    ========================================================= --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">

                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Usuários</h3>
                    <p class="text-sm opacity-90">Como utilizar esta tela</p>
                </div>

                <div class="p-6 space-y-4 text-sm text-slate-700">

                    <p><strong>➕ Novo Usuário:</strong> Cadastra novos acessos.</p>
                    <p><strong>👁 Visualizar:</strong> Exibe os dados completos.</p>
                    <p><strong>✏️ Editar:</strong> Atualiza informações.</p>
                    <p><strong>⛔ Ativar/Inativar:</strong> Controla acesso ao sistema.</p>
                    <p><strong>🗑 Excluir:</strong> Remove definitivamente.</p>

                    <div class="mt-4 p-4 bg-slate-50 rounded-lg text-xs">
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
    MODAL TOGGLE STATUS
    ========================================================= --}}
        <div x-show="modalToggle" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center">
            <div class="bg-white rounded-xl p-6 w-full max-w-md text-center">

                <h3 class="text-lg font-semibold mb-4">Confirmar ação</h3>

                <p class="text-sm text-slate-600 mb-6">
                    Deseja alterar o status do usuário?
                </p>

                <div class="flex justify-center gap-3">
                    <button @click="modalToggle = false" class="px-4 py-2 border rounded">
                        Cancelar
                    </button>

                    <form :action="url" method="POST">
                        @csrf
                        @method('PATCH')
                        <button class="px-5 py-2 bg-blue-600 text-white rounded">
                            Confirmar
                        </button>
                    </form>
                </div>

            </div>
        </div>

        {{-- =========================================================
    MODAL EXCLUSÃO
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
ALPINE (CONTROLE DOS MODAIS)
========================================================= --}}
    <script>
        function userTable() {
            return {
                modalToggle: false,
                modalDelete: false,
                modalAjuda: false,
                url: '',

                abrirModalToggle(url) {
                    this.url = url;
                    this.modalToggle = true;
                },

                abrirModalDelete(url) {
                    this.url = url;
                    this.modalDelete = true;
                }
            }
        }
    </script>

@endsection
