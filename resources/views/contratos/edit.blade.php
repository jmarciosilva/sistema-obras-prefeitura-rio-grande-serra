@extends('layouts.app')

@section('title', 'Editar Contrato')
@section('subtitle', 'Atualização de dados do contrato')

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
                <a href="{{ route('contratos.show', $contrato) }}" class="hover:text-slate-900">
                    {{ $contrato->numero_contrato_ano ?? 'Contrato #' . $contrato->id }}
                </a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-slate-900 font-medium">Editar</span>
            </div>

            <button type="button" @click="modalAjuda = true"
                class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                ❓ Ajuda
            </button>

        </div>

        {{-- BOTÃO VOLTAR --}}
        <div class="mb-6">
            <a href="{{ route('contratos.show', $contrato) }}"
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
                                ✏️
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">Editar Contrato</h2>
                                <p class="text-sm text-slate-600 mt-1">
                                    Atualize as informações do contrato
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- ALERTA VENCIMENTO --}}
                    @if ($contrato->estaVencido())
                        <div class="mx-8 mt-6 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">
                            ⚠️ Este contrato está com a <strong>vigência vencida</strong>. Verifique se há necessidade de
                            aditivo.
                        </div>
                    @elseif ($contrato->venceEm(30))
                        <div
                            class="mx-8 mt-6 bg-yellow-50 border border-yellow-300 text-yellow-700 px-4 py-3 rounded-lg text-sm">
                            ⚠️ Este contrato vence em menos de <strong>30 dias</strong>.
                        </div>
                    @endif

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

                    <form method="POST" action="{{ route('contratos.update', $contrato) }}">
                        @csrf
                        @method('PUT')

                        <div class="px-8 py-6 space-y-6">

                            {{-- ========================
                            VÍNCULO
                            ======================== --}}
                            <div>
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">
                                    Vínculo da Obra e Empresa
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Obra *</label>
                                        <select name="obra_id" required
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                            <option value="">— Selecione a obra —</option>
                                            @foreach ($obras as $obra)
                                                <option value="{{ $obra->id }}"
                                                    {{ old('obra_id', $contrato->obra_id) == $obra->id ? 'selected' : '' }}>
                                                    {{ $obra->descricao }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Empresa *</label>
                                        <select name="empresa_id" required
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                            <option value="">— Selecione a empresa —</option>
                                            @foreach ($empresas as $empresa)
                                                <option value="{{ $empresa->id }}"
                                                    {{ old('empresa_id', $contrato->empresa_id) == $empresa->id ? 'selected' : '' }}>
                                                    {{ $empresa->nomeExibicao() }} — {{ $empresa->cnpj }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="text-xs text-slate-400">
                                            Para cadastrar nova empresa acesse
                                            <a href="{{ route('admin.empresas.create') }}" target="_blank"
                                                class="text-blue-600 hover:underline">Administração → Empresas</a>.
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
                                            value="{{ old('numero_contrato_ano', $contrato->numero_contrato_ano) }}"
                                            placeholder="Ex: 001/2024"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Processo de Licitação</label>
                                        <input type="text" name="processo_licitacao"
                                            value="{{ old('processo_licitacao', $contrato->processo_licitacao) }}"
                                            placeholder="Ex: PE 012/2024"
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
                                        <input type="date" name="data_assinatura"
                                            value="{{ old('data_assinatura', $contrato->data_assinatura?->format('Y-m-d')) }}"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Ordem de Início</label>
                                        <input type="date" name="ordem_inicio"
                                            value="{{ old('ordem_inicio', $contrato->ordem_inicio?->format('Y-m-d')) }}"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Vigência do Contrato</label>
                                        <input type="date" name="vigencia_contrato"
                                            value="{{ old('vigencia_contrato', $contrato->vigencia_contrato?->format('Y-m-d')) }}"
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

                                    <div class="relative">
                                        <span
                                            class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm select-none">R$</span>
                                        <input type="text" id="valor_display" x-ref="valorDisplay" placeholder="0,00"
                                            maxlength="16" @input="mascaraMoeda($event)"
                                            value="{{ old('valor_contrato', $contrato->valor_contrato ? number_format((float) $contrato->valor_contrato, 2, ',', '.') : '') }}"
                                            class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 text-right tabular-nums">
                                    </div>

                                    <input type="hidden" name="valor_contrato" x-ref="valorHidden"
                                        value="{{ old('valor_contrato', $contrato->valor_contrato ? number_format((float) $contrato->valor_contrato, 2, '.', '') : '') }}">

                                    <p class="text-xs text-slate-400">Suporta até R$ 999.999.999,99</p>
                                </div>
                            </div>

                        </div>

                        {{-- FOOTER --}}
                        <div class="px-8 py-5 border-t bg-slate-50 rounded-b-xl flex justify-between">

                            <a href="{{ route('contratos.show', $contrato) }}"
                                class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-700 bg-white border">
                                ← Cancelar
                            </a>

                            <button type="submit"
                                class="px-6 py-2.5 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                ✔ Atualizar Contrato
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
                    <h3 class="text-lg font-semibold">📘 Ajuda — Edição de Contrato</h3>
                    <p class="text-sm opacity-90">Como atualizar o contrato</p>
                </div>

                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p>Atualize os dados conforme necessário. Todos os campos são opcionais, exceto <strong>Obra</strong> e
                        <strong>Empresa</strong>.</p>
                    <p>Para trocar a empresa, selecione outra no campo correspondente. Para cadastrar nova empresa, acesse o
                        menu de Administração.</p>

                    <div class="bg-slate-50 p-4 rounded text-xs">
                        💡 Alterações no valor do contrato não afetam as medições já registradas.
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

                mascaraMoeda(e) {
                    let digits = e.target.value.replace(/\D/g, '').slice(0, 13);

                    if (digits === '' || digits === '0') {
                        e.target.value = '';
                        this.$refs.valorHidden.value = '';
                        return;
                    }

                    const centavos = BigInt(digits);
                    const reais = centavos / 100n;
                    const cents = centavos % 100n;

                    const intFormatado = reais.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    const centsFormatado = cents.toString().padStart(2, '0');

                    e.target.value = `${intFormatado},${centsFormatado}`;
                    this.$refs.valorHidden.value = `${reais}.${centsFormatado}`;
                },
            }
        }
    </script>

@endsection
