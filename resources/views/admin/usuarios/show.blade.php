@extends('layouts.app')

@section('title', 'Visualizar Usuário')
@section('subtitle', 'Detalhes do usuário')

@section('content')

    <div x-data="{ modalAjuda: false }">

        {{-- =========================================================
    BREADCRUMB + AJUDA
    ========================================================= --}}
        <div class="flex items-center justify-between mb-6">

            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('admin.usuarios.index') }}" class="hover:text-slate-900">Usuários</a>
                <span>›</span>
                <span class="text-slate-900 font-medium">Visualizar</span>
            </div>

            {{-- BOTÃO AJUDA --}}
            <button type="button" @click="modalAjuda = true"
                class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                ❓ Ajuda
            </button>

        </div>

        {{-- BOTÃO VOLTAR --}}
        <div class="mb-6">
            <a href="{{ route('admin.usuarios.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                   text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-100 transition">
                ← Voltar
            </a>
        </div>

        <div class="flex justify-center">
            <div class="w-full max-w-4xl">

                {{-- CARD --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                    {{-- HEADER --}}
                    <div class="px-8 py-6 border-b border-slate-200">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-2xl">
                                👤
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">
                                    {{ $usuario->name }}
                                </h2>
                                <p class="text-sm text-slate-600 mt-1">
                                    {{ $usuario->email }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- DADOS --}}
                    <div class="px-8 py-6 space-y-6">

                        {{-- PERFIL --}}
                        <div>
                            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-2">
                                Perfil de Acesso
                            </h3>

                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            {{ $usuario->perfil === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $usuario->labelPerfil() }}
                            </span>
                        </div>

                        {{-- STATUS --}}
                        <div>
                            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-2">
                                Status do Usuário
                            </h3>

                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            {{ $usuario->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $usuario->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>

                        {{-- CONTATO --}}
                        <div>
                            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">
                                Informações de Contato
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">

                                <div>
                                    <p class="text-slate-500">Telefone</p>
                                    <p class="font-medium">
                                        {{ $usuario->telefone ?? '—' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-slate-500">Ramal</p>
                                    <p class="font-medium">
                                        {{ $usuario->ramal ?? '—' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-slate-500">WhatsApp</p>
                                    <p class="font-medium">
                                        {{ $usuario->whatsapp ?? '—' }}
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
                                    <p class="text-slate-500">Criado em</p>
                                    <p class="font-medium text-slate-800">
                                        {{ $usuario->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-slate-500">Última atualização</p>
                                    <p class="font-medium text-slate-800">
                                        {{ $usuario->updated_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>

                            </div>
                        </div>

                    </div>

                    {{-- AÇÕES --}}
                    <div class="px-8 py-5 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-end gap-2">

                        <a href="{{ route('admin.usuarios.edit', $usuario) }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium
                               text-white bg-blue-600 hover:bg-blue-700 transition">
                            ✏️ Editar Usuário
                        </a>

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
                    <h3 class="text-lg font-semibold">📘 Ajuda — Visualização de Usuário</h3>
                    <p class="text-sm opacity-90">Entenda os dados exibidos</p>
                </div>

                <div class="p-6 space-y-4 text-sm text-slate-700">

                    <p><strong>Perfil:</strong> Define o nível de acesso do usuário no sistema.</p>
                    <p><strong>Status:</strong> Usuários inativos não conseguem acessar o sistema.</p>
                    <p><strong>Contato:</strong> Informações para comunicação interna.</p>
                    <p><strong>Datas:</strong> Controle de criação e atualização do cadastro.</p>

                    <div class="bg-slate-50 p-4 rounded text-xs">
                        💡 Utilize o botão "Editar" para atualizar informações.
                    </div>

                </div>

                <div class="px-6 py-4 border-t flex justify-end">
                    <button @click="modalAjuda = false" class="px-5 py-2 bg-blue-600 text-white rounded-lg">
                        Entendi 👍
                    </button>
                </div>

            </div>
        </div>

    </div>

@endsection
