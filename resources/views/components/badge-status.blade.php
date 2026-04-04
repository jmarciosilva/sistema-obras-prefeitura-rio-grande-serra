{{--
    Componente: <x-badge-status :status="$obra->status" />

    Props:
        $status  — instância de StatusObra (com nome e cor)

    Uso:
        <x-badge-status :status="$obra->status" />
--}}
@props(['status'])

<span class="badge"
    style="background-color: {{ $status->cor ?? '#6c757d' }}; font-size: .8rem; padding: 5px 10px; border-radius: 20px;">
    {{ $status->nome }}
</span>
