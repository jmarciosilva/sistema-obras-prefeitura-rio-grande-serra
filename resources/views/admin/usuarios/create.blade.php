@extends('layouts.app')

@section('title', 'Novo Usuário')
@section('subtitle', 'Cadastro de novos usuários')

@section('content')

    <div x-data="userForm()">

        {{-- =========================================================
    BREADCRUMB
    ========================================================= --}}
        <div class="flex items-center justify-between mb-6">

            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('admin.usuarios.index') }}" class="hover:text-slate-900">Usuários</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-slate-900 font-medium">Novo Usuário</span>
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

                {{-- =========================================================
            CARD PRINCIPAL
            ========================================================= --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                    {{-- HEADER --}}
                    <div class="px-8 py-6 border-b border-slate-200">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                                👤
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">Dados do Usuário</h2>
                                <p class="text-sm text-slate-600 mt-1">
                                    Preencha as informações abaixo para cadastrar um novo usuário
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- ERROS DE VALIDAÇÃO --}}
                    @if ($errors->any())
                        <div class="mx-8 mt-6 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg">
                            <strong>Erro ao salvar:</strong>
                            <ul class="mt-2 text-sm list-disc ml-6">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- FORMULÁRIO --}}
                    <form method="POST" action="{{ route('admin.usuarios.store') }}">
                        @csrf

                        <div class="px-8 py-6 space-y-6">

                            {{-- ========================
                        DADOS BÁSICOS
                        ======================== --}}
                            <div>
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">
                                    Dados Básicos
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Nome *</label>
                                        <input type="text" name="name" value="{{ old('name') }}" required
                                            placeholder="Nome completo"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Email *</label>
                                        <input type="email" name="email" value="{{ old('email') }}" required
                                            placeholder="email@prefeitura.sp.gov.br"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Senha *</label>
                                        <input type="password" name="password" required placeholder="Mínimo 6 caracteres"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Confirmar Senha *</label>
                                        <input type="password" name="password_confirmation" required
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                </div>
                            </div>

                            {{-- ========================
                        CONTATO
                        ======================== --}}
                            <div>
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">
                                    Informações de Contato
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                                    <input type="text" name="telefone" value="{{ old('telefone') }}"
                                        placeholder="(11) 4820-1234" @input="mascaraTelefone($event)" maxlength="14"
                                        class="px-4 py-2.5 rounded-lg border border-slate-300">

                                    <input type="text" name="ramal" value="{{ old('ramal') }}" placeholder="Ex: 123"
                                        @input="mascaraRamal($event)" maxlength="3"
                                        class="px-4 py-2.5 rounded-lg border border-slate-300">

                                    <input type="text" name="whatsapp" value="{{ old('whatsapp') }}"
                                        placeholder="(11) 91234-5678" @input="mascaraWhatsapp($event)" maxlength="15"
                                        class="px-4 py-2.5 rounded-lg border border-slate-300">

                                </div>
                            </div>

                            {{-- ========================
                        PERFIL
                        ======================== --}}
                            <div>
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">
                                    Perfil de Acesso
                                </h3>

                                <select name="perfil" class="w-full px-4 py-2.5 rounded-lg border border-slate-300">
                                    <option value="admin">Administrador</option>
                                    <option value="secretario">Secretário</option>
                                    <option value="operador">Operador</option>
                                    <option value="tecnico">Técnico</option>
                                </select>
                            </div>

                        </div>

                        {{-- FOOTER --}}
                        <div class="px-8 py-5 border-t bg-slate-50 rounded-b-xl flex justify-between">

                            <a href="{{ route('admin.usuarios.index') }}"
                                class="px-5 py-2.5 rounded-lg text-sm font-medium
                                  text-slate-700 bg-white border">
                                ← Cancelar
                            </a>

                            <button type="submit"
                                class="px-6 py-2.5 rounded-lg text-sm font-medium
                                       text-white bg-blue-600 hover:bg-blue-700">
                                ✔ Salvar Usuário
                            </button>

                        </div>

                    </form>

                </div>
            </div>
        </div>

        {{-- =========================================================
        MODAL AJUDA
        ========================================================= --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">

                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Cadastro de Usuário</h3>
                    <p class="text-sm opacity-90">Como preencher o formulário</p>
                </div>

                <div class="p-6 space-y-4 text-sm text-slate-700">

                    <p><strong>Nome:</strong> Nome completo do usuário.</p>
                    <p><strong>Email:</strong> Será utilizado para login.</p>
                    <p><strong>Senha:</strong> Mínimo de 6 caracteres.</p>
                    <p><strong>Perfil:</strong> Define o nível de acesso.</p>
                    <p><strong>Contato:</strong> Informações opcionais para comunicação.</p>

                    <div class="bg-slate-50 p-4 rounded text-xs">
                        💡 Após criar, o usuário já estará ativo.
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

    {{-- =========================================================
ALPINE
========================================================= --}}
    <script>
        function userForm() {
            return {
                modalAjuda: false,

                // =========================
                // 📞 TELEFONE FIXO
                // Formato: (00) 0000-0000
                // =========================
                mascaraTelefone(e) {
                    let v = e.target.value.replace(/\D/g, '').slice(0, 10);

                    if (v.length > 6) {
                        v = v.replace(/^(\d{2})(\d{4})(\d+)/, '($1) $2-$3');
                    } else if (v.length > 2) {
                        v = v.replace(/^(\d{2})(\d+)/, '($1) $2');
                    } else if (v.length > 0) {
                        v = v.replace(/^(\d+)/, '($1');
                    }

                    e.target.value = v;
                },

                // =========================
                // ☎️ RAMAL
                // Formato: 000
                // =========================
                mascaraRamal(e) {
                    let v = e.target.value.replace(/\D/g, '').slice(0, 3);
                    e.target.value = v;
                },

                // =========================
                // 📱 WHATSAPP
                // Formato: (00) 00000-0000
                // =========================
                mascaraWhatsapp(e) {
                    let v = e.target.value.replace(/\D/g, '').slice(0, 11);

                    if (v.length > 7) {
                        v = v.replace(/^(\d{2})(\d{5})(\d+)/, '($1) $2-$3');
                    } else if (v.length > 2) {
                        v = v.replace(/^(\d{2})(\d+)/, '($1) $2');
                    } else if (v.length > 0) {
                        v = v.replace(/^(\d+)/, '($1');
                    }

                    e.target.value = v;
                }
            }
        }
    </script>

@endsection
