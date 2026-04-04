@extends('layouts.app')
@section('title', 'Editar Obra')
@section('conteudo')

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('obras.index') }}">Obras</a></li>
            <li class="breadcrumb-item">
                <a href="{{ route('obras.show', $obra) }}">{{ Str::limit($obra->descricao, 40) }}</a>
            </li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>

    <h1 class="pagina-titulo mb-4">
        <i class="bi bi-pencil me-2"></i>Editar Obra
    </h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('obras.update', $obra) }}">
                @csrf
                @method('PUT')
                @include('obras._form')
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Salvar Alterações
                    </button>
                    <a href="{{ route('obras.show', $obra) }}" class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection
