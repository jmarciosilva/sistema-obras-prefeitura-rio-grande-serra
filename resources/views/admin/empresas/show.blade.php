@extends('layouts.app')

@section('title', 'Visualizar Empresa')
@section('subtitle', 'Dados da empresa contratada')

@section('content')

    <div x-data="{ modalAjuda: false, modalDelete: false }">

        {{-- BREADCRUMB --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('admin.empresas.index') }}" class="hover:text-slate-900">Empresas</a>
                <span>›</span>
                <span class="text-slate-900 font-medium">{{ $empresa->nomeExibicao() }}</span>
            </div>
            <button type="button" @click="modalAjuda = true"
                class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                ❓ Ajuda
            </button>
        </div>

        <div class="mb-6">
            <a href="{{ route('admin.empresas.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                   text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-100 transition">
                ← Voltar
            </a>
        </div>

        {{-- FEEDBACK --}}
        @if (session('sucesso'))
            <div class="mb-6 bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm">
                ✅ {{ session('sucesso') }}
            </div>
        @endif

        <div class="flex justify-center">
            <div class="w-full max-w-4xl space-y-6">

                {{-- CARD PRINCIPAL --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                    <div class="px-8 py-6 border-b border-slate-200">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center text-2xl">
                                🏢
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">
                                    {{ $empresa->nomeExibicao() }}
                                </h2>
                                @if ($empresa->nome_fantasia && $empresa->nome_fantasia !== $empresa->razao_social)
                                    <p class="text-sm text-slate-500 mt-0.5">{{ $empresa->razao_social }}</p>
                                @endif
                                <p class="text-sm font-mono text-slate-500 mt-1">{{ $empresa->cnpj }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="px-8 py-6 space-y-6">

                        {{-- RESPONSÁVEL --}}
                        <div>
                            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Responsável</h3>
                            <p class="text-sm font-medium text-slate-800">{{ $empresa->responsavel ?? '—' }}</p>
                        </div>

                        {{-- CONTATO --}}
                        <div>
                            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Contato</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                                <div>
                                    <p class="text-slate-500">Telefone</p>
                                    <p class="font-medium text-slate-800">{{ $empresa->telefone ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">E-mail</p>
                                    @if ($empresa->email)
                                        <a href="mailto:{{ $empresa->email }}"
                                            class="font-medium text-blue-600 hover:underline">
                                            {{ $empresa->email }}
                                        </a>
                                    @else
                                        <p class="font-medium text-slate-800">—</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- ENDEREÇO --}}
                        <div>
                            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-2">Endereço</h3>
                            <p class="text-sm text-slate-800">{{ $empresa->endereco_completo ?? '—' }}</p>
                        </div>

                        {{-- METADADOS --}}
                        <div>
                            <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4">Informações do Sistema</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                                <div>
                                    <p class="text-slate-500">Cadastrada em</p>
                                    <p class="font-medium text-slate-800">{{ $empresa->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Última atualização</p>
                                    <p class="font-medium text-slate-800">{{ $empresa->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="px-8 py-5 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-between items-center">

                        @if ($empresa->contratos->isEmpty())
                            <button type="button" @click="modalDelete = true"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium
                                   text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 transition">
                                🗑 Excluir Empresa
                            </button>
                        @else
                            <span class="text-xs text-slate-400 italic">
                                Exclusão indisponível — empresa possui {{ $empresa->contratos->count() }} contrato(s).
                            </span>
                        @endif

                        <a href="{{ route('admin.empresas.edit', $empresa) }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium
                               text-white bg-blue-600 hover:bg-blue-700 transition">
                            ✏️ Editar Empresa
                        </a>

                    </div>

                </div>

                {{-- CONTRATOS VINCULADOS --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                    <div class="px-8 py-5 border-b flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-800">📋 Contratos Vinculados</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Contratos nos quais esta empresa foi contratada</p>
                        </div>
                        <a href="{{ route('contratos.create') }}"
                            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            ➕ Novo Contrato
                        </a>
                    </div>

                    <div class="p-6">
                        @forelse($empresa->contratos as $contrato)
                            <div class="flex items-center justify-between py-3 border-b last:border-0 text-sm">
                                <div>
                                    <a href="{{ route('contratos.show', $contrato) }}"
                                        class="font-medium text-blue-600 hover:underline">
                                        {{ $contrato->numero_contrato_ano ?? 'Contrato #' . $contrato->id }}
                                    </a>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Obra: {{ $contrato->obra->descricao ?? '—' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-slate-700">
                                        R$ {{ $contrato->valor_contrato
                                            ? number_format($contrato->valor_contrato, 2, ',', '.')
                                            : '—' }}
                                    </p>
                                    @if ($contrato->vigencia_contrato)
                                        @php $cv = $contrato->estaVencido(); @endphp
                                        <p class="text-xs {{ $cv ? 'text-red-500' : 'text-slate-400' }}">
                                            Vigência: {{ $contrato->vigencia_contrato->format('d/m/Y') }}
                                            {{ $cv ? '(vencido)' : '' }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 text-center py-6">
                                Nenhum contrato vinculado a esta empresa.
                            </p>
                        @endforelse
                    </div>

                </div>

            </div>
        </div>

        {{-- MODAL AJUDA --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Visualização de Empresa</h3>
                    <p class="text-sm opacity-90">Entenda os dados exibidos</p>
                </div>
                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p><strong>Contratos:</strong> Lista de contratos nos quais esta empresa foi contratada pela prefeitura.</p>
                    <p><strong>Exclusão:</strong> Somente é possível excluir a empresa se ela não possuir contratos vinculados.</p>
                    <div class="bg-slate-50 p-4 rounded text-xs">
                        💡 Use o botão "Novo Contrato" para criar um contrato já vinculado a esta empresa.
                    </div>
                </div>
                <div class="px-6 py-4 border-t flex justify-end">
                    <button @click="modalAjuda = false" class="px-5 py-2 bg-blue-600 text-white rounded-lg">
                        Entendi 👍
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL EXCLUSÃO --}}
        <div x-show="modalDelete" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md text-center">
                <h3 class="text-lg font-semibold text-red-600 mb-4">Confirmar exclusão</h3>
                <p class="text-sm text-slate-600 mb-6">Essa ação não poderá ser desfeita.</p>
                <div class="flex justify-center gap-3">
                    <button @click="modalDelete = false" class="px-4 py-2 border rounded">Cancelar</button>
                    <form action="{{ route('admin.empresas.destroy', $empresa) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="px-5 py-2 bg-red-600 text-white rounded">Excluir</button>
                    </form>
                </div>
            </div>
        </div>

    </div>

@endsection