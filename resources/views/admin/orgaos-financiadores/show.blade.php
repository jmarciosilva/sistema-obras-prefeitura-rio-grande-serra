@extends('layouts.app')
@section('title', 'Visualizar Órgão Financiador')
@section('content')
    <div x-data="{ modalAjuda: false }">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('admin.orgaos-financiadores.index') }}" class="hover:text-slate-900">Órgãos
                    Financiadores</a>
                <span>›</span><span class="text-slate-900 font-medium">{{ $orgao->nome }}</span>
            </div>
            <button type="button" @click="modalAjuda=true"
                class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">❓
                Ajuda</button>
        </div>
        <div class="mb-6"><a href="{{ route('admin.orgaos-financiadores.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-100 transition">←
                Voltar</a></div>

        @if (session('sucesso'))
            <div class="mb-6 bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm">✅
                {{ session('sucesso') }}</div>
        @endif

        <div class="flex justify-center">
            <div class="w-full max-w-2xl">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="px-8 py-6 border-b">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-2xl">🏛️
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">{{ $orgao->nome }}</h2>
                                @if ($orgao->sigla)
                                    <p class="text-sm font-mono text-slate-500 mt-0.5">{{ $orgao->sigla }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="px-8 py-6 space-y-6">
                        <div class="grid grid-cols-2 gap-6 text-sm">
                            <div>
                                <p class="text-slate-500 mb-1">Esfera</p>
                                @php $cor = ['federal'=>'bg-blue-100 text-blue-700','estadual'=>'bg-green-100 text-green-700','municipal'=>'bg-indigo-100 text-indigo-700'][$orgao->esfera]??'bg-slate-100 text-slate-600'; @endphp
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-medium {{ $cor }}">{{ $orgao->esfera_label }}</span>
                            </div>
                            <div>
                                <p class="text-slate-500 mb-1">Convênios vinculados</p>
                                <span
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-full text-sm font-bold {{ $orgao->convenios_count > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-400' }}">{{ $orgao->convenios_count }}</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-6 text-sm">
                            <div>
                                <p class="text-slate-500">Criado em</p>
                                <p class="font-medium text-slate-800">{{ $orgao->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Atualizado em</p>
                                <p class="font-medium text-slate-800">{{ $orgao->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="px-8 py-5 border-t bg-slate-50 rounded-b-xl flex justify-end">
                        <a href="{{ route('admin.orgaos-financiadores.edit', $orgao) }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition">✏️
                            Editar Órgão</a>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda</h3>
                </div>
                <div class="p-6 text-sm text-slate-700">
                    <p>Convênios com este órgão aparecem na listagem de convênios. Órgãos com convênios não podem ser
                        excluídos.</p>
                </div>
                <div class="px-6 py-4 border-t flex justify-end"><button @click="modalAjuda=false"
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg">Entendi 👍</button></div>
            </div>
        </div>
    </div>
@endsection
