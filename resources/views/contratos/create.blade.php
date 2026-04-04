@extends('layouts.app')

@section('title', 'Novo Contrato')
@section('subtitle', 'Cadastro de contrato de obra')

@section('content')

    <div x-data="contratoForm()">

        {{-- =========================================================
        BREADCRUMB + AJUDA
        ========================================================= --}}
        <div class="flex items-center justify-between mb-6">

            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('contratos.index') }}" class="hover:text-slate-900">Contratos</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-slate-900 font-medium">Novo Contrato</span>
            </div>

            <button type="button" @click="modalAjuda = true"
                class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                ❓ Ajuda
            </button>

        </div>

        {{-- BOTÃO VOLTAR --}}
        <div class="mb-6">
            <a href="{{ route('contratos.index') }}"
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
                            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-xl">
                                📋
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">Dados do Contrato</h2>
                                <p class="text-sm text-slate-600 mt-1">
                                    Preencha as informações para cadastrar um novo contrato
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

                    {{-- MENSAGEM DE SUCESSO AO CRIAR EMPRESA VIA MODAL --}}
                    @if (session('empresa_criada'))
                        <div
                            class="mx-8 mt-6 bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm">
                            ✅ {{ session('empresa_criada') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contratos.store') }}">
                        @csrf

                        <div class="px-8 py-6 space-y-6">

                            {{-- ========================
                            VÍNCULO
                            ======================== --}}
                            <div>
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">
                                    Vínculo da Obra e Empresa
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                    {{-- OBRA --}}
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Obra *</label>
                                        <select name="obra_id" required
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                            <option value="">— Selecione a obra —</option>
                                            @foreach ($obras as $obra)
                                                <option value="{{ $obra->id }}"
                                                    {{ old('obra_id', $obraSelecionada) == $obra->id ? 'selected' : '' }}>
                                                    {{ $obra->descricao }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- EMPRESA --}}
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <label class="text-sm font-medium">Empresa *</label>
                                            <button type="button" @click="modalEmpresa = true"
                                                class="text-xs text-blue-600 hover:underline">
                                                ➕ Nova Empresa
                                            </button>
                                        </div>
                                        <select name="empresa_id" required x-ref="selectEmpresa"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                            <option value="">— Selecione a empresa —</option>
                                            @foreach ($empresas as $empresa)
                                                <option value="{{ $empresa->id }}"
                                                    {{ old('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                                    {{ $empresa->nomeExibicao() }} — {{ $empresa->cnpj }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="text-xs text-slate-400">
                                            Empresa não listada? Clique em "Nova Empresa" acima.
                                        </p>
                                    </div>

                                </div>
                            </div>

                            {{-- ========================
                            IDENTIFICAÇÃO
                            ======================== --}}
                            <div>
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">
                                    Identificação do Contrato
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Número do Contrato / Ano</label>
                                        <input type="text" name="numero_contrato_ano"
                                            value="{{ old('numero_contrato_ano') }}" placeholder="Ex: 001/2024"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Processo de Licitação</label>
                                        <input type="text" name="processo_licitacao"
                                            value="{{ old('processo_licitacao') }}" placeholder="Ex: PE 012/2024"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                </div>
                            </div>

                            {{-- ========================
                            DATAS
                            ======================== --}}
                            <div>
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">
                                    Datas
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Data de Assinatura</label>
                                        <input type="date" name="data_assinatura" value="{{ old('data_assinatura') }}"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Ordem de Início</label>
                                        <input type="date" name="ordem_inicio" value="{{ old('ordem_inicio') }}"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Vigência do Contrato</label>
                                        <input type="date" name="vigencia_contrato"
                                            value="{{ old('vigencia_contrato') }}"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                </div>
                            </div>

                            {{-- ========================
                            VALOR
                            ======================== --}}
                            <div>
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">
                                    Valor Contratual
                                </h3>

                                <div class="max-w-sm space-y-2">
                                    <label class="text-sm font-medium">Valor do Contrato (R$)</label>

                                    {{-- Campo visual com máscara — NÃO tem name, não é enviado --}}
                                    <div class="relative">
                                        <span
                                            class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm select-none">R$</span>
                                        <input type="text" id="valor_display" x-ref="valorDisplay" placeholder="0,00"
                                            maxlength="16" @input="mascaraMoeda($event)"
                                            value="{{ old('valor_contrato') ? number_format((float) old('valor_contrato'), 2, ',', '.') : '' }}"
                                            class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 text-right tabular-nums">
                                    </div>

                                    {{-- Campo oculto que envia o valor decimal limpo ao Laravel --}}
                                    <input type="hidden" name="valor_contrato" x-ref="valorHidden"
                                        value="{{ old('valor_contrato') }}">

                                    <p class="text-xs text-slate-400">Suporta até R$ 999.999.999,99</p>
                                </div>
                            </div>

                        </div>

                        {{-- FOOTER --}}
                        <div class="px-8 py-5 border-t bg-slate-50 rounded-b-xl flex justify-between">

                            <a href="{{ route('contratos.index') }}"
                                class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-700 bg-white border">
                                ← Cancelar
                            </a>

                            <button type="submit"
                                class="px-6 py-2.5 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                ✔ Salvar Contrato
                            </button>

                        </div>

                    </form>

                </div>
            </div>
        </div>

        {{-- =========================================================
        MODAL — NOVA EMPRESA (RÁPIDO)
        ========================================================= --}}
        <div x-show="modalEmpresa" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">

                <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">🏢 Nova Empresa</h3>
                    <p class="text-sm opacity-90">Cadastro rápido — dados essenciais</p>
                </div>

                <form method="POST" action="{{ route('admin.empresas.store') }}">
                    @csrf
                    {{-- Redireciona de volta para o create do contrato preservando obra_id --}}
                    <input type="hidden" name="_redirect_back"
                        value="{{ route('contratos.create', ['obra_id' => $obraSelecionada]) }}">

                    <div class="p-6 space-y-4">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <div class="space-y-1">
                                <label class="text-sm font-medium">Razão Social *</label>
                                <input type="text" name="razao_social" required placeholder="Nome jurídico completo"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-green-500">
                            </div>

                            <div class="space-y-1">
                                <label class="text-sm font-medium">Nome Fantasia</label>
                                <input type="text" name="nome_fantasia" placeholder="Nome comercial (opcional)"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-green-500">
                            </div>

                            <div class="space-y-1">
                                <label class="text-sm font-medium">CNPJ *</label>
                                <input type="text" name="cnpj" required placeholder="00.000.000/0000-00"
                                    @input="mascaraCnpj($event)" maxlength="18"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-green-500">
                            </div>

                            <div class="space-y-1">
                                <label class="text-sm font-medium">Responsável</label>
                                <input type="text" name="responsavel" placeholder="Nome do responsável"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-green-500">
                            </div>

                            <div class="space-y-1">
                                <label class="text-sm font-medium">Telefone</label>
                                <input type="text" name="telefone" placeholder="(11) 3333-4444"
                                    @input="mascaraTelefone($event)" maxlength="15"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                            </div>

                            <div class="space-y-1">
                                <label class="text-sm font-medium">E-mail</label>
                                <input type="email" name="email" placeholder="contato@empresa.com.br"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                            </div>

                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium">Endereço Completo</label>
                            <input type="text" name="endereco_completo" placeholder="Rua, número, bairro, cidade — SP"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-700">
                            💡 Após salvar, a empresa será adicionada automaticamente à lista e você poderá selecioná-la.
                        </div>

                    </div>

                    <div class="px-6 py-4 border-t bg-slate-50 flex justify-between">
                        <button type="button" @click="modalEmpresa = false"
                            class="px-4 py-2 text-sm border rounded-lg text-slate-600 hover:bg-slate-100">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-5 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700">
                            ✔ Salvar Empresa
                        </button>
                    </div>

                </form>

            </div>
        </div>

        {{-- =========================================================
        MODAL AJUDA
        ========================================================= --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">

                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Cadastro de Contrato</h3>
                    <p class="text-sm opacity-90">Como preencher o formulário</p>
                </div>

                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p><strong>Obra:</strong> Selecione a obra à qual o contrato pertence.</p>
                    <p><strong>Empresa:</strong> Contratada responsável pela execução. Se não estiver listada, clique em
                        "Nova Empresa".</p>
                    <p><strong>Número do Contrato:</strong> Identificador oficial, ex: <em>001/2024</em>.</p>
                    <p><strong>Processo de Licitação:</strong> Referência ao pregão ou concorrência, ex: <em>PE
                            012/2024</em>.</p>
                    <p><strong>Datas:</strong> Assinatura, início das obras e prazo de vigência.</p>
                    <p><strong>Valor:</strong> Valor total contratado em reais.</p>

                    <div class="bg-slate-50 p-4 rounded text-xs">
                        💡 Após criar o contrato, você poderá registrar medições (execuções) na aba de Execuções da obra.
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
        function contratoForm() {
            return {
                modalAjuda: false,
                modalEmpresa: false,

                // ─────────────────────────────────────────────────────────
                // Máscara pt-BR para valor monetário.
                // Limita a 13 dígitos (999.999.999,99) usando BigInt para
                // evitar perda de precisão em valores acima de 2^53.
                // Ao mesmo tempo, escreve o valor decimal limpo no hidden.
                // ─────────────────────────────────────────────────────────
                mascaraMoeda(e) {
                    // Remove tudo que não for dígito e limita a 13 chars numéricos
                    let digits = e.target.value.replace(/\D/g, '').slice(0, 13);

                    if (digits === '' || digits === '0') {
                        e.target.value = '';
                        this.$refs.valorHidden.value = '';
                        return;
                    }

                    // Trabalha como centavos usando BigInt para inteiros grandes
                    const centavos = BigInt(digits);
                    const reais = centavos / 100n;
                    const cents = centavos % 100n;

                    // Formata a parte inteira com pontos de milhar
                    const intFormatado = reais.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    const centsFormatado = cents.toString().padStart(2, '0');

                    e.target.value = `${intFormatado},${centsFormatado}`;

                    // Valor decimal limpo para o campo hidden (ex: 123456789.99)
                    this.$refs.valorHidden.value = `${reais}.${centsFormatado}`;
                },

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
