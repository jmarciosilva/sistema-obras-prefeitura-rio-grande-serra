{{--
    Componente: <x-form-select name="status_id" label="Status" :options="$statuses" required />

    Props:
        $name       — atributo name/id
        $label      — rótulo
        $options    — Collection ou array associativo [id => label] ou [['value','label',...]]
        $selected   — valor selecionado (default: old(name))
        $placeholder — opção vazia (default: "Selecione...")
        $required
        $valueKey   — chave do valor no array de objetos (default: 'id')
        $labelKey   — chave do texto no array de objetos (default: 'nome')
--}}
@props([
    'name',
    'label' => '',
    'options' => [],
    'selected' => null,
    'placeholder' => 'Selecione...',
    'required' => false,
    'valueKey' => 'id',
    'labelKey' => 'nome',
])

@php
    $valorAtual = $selected ?? old($name);
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

    <select id="{{ $name }}" name="{{ $name }}" @if ($required) required @endif
        {{ $attributes->merge(['class' => 'form-select' . ($erro ? ' is-invalid' : '')]) }}>
        <option value="">{{ $placeholder }}</option>

        @foreach ($options as $key => $option)
            @if (is_object($option))
                <option value="{{ $option->{$valueKey} }}"
                    {{ (string) $valorAtual === (string) $option->{$valueKey} ? 'selected' : '' }}>
                    {{ $option->{$labelKey} }}
                </option>
            @elseif(is_array($option))
                <option value="{{ $option[$valueKey] }}"
                    {{ (string) $valorAtual === (string) $option[$valueKey] ? 'selected' : '' }}>
                    {{ $option[$labelKey] }}
                </option>
            @else
                {{-- Array associativo simples: ['1' => 'Label'] --}}
                <option value="{{ $key }}" {{ (string) $valorAtual === (string) $key ? 'selected' : '' }}>
                    {{ $option }}
                </option>
            @endif
        @endforeach
    </select>

    @if ($erro)
        <div class="invalid-feedback">{{ $erro }}</div>
    @endif
</div>
