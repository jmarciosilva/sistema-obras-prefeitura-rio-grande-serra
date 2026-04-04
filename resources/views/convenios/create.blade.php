@extends('layouts.app')

@section('title', 'Novo Convênio')
@section('subtitle', 'Cadastro de convênio')

@section('content')

    <div x-data="convenioForm()">

        {{-- BREADCRUMB --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('convenios.index') }}" class="hover:text-slate-900">Convênios</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-slate-900 font-medium">Novo Convênio</span>
            </div>
            <button type="button" @click="modalAjuda = true"
                class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                ❓ Ajuda
            </button>
        </div>

        <div class="mb-6">
            <a href="{{ route('convenios.index') }}"
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
                            <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center text-xl">
                                🤝
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">Dados do Convênio</h2>
                                <p class="text-sm text-slate-600 mt-1">Preencha as informações para cadastrar um novo convênio</p>
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

                    <form method="POST" action="{{ route('convenios.store') }}">
                        @csrf

                        <div class="px-8 py-6 space-y-6">

                            {{-- CLASSIFICAÇÃO --}}
                            <div>
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Classificação</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Categoria *</label>
                                        <select name="categoria_convenio_id" required
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                            <option value="">— Selecione —</option>
                                            @foreach ($categorias as $cat)
                                                <option value="{{ $cat->id }}"
                                                    {{ old('categoria_convenio_id') == $cat->id ? 'selected' : '' }}>
                                                    {{ $cat->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Órgão Financiador *</label>
                                        <select name="orgao_financiador_id" required
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                            <option value="">— Selecione —</option>
                                            @foreach ($orgaos as $o)
                                                <option value="{{ $o->id }}"
                                                    {{ old('orgao_financiador_id') == $o->id ? 'selected' : '' }}>
                                                    {{ $o->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>
                            </div>

                            {{-- DESCRIÇÃO --}}
                            <div>
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Descrição</h3>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Objeto / Descrição *</label>
                                    <textarea name="descricao" rows="3" required
                                        placeholder="Descreva o objeto do convênio..."
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 resize-none">{{ old('descricao') }}</textarea>
                                </div>
                            </div>

                            {{-- IDENTIFICAÇÃO --}}
                            <div>
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Identificação</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Número do Convênio / Ano</label>
                                        <input type="text" name="numero_convenio_ano"
                                            value="{{ old('numero_convenio_ano') }}"
                                            placeholder="Ex: 001/2024"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Processo Prefeitura / Concedente</label>
                                        <input type="text" name="processo_pref_concedente"
                                            value="{{ old('processo_pref_concedente') }}"
                                            placeholder="Ex: ADM 045/2024"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Cadastro PRESCON</label>
                                        <input type="text" name="cadastro_prescon_num"
                                            value="{{ old('cadastro_prescon_num') }}"
                                            placeholder="Número no PRESCON"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                </div>
                            </div>

                            {{-- DATAS --}}
                            <div>
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Datas</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Data de Assinatura</label>
                                        <input type="date" name="assinatura"
                                            value="{{ old('assinatura') }}"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Vigência</label>
                                        <input type="date" name="vigencia"
                                            value="{{ old('vigencia') }}"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>

                                </div>
                            </div>

                            {{-- VALOR --}}
                            <div>
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Valor</h3>
                                <div class="max-w-sm space-y-2">
                                    <label class="text-sm font-medium">Valor Repasse / Contrapartida (R$)</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm select-none">R$</span>
                                        <input type="text"
                                            x-ref="valorDisplay"
                                            placeholder="0,00"
                                            maxlength="16"
                                            @input="mascaraMoeda($event)"
                                            value="{{ old('valor_repasse_contrapartida') ? number_format((float) old('valor_repasse_contrapartida'), 2, ',', '.') : '' }}"
                                            class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 text-right tabular-nums">
                                    </div>
                                    <input type="hidden" name="valor_repasse_contrapartida" x-ref="valorHidden"
                                        value="{{ old('valor_repasse_contrapartida') }}">
                                    <p class="text-xs text-slate-400">Suporta até R$ 999.999.999,99</p>
                                </div>
                            </div>

                        </div>

                        {{-- FOOTER --}}
                        <div class="px-8 py-5 border-t bg-slate-50 rounded-b-xl flex justify-between">
                            <a href="{{ route('convenios.index') }}"
                                class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-700 bg-white border">
                                ← Cancelar
                            </a>
                            <button type="submit"
                                class="px-6 py-2.5 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                ✔ Salvar Convênio
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
                    <h3 class="text-lg font-semibold">📘 Ajuda — Cadastro de Convênio</h3>
                    <p class="text-sm opacity-90">Como preencher o formulário</p>
                </div>
                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p><strong>Categoria:</strong> Tipo do convênio (ex: Pavimentação, Saneamento).</p>
                    <p><strong>Órgão Financiador:</strong> Entidade que repassa os recursos (ex: Governo do Estado, FNDE).</p>
                    <p><strong>Descrição:</strong> Objeto detalhado do convênio.</p>
                    <p><strong>PRESCON:</strong> Número de cadastro no sistema estadual de convênios, se houver.</p>
                    <p><strong>Valor:</strong> Total do repasse incluindo contrapartida municipal.</p>
                    <div class="bg-slate-50 p-4 rounded text-xs">
                        💡 Após cadastrar o convênio, vincule-o às obras na tela de cada obra.
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
        function convenioForm() {
            return {
                modalAjuda: false,

                mascaraMoeda(e) {
                    let digits = e.target.value.replace(/\D/g, '').slice(0, 13);
                    if (digits === '' || digits === '0') {
                        e.target.value = '';
                        this.$refs.valorHidden.value = '';
                        return;
                    }
                    const centavos       = BigInt(digits);
                    const reais          = centavos / 100n;
                    const cents          = centavos % 100n;
                    const intFormatado   = reais.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    const centsFormatado = cents.toString().padStart(2, '0');
                    e.target.value = `${intFormatado},${centsFormatado}`;
                    this.$refs.valorHidden.value = `${reais}.${centsFormatado}`;
                },
            }
        }
    </script>

@endsection