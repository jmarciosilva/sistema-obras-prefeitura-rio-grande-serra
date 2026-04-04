{{--
    Componente: <x-form-input name="titulo" label="Título da Obra" required />

    Props:
        $name       — atributo name/id do input (obrigatório)
        $label      — rótulo exibido acima do campo
        $type       — tipo do input (default: text)
        $value      — valor inicial (default: old(name))
        $required   — boolean (default: false)
        $help       — texto de ajuda abaixo do campo
        $placeholder
--}}
@props([
    'name',
    'label' => '',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'help' => '',
    'placeholder' => '',
])

@php
    $valor = $value ?? old($name);
    $erro = $errors->first($name);
@endphp

<div class="mb-3">
    @if ($label)
        <label for="{{ $name }}" class="form-label">
            {{ $label }}
            @if ($required)
                <span class="text-danger" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" value="{{ $valor }}"
        placeholder="{{ $placeholder }}" @if ($required) required @endif
        {{ $attributes->merge(['class' => 'form-control' . ($erro ? ' is-invalid' : '')]) }}>

    @if ($erro)
        <div class="invalid-feedback">{{ $erro }}</div>
    @elseif($help)
        <div class="form-text text-muted">{{ $help }}</div>
    @endif
</div>
