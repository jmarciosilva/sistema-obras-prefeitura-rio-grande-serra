{{--
    Componente: <x-card-stat titulo="Total de Obras" valor="42" icone="bi-cone-striped" cor="primary" />

    Props:
        $titulo  — texto do card
        $valor   — número ou texto destacado
        $icone   — classe Bootstrap Icon (ex: "bi-cone-striped")
        $cor     — variante Bootstrap: primary | success | warning | danger | info | secondary
        $rodape  — texto opcional abaixo do valor
--}}
@props(['titulo', 'valor', 'icone' => 'bi-bar-chart', 'cor' => 'primary', 'rodape' => ''])

<div class="card h-100 border-0 shadow-sm">
    <div class="card-body d-flex align-items-center gap-3">
        {{-- Ícone --}}
        <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3"
            style="width:56px;height:56px;background:var(--bs-{{ $cor }}-bg-subtle, #e8eef8);">
            <i class="bi {{ $icone }} fs-2 text-{{ $cor }}"></i>
        </div>

        {{-- Dados --}}
        <div class="flex-grow-1 min-w-0">
            <div class="text-muted small fw-semibold text-uppercase tracking-wide">{{ $titulo }}</div>
            <div class="fw-bold fs-2 lh-1 mt-1" style="color:var(--pref-azul-esc)">{{ $valor }}</div>
            @if ($rodape)
                <div class="small text-muted mt-1">{{ $rodape }}</div>
            @endif
        </div>
    </div>
</div>
