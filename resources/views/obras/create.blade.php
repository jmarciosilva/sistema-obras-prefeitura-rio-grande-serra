@extends('layouts.app')

@section('title', 'Nova Obra')
@section('subtitle', 'Cadastro de obra no municipio.')

@section('content')

    <div class="flex justify-center">
        <div class="w-full max-w-5xl" x-data="formObra({{ $statuses->toJson() }})" x-init="init()">

            {{-- BREADCRUMB --}}
            <div class="flex items-center justify-between mb-6">

                <div class="flex items-center gap-2 text-sm text-slate-600">
                    <a href="{{ route('obras.index') }}">Obras</a>
                    <span>›</span>
                    <span class="font-medium text-slate-900">Nova Obra</span>
                </div>

                <button @click="modalAjuda = true" class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg">
                    ❓ Ajuda
                </button>
            </div>

            {{-- VOLTAR --}}
            <div class="mb-6">
                <a href="{{ route('obras.index') }}" class="px-4 py-2 border rounded-lg bg-white">
                    ← Voltar
                </a>
            </div>

            <div class="bg-white rounded-xl shadow border">

                {{-- STEPS --}}
                <div class="flex justify-between px-6 py-4 border-b text-sm">
                    <template x-for="(s, i) in steps">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 flex items-center justify-center rounded-full text-white text-xs font-bold"
                                :class="step >= i ? 'bg-blue-600' : 'bg-slate-300'">
                                <span x-text="(i+1).toString().padStart(2,'0')"></span>
                            </div>
                            <span x-text="s"></span>
                        </div>
                    </template>
                </div>

                <form method="POST" action="{{ route('obras.store') }}">
                    @csrf

                    <div class="p-6 space-y-6">

                        {{-- ERRO --}}
                        <div x-show="erro" class="bg-red-100 text-red-700 p-3 rounded">
                            <span x-text="erro"></span>
                        </div>

                        {{-- STEP 1 --}}
                        <div x-show="step === 0" x-cloak>

                            <input type="text" name="descricao" x-model="form.descricao" placeholder="Descrição da obra"
                                class="w-full border rounded px-3 py-2 mb-4">

                            <select name="status_obra_id" x-model="form.status" class="w-full border rounded px-3 py-2">
                                <option value="">Selecione</option>
                                @foreach ($statuses as $s)
                                    <option value="{{ $s->id }}">{{ $s->nome }}</option>
                                @endforeach
                            </select>

                        </div>

                        {{-- STEP 2 --}}
                        <div x-show="step === 1" x-cloak>

                            <div class="grid md:grid-cols-3 gap-4 mb-4">

                                <input x-model="form.cep" @blur="buscarCep" placeholder="CEP"
                                    class="border rounded px-3 py-2">

                                <input x-model="form.rua" placeholder="Rua" class="border rounded px-3 py-2">
                                <input x-model="form.bairro" placeholder="Bairro" class="border rounded px-3 py-2">
                                <input x-model="form.cidade" placeholder="Cidade" class="border rounded px-3 py-2">
                                <input x-model="form.uf" placeholder="UF" class="border rounded px-3 py-2">

                            </div>

                            <input type="hidden" name="endereco" :value="enderecoCompleto">

                            <div id="map" class="w-full h-[400px] border rounded"></div>

                        </div>

                        {{-- STEP 3 --}}
                        <div x-show="step === 2" x-cloak>

                            <input type="text" name="processo_execucao" x-model="form.processo" placeholder="Processo"
                                class="w-full border rounded px-3 py-2 mb-4">

                            <textarea name="observacoes" x-model="form.observacoes" placeholder="Observações"
                                class="w-full border rounded px-3 py-2"></textarea>

                        </div>

                        {{-- STEP 4 --}}
                        <div x-show="step === 3" x-cloak>

                            <div class="bg-gray-50 p-4 rounded text-sm space-y-2">
                                <p><b>Descrição:</b> <span x-text="form.descricao"></span></p>
                                <p><b>Status:</b> <span x-text="getStatusNome()"></span></p>
                                <p><b>Endereço:</b> <span x-text="enderecoCompleto"></span></p>
                                <p><b>Processo:</b> <span x-text="form.processo"></span></p>
                            </div>

                        </div>

                    </div>

                    {{-- BOTÕES --}}
                    <div class="flex justify-between px-6 py-4 border-t">

                        <button type="button" @click="voltar()" x-show="step > 0" class="px-4 py-2 border rounded">
                            ← Voltar
                        </button>

                        <div class="ml-auto">
                            <button type="button" @click="proximo()" x-show="step < 3"
                                class="px-4 py-2 bg-blue-600 text-white rounded">
                                Próximo →
                            </button>

                            <button type="submit" x-show="step === 3" class="px-4 py-2 bg-green-600 text-white rounded">
                                ✔ Salvar
                            </button>
                        </div>

                    </div>

                </form>

            </div>

            {{-- MODAL AJUDA --}}
            <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center">

                <div class="bg-white p-6 rounded-xl w-full max-w-lg">

                    <h3 class="font-semibold text-lg mb-3">🏗️ Como cadastrar uma obra</h3>

                    <ul class="text-sm space-y-2">
                        <li>1️⃣ Informe os dados da obra</li>
                        <li>2️⃣ Digite o CEP para preencher o endereço</li>
                        <li>3️⃣ Informe o processo</li>
                        <li>4️⃣ Revise e salve</li>
                    </ul>

                    <div class="mt-4 text-right">
                        <button @click="modalAjuda = false" class="px-4 py-2 bg-blue-600 text-white rounded">
                            Entendi
                        </button>
                    </div>

                </div>

            </div>

        </div>
    </div>

    {{-- LEAFLET --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        function formObra(statuses) {
            return {
                step: 0,
                erro: '',
                modalAjuda: false,
                statuses,

                steps: [
                    'Dados',
                    'Localização',
                    'Processo',
                    'Revisão'
                ],

                form: {
                    descricao: '',
                    status: '',
                    cep: '',
                    rua: '',
                    bairro: '',
                    cidade: '',
                    uf: '',
                    processo: '',
                    observacoes: '',
                },

                map: null,
                marker: null,

                init() {
                    this.$nextTick(() => this.initMap());
                },

                proximo() {
                    this.erro = '';

                    if (this.step === 0 && (!this.form.descricao || !this.form.status)) {
                        this.erro = 'Preencha descrição e status.';
                        return;
                    }

                    if (this.step === 1 && !this.form.rua) {
                        this.erro = 'Informe o endereço.';
                        return;
                    }

                    this.step++;
                    this.atualizarMapa();
                },

                voltar() {
                    this.step--;
                },

                get enderecoCompleto() {
                    return `${this.form.rua}, ${this.form.bairro} - ${this.form.cidade}/${this.form.uf}`;
                },

                getStatusNome() {
                    let s = this.statuses.find(x => x.id == this.form.status);
                    return s ? s.nome : '';
                },

                initMap() {
                    this.map = L.map('map').setView([-23.7, -46.4], 12);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png')
                        .addTo(this.map);

                    this.marker = L.marker([-23.7, -46.4], {
                            draggable: true
                        })
                        .addTo(this.map);
                },

                atualizarMapa() {
                    setTimeout(() => this.map.invalidateSize(), 200);
                },

                async buscarCep() {

                    let cep = this.form.cep.replace(/\D/g, '');
                    if (cep.length !== 8) return;

                    let res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                    let data = await res.json();

                    if (data.erro) return alert('CEP não encontrado');

                    this.form.rua = data.logradouro;
                    this.form.bairro = data.bairro;
                    this.form.cidade = data.localidade;
                    this.form.uf = data.uf;

                    let geo = await fetch(
                        `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(data.logradouro)}`
                    );

                    let g = await geo.json();

                    if (g.length) {
                        let lat = parseFloat(g[0].lat);
                        let lon = parseFloat(g[0].lon);

                        this.map.setView([lat, lon], 16);
                        this.marker.setLatLng([lat, lon]);

                        this.atualizarMapa();
                    }
                }
            }
        }
    </script>

@endsection
