{{--
    Partial: resources/views/obras/_form.blade.php
    Usado em create.blade.php e edit.blade.php.

    Variáveis esperadas:
        $obra                  — instância ou null
        $statuses              — collection de StatusObra
        $convenios             — collection de Convenio
        $demandasAbertas       — collection de DemandaProposta
        $conveniosSelecionados — array de IDs já vinculados
--}}

<div class="row g-3">

    {{-- Descrição --}}
    <div class="col-12">
        <x-form-input name="descricao" label="Descrição da Obra" :value="$obra->descricao ?? ''"
            placeholder="Ex: Pavimentação da Rua das Flores" required />
    </div>

    {{-- Status --}}
    <div class="col-12 col-md-6">
        <x-form-select name="status_obra_id" label="Status" :options="$statuses" :selected="$obra->status_obra_id ?? ''" required />
    </div>

    {{-- Endereço --}}
    <div class="col-12 col-md-6">
        <x-form-input name="endereco" label="Endereço / Localização" :value="$obra->endereco ?? ''"
            placeholder="Ex: Rua das Flores, 100 — Bairro Centro" />
    </div>

    {{-- Processo --}}
    <div class="col-12 col-md-6">
        <x-form-input name="processo_execucao" label="Nº do Processo de Execução" :value="$obra->processo_execucao ?? ''"
            placeholder="Ex: 2024/001234" help="Número do processo administrativo na prefeitura." />
    </div>

    {{-- Demanda --}}
    <div class="col-12 col-md-6">
        <x-form-select name="demanda_proposta_id" label="Demanda / Proposta de origem" :options="$demandasAbertas"
            labelKey="numero_demanda" :selected="$obra->demanda_proposta_id ?? ''" placeholder="Nenhuma demanda vinculada" />
    </div>

    {{-- Convênios (multi-select) --}}
    <div class="col-12">
        <label class="form-label fw-semibold">Convênios vinculados</label>
        <select name="convenios[]" multiple class="form-select" style="height:120px">
            @foreach ($convenios as $c)
                <option value="{{ $c->id }}" {{ in_array($c->id, $conveniosSelecionados) ? 'selected' : '' }}>
                    {{ $c->numero_convenio_ano }} — {{ Str::limit($c->descricao, 60) }}
                </option>
            @endforeach
        </select>
        <div class="form-text">Segure Ctrl (Windows) ou ⌘ (Mac) para selecionar mais de um.</div>
    </div>

    {{-- Observações --}}
    <div class="col-12">
        <label for="observacoes" class="form-label fw-semibold">Observações</label>
        <textarea id="observacoes" name="observacoes" rows="3"
            class="form-control @error('observacoes') is-invalid @enderror" placeholder="Informações adicionais...">{{ old('observacoes', $obra->observacoes ?? '') }}</textarea>
        @error('observacoes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
