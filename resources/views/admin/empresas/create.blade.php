@extends('layouts.app')

@section('title', 'Nova Empresa')
@section('subtitle', 'Cadastro de empresa contratada')

@section('content')

    <div x-data="empresaForm()">

        {{-- BREADCRUMB --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('admin.empresas.index') }}" class="hover:text-slate-900">Empresas</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-slate-900 font-medium">Nova Empresa</span>
            </div>
            <button type="button" @click="modalAjuda = true"
                class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                ❓ Ajuda
            </button>
        </div>

        <div class="mb-6">
            <a href="{{ route('admin.empresas.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                   text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-100 transition">
                ← Voltar
            </a>
        </div>

        <div class="flex justify-center">
            <div class="w-full max-w-4xl">

                <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                    {{-- HEADER --}}
                    <div class="px-8 py-6 border-b border-slate-200">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center text-xl">
                                🏢
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">Dados da Empresa</h2>
                                <p class="text-sm text-slate-600 mt-1">Preencha as informações para cadastrar uma nova empresa</p>
                            </div>
                        </div>
                    </div>

                    {{-- ERROS --}}
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

                    <form method="POST" action="{{ route('admin.empresas.store') }}">
                        @csrf

                        <div class="px-8 py-6 space-y-6">

                            {{-- RAZÃO SOCIAL / FANTASIA --}}
                            <div>
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Identificação</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Razão Social *</label>
                                        <input type="text" name="razao_social"
                                            value="{{ old('razao_social') }}" required
                                            placeholder="Nome jurídico completo"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Nome Fantasia</label>
                                        <input type="text" name="nome_fantasia"
                                            value="{{ old('nome_fantasia') }}"
                                            placeholder="Nome comercial (opcional)"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">CNPJ *</label>
                                        <input type="text" name="cnpj"
                                            value="{{ old('cnpj') }}" required
                                            placeholder="00.000.000/0000-00"
                                            @input="mascaraCnpj($event)" maxlength="18"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 font-mono">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Responsável</label>
                                        <input type="text" name="responsavel"
                                            value="{{ old('responsavel') }}"
                                            placeholder="Nome do responsável / representante"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                </div>
                            </div>

                            {{-- CONTATO --}}
                            <div>
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Contato</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Telefone</label>
                                        <input type="text" name="telefone"
                                            value="{{ old('telefone') }}"
                                            placeholder="(11) 3333-4444"
                                            @input="mascaraTelefone($event)" maxlength="15"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">E-mail</label>
                                        <input type="email" name="email"
                                            value="{{ old('email') }}"
                                            placeholder="contato@empresa.com.br"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                </div>
                            </div>

                            {{-- ENDEREÇO --}}
                            <div>
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Endereço</h3>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Endereço Completo</label>
                                    <input type="text" name="endereco_completo"
                                        value="{{ old('endereco_completo') }}"
                                        placeholder="Rua, número, bairro, cidade — UF"
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                        </div>

                        {{-- FOOTER --}}
                        <div class="px-8 py-5 border-t bg-slate-50 rounded-b-xl flex justify-between">
                            <a href="{{ route('admin.empresas.index') }}"
                                class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-700 bg-white border">
                                ← Cancelar
                            </a>
                            <button type="submit"
                                class="px-6 py-2.5 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                ✔ Salvar Empresa
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL AJUDA --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Cadastro de Empresa</h3>
                    <p class="text-sm opacity-90">Como preencher o formulário</p>
                </div>
                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p><strong>Razão Social:</strong> Nome jurídico conforme CNPJ.</p>
                    <p><strong>Nome Fantasia:</strong> Nome comercial — será exibido nos contratos se preenchido.</p>
                    <p><strong>CNPJ:</strong> Obrigatório e único no sistema. Formato: 00.000.000/0000-00.</p>
                    <p><strong>Responsável:</strong> Nome do representante legal ou técnico da empresa.</p>
                    <div class="bg-slate-50 p-4 rounded text-xs">
                        💡 Após cadastrar, a empresa poderá ser selecionada ao criar contratos.
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

    <script>
        function empresaForm() {
            return {
                modalAjuda: false,

                mascaraCnpj(e) {
                    let v = e.target.value.replace(/\D/g, '').slice(0, 14);
                    if (v.length > 12) v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d+)/, '$1.$2.$3/$4-$5');
                    else if (v.length > 8) v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d+)/, '$1.$2.$3/$4');
                    else if (v.length > 5) v = v.replace(/^(\d{2})(\d{3})(\d+)/, '$1.$2.$3');
                    else if (v.length > 2) v = v.replace(/^(\d{2})(\d+)/, '$1.$2');
                    e.target.value = v;
                },

                mascaraTelefone(e) {
                    let v = e.target.value.replace(/\D/g, '').slice(0, 11);
                    if (v.length > 6) v = v.replace(/^(\d{2})(\d{4,5})(\d+)/, '($1) $2-$3');
                    else if (v.length > 2) v = v.replace(/^(\d{2})(\d+)/, '($1) $2');
                    else if (v.length > 0) v = v.replace(/^(\d+)/, '($1');
                    e.target.value = v;
                },
            }
        }
    </script>

@endsection