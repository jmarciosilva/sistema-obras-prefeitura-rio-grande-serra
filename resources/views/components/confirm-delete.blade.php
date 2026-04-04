{{--
    Componente: <x-confirm-delete :action="route('obras.destroy', $obra)" />

    Props:
        $action  — URL do DELETE
        $label   — texto do botão (default: "Excluir")
        $titulo  — título do modal
        $mensagem — corpo do modal

    Gera um botão que abre um modal Bootstrap de confirmação.
    O modal submete um form com método DELETE.
--}}
@props([
    'action',
    'label' => 'Excluir',
    'titulo' => 'Confirmar exclusão',
    'mensagem' => 'Tem certeza que deseja excluir este registro? Esta ação não pode ser desfeita.',
])

@php
    $modalId = 'modalDel' . uniqid();
@endphp

{{-- Botão de trigger --}}
<button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}"
    aria-label="{{ $label }}">
    <i class="bi bi-trash me-1"></i>{{ $label }}
</button>

{{-- Modal --}}
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger" id="{{ $modalId }}Label">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $titulo }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" style="font-size:1rem">{{ $mensagem }}</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Cancelar
                </button>
                <form method="POST" action="{{ $action }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Sim, excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
