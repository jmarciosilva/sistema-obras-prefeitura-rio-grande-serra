@extends('layouts.app')

@section('title', 'Processos Administrativos')
@section('subtitle', 'Licenciamento, alvarás e certidões — acompanhamento por fase')

@section('content')

    <div class="space-y-6" x-data="processoTable()">

        {{-- =========================================================
        HEADER
        ========================================================= --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">Processos Administrativos</h1>
                <p class="text-sm text-slate-500">
                    Acompanhe em qual fase está cada processo e o motivo de eventuais pendências.
                </p>
            </div>

            <div class="flex gap-2">

                {{-- AJUDA --}}
                <button @click="modalAjuda = true"
                    class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg border border-amber-200 hover:bg-amber-200">
                    ❓ Ajuda
                </button>

                {{-- NOVO PROCESSO --}}
                @if (auth()->user()->perfil !== 'operador' && auth()->user()->perfil !== 'secretario')
                    <a href="{{ route('processos.create') }}"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        ➕ Novo Processo
                    </a>
                @endif

            </div>
        </div>

        {{-- =========================================================
        FILTROS
        ========================================================= --}}
        <form method="GET" class="bg-white rounded-xl shadow p-4 grid grid-cols-1 md:grid-cols-5 gap-3">

            <input type="text" name="busca" value="{{ request('busca') }}"
                placeholder="Nº do processo ou requerente..."
                class="border rounded-lg px-4 py-2 text-sm md:col-span-2">

            <input type="text" name="endereco" value="{{ request('endereco') }}" placeholder="Endereço..."
                class="border rounded-lg px-4 py-2 text-sm">

            <select name="tipo_processo_id" class="border rounded-lg px-4 py-2 text-sm">
                <option value="">Todos os tipos</option>
                @foreach ($tiposProcesso as $tipo)
                    <option value="{{ $tipo->id }}" {{ request('tipo_processo_id') == $tipo->id ? 'selected' : '' }}>
                        {{ $tipo->nome }}
                    </option>
                @endforeach
            </select>

            <select name="fase_atual_id" class="border rounded-lg px-4 py-2 text-sm">
                <option value="">Todas as fases</option>
                @foreach ($fasesProcesso as $fase)
                    <option value="{{ $fase->id }}" {{ request('fase_atual_id') == $fase->id ? 'selected' : '' }}>
                        {{ $fase->nome }}
                    </option>
                @endforeach
            </select>

            <select name="responsavel_tecnico_id" class="border rounded-lg px-4 py-2 text-sm">
                <option value="">Todos os responsáveis</option>
                @foreach ($responsaveis as $resp)
                    <option value="{{ $resp->id }}"
                        {{ request('responsavel_tecnico_id') == $resp->id ? 'selected' : '' }}>
                        {{ $resp->nome }}
                    </option>
                @endforeach
            </select>

            <select name="situacao" class="border rounded-lg px-4 py-2 text-sm">
                <option value="">Aberto e arquivado</option>
                @foreach (\App\Models\Processo::$situacoes as $valor => $label)
                    <option value="{{ $valor }}" {{ request('situacao') == $valor ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <div class="md:col-span-5 flex gap-2 justify-end">
                <a href="{{ route('processos.index') }}" class="px-4 py-2 text-sm border rounded-lg text-slate-600">
                    Limpar
                </a>
                <button class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm">
                    Filtrar
                </button>
            </div>

        </form>

        {{-- =========================================================
        TABELA
        ========================================================= --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">

            <table class="w-full text-sm">

                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left">Processo</th>
                        <th class="text-left">Tipo</th>
                        <th class="text-left">Fase Atual</th>
                        <th class="text-left">Responsável Técnico</th>
                        <th class="text-left">Entrada</th>
                        <th class="text-right px-4">Ações</th>
                    </tr>
                </thead>

                <tbody class="divide-y">

                    @forelse($processos as $processo)
                        <tr class="hover:bg-slate-50">

                            {{-- PROCESSO --}}
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">
                                    {{ $processo->processo_numero }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $processo->requerente }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    {{ $processo->endereco_completo ?? '—' }}
                                </div>
                            </td>

                            {{-- TIPO --}}
                            <td class="text-slate-700">
                                {{ $processo->tipoProcesso->nome ?? '—' }}
                            </td>

                            {{-- FASE ATUAL --}}
                            <td>
                                <span class="px-2 py-1 rounded-full text-xs font-medium text-white"
                                    style="background-color: {{ $processo->faseAtual->cor ?? '#64748b' }}">
                                    {{ $processo->faseAtual->nome ?? '—' }}
                                </span>
                                @if ($processo->motivo_pendencia)
                                    <div class="text-xs text-red-600 mt-1" title="{{ $processo->motivo_pendencia }}">
                                        ⚠️ {{ \Illuminate\Support\Str::limit($processo->motivo_pendencia, 40) }}
                                    </div>
                                @endif
                            </td>

                            {{-- RESPONSÁVEL --}}
                            <td class="text-slate-700">
                                {{ $processo->responsavelTecnico->nome ?? '—' }}
                            </td>

                            {{-- ENTRADA --}}
                            <td class="text-slate-700">
                                {{ $processo->data_entrada?->format('d/m/Y') ?? '—' }}
                            </td>

                            {{-- AÇÕES --}}
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">

                                    <a href="{{ route('processos.show', $processo) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                        👁 Ver
                                    </a>

                                    @if (auth()->user()->perfil !== 'operador' && auth()->user()->perfil !== 'secretario')
                                        <a href="{{ route('processos.edit', $processo) }}"
                                            class="px-3 py-1 text-xs rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100">
                                            ✏️ Editar
                                        </a>
                                    @endif

                                    @if (auth()->user()->perfil === 'admin')
                                        <button @click="abrirModalDelete('{{ route('processos.destroy', $processo) }}')"
                                            class="px-3 py-1 text-xs rounded-lg border border-red-200 text-red-700 bg-red-50 hover:bg-red-100">
                                            🗑 Excluir
                                        </button>
                                    @endif

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-500">
                                Nenhum processo encontrado.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

            {{-- PAGINAÇÃO --}}
            <div class="p-4 border-t">
                {{ $processos->links() }}
            </div>

        </div>

        {{-- =========================================================
        MODAL AJUDA
        ========================================================= --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">

                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Processos Administrativos</h3>
                    <p class="text-sm opacity-90">Como utilizar esta tela</p>
                </div>

                <div class="p-6 space-y-3 text-sm text-slate-700">
                    <p><strong>➕ Novo Processo:</strong> Cadastra um novo processo (alvará, certidão, ligação etc.).</p>
                    <p><strong>Fase Atual:</strong> Mostra em qual etapa da tramitação o processo está agora.</p>
                    <p><strong>⚠️ Motivo:</strong> Quando exibido, indica por que o processo está parado (ex.:
                        notificação aguardando devolutiva).</p>
                    <p><strong>👁 Ver:</strong> Abre a linha do tempo completa de trâmites do processo.</p>
                    <div class="mt-3 p-3 bg-slate-50 rounded text-xs">
                        💡 Use os filtros para localizar processos por tipo, responsável técnico, endereço ou fase.
                    </div>
                </div>

                <div class="px-6 py-4 border-t flex justify-end">
                    <button @click="modalAjuda = false" class="px-5 py-2 bg-blue-600 text-white rounded-lg">
                        Entendi 👍
                    </button>
                </div>

            </div>
        </div>

        {{-- =========================================================
        MODAL DELETE
        ========================================================= --}}
        <div x-show="modalDelete" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center">

            <div class="bg-white rounded-xl p-6 w-full max-w-md text-center">

                <h3 class="text-lg font-semibold text-red-600 mb-4">
                    Confirmar exclusão
                </h3>

                <p class="text-sm text-slate-600 mb-6">
                    Essa ação removerá o processo e todo o seu histórico de trâmites. Não poderá ser desfeita.
                </p>

                <div class="flex justify-center gap-3">
                    <button @click="modalDelete = false" class="px-4 py-2 border rounded">
                        Cancelar
                    </button>

                    <form :action="url" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="px-5 py-2 bg-red-600 text-white rounded">
                            Excluir
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>

    <script>
        function processoTable() {
            return {
                modalAjuda: false,
                modalDelete: false,
                url: '',

                abrirModalDelete(url) {
                    this.url = url;
                    this.modalDelete = true;
                }
            }
        }
    </script>

@endsection
