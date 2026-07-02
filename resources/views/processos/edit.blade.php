@extends('layouts.app')

@section('title', 'Editar Processo')
@section('subtitle', 'Atualização de dados do processo administrativo')

@section('content')

    <div x-data="processoForm({
        cep: {{ \Illuminate\Support\Js::from(old('cep', $processo->cep ?? '')) }},
        endereco: {{ \Illuminate\Support\Js::from(old('endereco', $processo->endereco ?? '')) }},
        numero: {{ \Illuminate\Support\Js::from(old('numero', $processo->numero ?? '')) }},
        complemento: {{ \Illuminate\Support\Js::from(old('complemento', $processo->complemento ?? '')) }},
        bairro: {{ \Illuminate\Support\Js::from(old('bairro', $processo->bairro ?? '')) }},
        cidade: {{ \Illuminate\Support\Js::from(old('cidade', $processo->cidade ?? '')) }},
        uf: {{ \Illuminate\Support\Js::from(old('uf', $processo->uf ?? '')) }},
    })">

        {{-- =========================================================
        BREADCRUMB + AJUDA
        ========================================================= --}}
        <div class="flex items-center justify-between mb-6">

            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('processos.index') }}" class="hover:text-slate-900">Processos</a>
                <span>›</span>
                <a href="{{ route('processos.show', $processo) }}" class="hover:text-slate-900">
                    {{ $processo->processo_numero }}
                </a>
                <span>›</span>
                <span class="text-slate-900 font-medium">Editar</span>
            </div>

            <button type="button" @click="modalAjuda = true"
                class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                ❓ Ajuda
            </button>

        </div>

        {{-- BOTÃO VOLTAR --}}
        <div class="mb-6">
            <a href="{{ route('processos.show', $processo) }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                  text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-100 transition">
                ← Voltar
            </a>
        </div>

        <div class="flex justify-center">
            <div class="w-full max-w-4xl">

                <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                    {{-- HEADER --}}
                    <div class="px-8 py-6 border-b border-slate-200">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-xl">
                                ✏️
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">Editar Processo</h2>
                                <p class="text-sm text-slate-600 mt-1">
                                    Atualize as informações do processo
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- ERROS --}}
                    @if ($errors->any())
                        <div class="mx-8 mt-6 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg">
                            <strong>Erro ao salvar:</strong>
                            <ul class="mt-2 text-sm list-disc ml-6">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- MENSAGEM DE SUCESSO AO CRIAR RESPONSÁVEL VIA MODAL --}}
                    @if (session('responsavel_criado'))
                        <div
                            class="mx-8 mt-6 bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm">
                            ✅ {{ session('responsavel_criado') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('processos.update', $processo) }}">
                        @csrf
                        @method('PUT')

                        @include('processos._form')

                        {{-- FOOTER --}}
                        <div class="px-8 py-5 border-t bg-slate-50 rounded-b-xl flex justify-between">

                            <a href="{{ route('processos.show', $processo) }}"
                                class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-700 bg-white border">
                                ← Cancelar
                            </a>

                            <button type="submit"
                                class="px-6 py-2.5 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                ✔ Atualizar Processo
                            </button>

                        </div>

                    </form>

                </div>
            </div>
        </div>

        {{-- =========================================================
        MODAL AJUDA
        ========================================================= --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">

                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Edição de Processo</h3>
                    <p class="text-sm opacity-90">Como atualizar o processo</p>
                </div>

                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p>Atualize os dados cadastrais conforme necessário.</p>
                    <p><strong>Fase:</strong> Alterar a fase aqui não gera um registro na linha do tempo. Para
                        movimentações do dia a dia, prefira registrar um novo trâmite na tela de detalhes do
                        processo.</p>

                    <div class="bg-slate-50 p-4 rounded text-xs">
                        💡 Use este formulário para corrigir dados cadastrais (número, requerente, endereço,
                        responsável técnico).
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
        MODAL — NOVO RESPONSÁVEL TÉCNICO (RÁPIDO)
        ========================================================= --}}
        <div x-show="modalResponsavel" x-cloak
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">

                <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">👷 Novo Responsável Técnico</h3>
                    <p class="text-sm opacity-90">Cadastro rápido — engenheiro ou arquiteto (CREA/CAU)</p>
                </div>

                <form method="POST" action="{{ route('admin.responsaveis-tecnicos.store') }}">
                    @csrf
                    {{-- Redireciona de volta para este formulário, já com o novo responsável selecionado --}}
                    <input type="hidden" name="_redirect_back" value="{{ route('processos.edit', $processo) }}">

                    <div class="p-6 space-y-4">

                        <div class="space-y-1">
                            <label class="text-sm font-medium">Nome *</label>
                            <input type="text" name="nome" required placeholder="Nome completo"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-green-500">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-sm font-medium">Registro (CREA/CAU)</label>
                                <input type="text" name="registro" placeholder="Ex: CREA-SP 123456"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-green-500">
                            </div>

                            <div class="space-y-1">
                                <label class="text-sm font-medium">Telefone</label>
                                <input type="text" name="telefone" placeholder="(11) 98888-8888"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-green-500">
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-700">
                            💡 Após salvar, o responsável será selecionado automaticamente neste formulário.
                        </div>

                    </div>

                    <div class="px-6 py-4 border-t bg-slate-50 flex justify-between">
                        <button type="button" @click="modalResponsavel = false"
                            class="px-4 py-2 text-sm border rounded-lg text-slate-600 hover:bg-slate-100">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-5 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700">
                            ✔ Salvar Responsável
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>

    <script>
        function processoForm(initial = {}) {
            return {
                modalAjuda: false,
                modalResponsavel: false,
                buscandoCep: false,
                erroCep: false,

                endereco: {
                    cep: initial.cep || '',
                    rua: initial.endereco || '',
                    numero: initial.numero || '',
                    complemento: initial.complemento || '',
                    bairro: initial.bairro || '',
                    cidade: initial.cidade || '',
                    uf: initial.uf || '',
                },

                async buscarCep() {
                    this.erroCep = false;

                    let cep = this.endereco.cep.replace(/\D/g, '');
                    if (cep.length !== 8) return;

                    this.buscandoCep = true;

                    try {
                        let res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                        let data = await res.json();

                        if (data.erro) {
                            this.erroCep = true;
                            return;
                        }

                        this.endereco.rua = data.logradouro || this.endereco.rua;
                        this.endereco.bairro = data.bairro || '';
                        this.endereco.cidade = data.localidade || '';
                        this.endereco.uf = data.uf || '';
                    } catch (e) {
                        this.erroCep = true;
                    } finally {
                        this.buscandoCep = false;
                    }
                },
            }
        }
    </script>

@endsection
