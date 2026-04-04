@extends('layouts.app')
@section('title', 'Órgãos Financiadores')
@section('subtitle', 'Entidades que concedem recursos para obras municipais')
@section('content')
    <div class="space-y-6" x-data="orgaoTable()">

        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">Órgãos Financiadores</h1>
                <p class="text-sm text-slate-500">Cadastre os órgãos que concedem convênios (CAIXA, Governo do Estado, etc.).
                </p>
            </div>
            <div class="flex gap-2">
                <button type="button" @click="modalAjuda=true"
                    class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">❓
                    Ajuda</button>
                <a href="{{ route('admin.orgaos-financiadores.create') }}"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">➕ Novo Órgão</a>
            </div>
        </div>

        {{-- FILTRO DE ESFERA --}}
        <form method="GET" class="bg-white rounded-xl shadow p-4 flex flex-wrap gap-3 items-center">
            <span class="text-sm text-slate-600 font-medium">Filtrar por esfera:</span>
            @foreach (['federal' => 'Federal', 'estadual' => 'Estadual', 'municipal' => 'Municipal'] as $val => $label)
                <a href="{{ route('admin.orgaos-financiadores.index', ['esfera' => $val]) }}"
                    class="px-3 py-1.5 text-xs rounded-lg border transition
                {{ request('esfera') === $val ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50' }}">
                    {{ $label }}
                </a>
            @endforeach
            @if (request('esfera'))
                <a href="{{ route('admin.orgaos-financiadores.index') }}"
                    class="px-3 py-1.5 text-xs text-slate-500 hover:text-slate-700">✕ Limpar</a>
            @endif
        </form>

        @if (session('sucesso'))
            <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm">✅
                {{ session('sucesso') }}</div>
        @endif
        @if ($errors->has('geral'))
            <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">⚠️
                {{ $errors->first('geral') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs text-slate-500 font-semibold">Órgão</th>
                        <th class="px-4 py-3 text-left text-xs text-slate-500 font-semibold">Sigla</th>
                        <th class="px-4 py-3 text-center text-xs text-slate-500 font-semibold">Esfera</th>
                        <th class="px-4 py-3 text-center text-xs text-slate-500 font-semibold">Convênios</th>
                        <th class="px-4 py-3 text-right text-xs text-slate-500 font-semibold">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($orgaos as $o)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $o->nome }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $o->sigla ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @php $esferaCor = ['federal'=>'bg-blue-100 text-blue-700','estadual'=>'bg-green-100 text-green-700','municipal'=>'bg-indigo-100 text-indigo-700'][$o->esfera] ?? 'bg-slate-100 text-slate-600'; @endphp
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs font-medium {{ $esferaCor }}">{{ $o->esfera_label }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-semibold {{ $o->convenios_count > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-400' }}">{{ $o->convenios_count }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.orgaos-financiadores.show', $o) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100">👁
                                        Ver</a>
                                    <a href="{{ route('admin.orgaos-financiadores.edit', $o) }}"
                                        class="px-3 py-1 text-xs rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100">✏️
                                        Editar</a>
                                    <button type="button"
                                        @click="abrirModalDelete('{{ route('admin.orgaos-financiadores.destroy', $o) }}','{{ addslashes($o->nome) }}',{{ $o->convenios_count }})"
                                        class="px-3 py-1 text-xs rounded-lg border border-red-200 text-red-700 bg-red-50 hover:bg-red-100">🗑
                                        Excluir</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-slate-400">
                                <p class="text-2xl mb-2">🏛️</p>
                                <p class="text-sm">Nenhum órgão cadastrado.</p><a
                                    href="{{ route('admin.orgaos-financiadores.create') }}"
                                    class="inline-block mt-3 text-xs text-blue-600 hover:underline">➕ Criar primeiro
                                    órgão</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- MODAL AJUDA --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Órgãos Financiadores</h3>
                </div>
                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p><strong>Órgãos Financiadores</strong> são as entidades que concedem recursos para obras municipais
                        via convênios.</p>
                    <p><strong>Esfera:</strong> Federal (ex: Ministério, CAIXA), Estadual (ex: Governo SP, SABESP) ou
                        Municipal.</p>
                    <p><strong>Sigla:</strong> Opcional, usada para identificação rápida (ex: CEF, FNDE, SABESP).</p>
                    <p>Órgãos com convênios vinculados <strong>não podem ser excluídos</strong>.</p>
                    <div class="bg-slate-50 p-4 rounded text-xs">💡 Exemplos: CAIXA Econômica Federal (CEF) · FNDE ·
                        Ministério das Cidades · Governo do Estado de SP · SABESP</div>
                </div>
                <div class="px-6 py-4 border-t flex justify-end"><button @click="modalAjuda=false"
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg">Entendi 👍</button></div>
            </div>
        </div>

        {{-- MODAL DELETE --}}
        <div x-show="modalDelete" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md text-center">
                <h3 class="text-lg font-semibold text-red-600 mb-4">Confirmar exclusão</h3>
                <template x-if="temConvenios">
                    <div
                        class="mb-4 bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-lg text-sm text-left">
                        ⚠️ <strong x-text="nomeOrgao"></strong> possui convênios e <strong>não pode ser excluído</strong>.
                    </div>
                </template>
                <template x-if="!temConvenios">
                    <p class="text-sm text-slate-600 mb-6">O órgão <strong x-text="nomeOrgao"></strong> será removido
                        permanentemente.</p>
                </template>
                <div class="flex justify-center gap-3">
                    <button @click="modalDelete=false" class="px-4 py-2 border rounded">Cancelar</button>
                    <template x-if="!temConvenios">
                        <form :action="url" method="POST">@csrf @method('DELETE')<button
                                class="px-5 py-2 bg-red-600 text-white rounded">Excluir</button></form>
                    </template>
                </div>
            </div>
        </div>
    </div>
    <script>
        function orgaoTable() {
            return {
                modalDelete: false,
                modalAjuda: false,
                url: '',
                nomeOrgao: '',
                temConvenios: false,
                abrirModalDelete(url, nome, qtd) {
                    this.url = url;
                    this.nomeOrgao = nome;
                    this.temConvenios = qtd > 0;
                    this.modalDelete = true;
                }
            }
        }
    </script>
@endsection
