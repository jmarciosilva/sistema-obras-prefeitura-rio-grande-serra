@php
    $processo = $processo ?? null;
@endphp

<div class="px-8 py-6 space-y-6">

    {{-- ========================
    IDENTIFICAÇÃO
    ======================== --}}
    <div>
        <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">
            Identificação
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="space-y-2">
                <label class="text-sm font-medium">Nº do Processo *</label>
                <input type="text" name="processo_numero"
                    value="{{ old('processo_numero', $processo->processo_numero ?? '') }}" required
                    placeholder="Ex: 1831/2019-5"
                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium">Requerente *</label>
                <input type="text" name="requerente" value="{{ old('requerente', $processo->requerente ?? '') }}"
                    required placeholder="Nome do requerente"
                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
            </div>

        </div>

        {{-- CEP + ENDEREÇO --}}
        <div class="mt-6 space-y-4">

            <div class="max-w-xs space-y-2">
                <label class="text-sm font-medium">CEP</label>
                <div class="relative">
                    <input type="text" name="cep" x-model="endereco.cep" @blur="buscarCep()" maxlength="9"
                        placeholder="00000-000"
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                    <span x-show="buscandoCep" x-cloak
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">
                        buscando...
                    </span>
                </div>
                <p x-show="erroCep" x-cloak class="text-xs text-red-500">
                    CEP não encontrado — preencha o endereço manualmente.
                </p>
                <p class="text-xs text-slate-400">
                    Digite o CEP para preencher automaticamente o endereço.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2 space-y-2">
                    <label class="text-sm font-medium">Endereço (Logradouro)</label>
                    <input type="text" name="endereco" x-model="endereco.rua" placeholder="Rua, avenida..."
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Número</label>
                    <input type="text" name="numero" x-model="endereco.numero" placeholder="Ex: 123"
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Complemento</label>
                    <input type="text" name="complemento" x-model="endereco.complemento"
                        placeholder="Apto, bloco, condomínio..."
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Bairro</label>
                    <input type="text" name="bairro" x-model="endereco.bairro"
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Cidade</label>
                    <input type="text" name="cidade" x-model="endereco.cidade"
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">UF</label>
                    <input type="text" name="uf" x-model="endereco.uf" maxlength="2"
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 uppercase">
                </div>
            </div>

            <p class="text-xs text-slate-400">
                Número e complemento (apto, bloco, condomínio) são sempre preenchidos manualmente — a busca por CEP
                não tem essa informação.
            </p>

        </div>
    </div>

    {{-- ========================
    TIPO E RESPONSÁVEL
    ======================== --}}
    <div>
        <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">
            Tipo e Responsável Técnico
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="space-y-2">
                <label class="text-sm font-medium">Tipo de Processo *</label>
                <select name="tipo_processo_id" required
                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                    <option value="">— Selecione o tipo —</option>
                    @foreach ($tiposProcesso as $tipo)
                        <option value="{{ $tipo->id }}"
                            {{ old('tipo_processo_id', $processo->tipo_processo_id ?? '') == $tipo->id ? 'selected' : '' }}>
                            {{ $tipo->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium">Responsável Técnico</label>
                    <button type="button" @click="modalResponsavel = true" class="text-xs text-blue-600 hover:underline">
                        ➕ Novo Responsável
                    </button>
                </div>
                <select name="responsavel_tecnico_id"
                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                    <option value="">— Sem responsável definido —</option>
                    @foreach ($responsaveis as $resp)
                        <option value="{{ $resp->id }}"
                            {{ old('responsavel_tecnico_id', request('responsavel_tecnico_id', $processo->responsavel_tecnico_id ?? '')) == $resp->id ? 'selected' : '' }}>
                            {{ $resp->nome }} @if ($resp->registro)
                                — {{ $resp->registro }}
                            @endif
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-400">
                    Não encontrou o responsável? Clique em "Novo Responsável" acima.
                </p>
            </div>

        </div>
    </div>

    {{-- ========================
    SITUAÇÃO ATUAL
    ======================== --}}
    <div>
        <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">
            Situação Atual
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="space-y-2">
                <label class="text-sm font-medium">Data de Entrada *</label>
                <input type="date" name="data_entrada"
                    value="{{ old('data_entrada', isset($processo->data_entrada) ? $processo->data_entrada->format('Y-m-d') : '') }}"
                    required
                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium">Fase {{ $processo ? 'Atual' : 'Inicial' }} *</label>
                <select name="fase_atual_id" required
                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                    <option value="">— Selecione a fase —</option>
                    @foreach ($fasesProcesso as $fase)
                        <option value="{{ $fase->id }}"
                            {{ old('fase_atual_id', $processo->fase_atual_id ?? '') == $fase->id ? 'selected' : '' }}>
                            {{ $fase->nome }}
                        </option>
                    @endforeach
                </select>
                @unless ($processo)
                    <p class="text-xs text-slate-400">
                        Para processos novos, normalmente "Protocolado".
                    </p>
                @else
                    <p class="text-xs text-slate-400">
                        Para mudar de fase no dia a dia, prefira registrar um novo trâmite na tela de detalhes.
                    </p>
                @endunless
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium">Situação *</label>
                <select name="situacao" required
                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                    @foreach (\App\Models\Processo::$situacoes as $valor => $label)
                        <option value="{{ $valor }}"
                            {{ old('situacao', $processo->situacao ?? 'aberto') == $valor ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">

            <div class="space-y-2">
                <label class="text-sm font-medium">Setor Atual</label>
                <input type="text" name="setor_atual" value="{{ old('setor_atual', $processo->setor_atual ?? '') }}"
                    placeholder="Ex: CTM, Fiscalização, SAJ..."
                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium">Caixa Atual</label>
                <input type="text" name="caixa_atual" value="{{ old('caixa_atual', $processo->caixa_atual ?? '') }}"
                    placeholder="Ex: Caixa 12 — Novembro"
                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
            </div>

        </div>

        <div class="mt-6 space-y-2">
            <label class="text-sm font-medium">Motivo da Pendência</label>
            <textarea name="motivo_pendencia" rows="2" placeholder="Preencha se o processo estiver parado por algum motivo"
                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">{{ old('motivo_pendencia', $processo->motivo_pendencia ?? '') }}</textarea>
        </div>
    </div>

</div>
