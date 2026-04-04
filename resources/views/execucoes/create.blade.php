@extends('layouts.app')

@section('title', 'Nova Medição')
@section('subtitle', 'Registro de execução / medição de obra')

@section('content')

    <div x-data="medicaoForm({{ $saldosPorContrato->toJson() }})">

        {{-- BREADCRUMB --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('obras.index') }}" class="hover:text-slate-900">Obras</a>
                <span>›</span>
                <a href="{{ route('obras.show', $obra) }}" class="hover:text-slate-900">
                    {{ Str::limit($obra->descricao, 40) }}
                </a>
                <span>›</span>
                <span class="text-slate-900 font-medium">Nova Medição</span>
            </div>
            <button type="button" @click="modalAjuda = true"
                class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">
                ❓ Ajuda
            </button>
        </div>

        <div class="mb-6">
            <a href="{{ route('obras.show', $obra) }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                   text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-100 transition">
                ← Voltar à Obra
            </a>
        </div>

        {{-- CARD DA OBRA --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-lg">🏗️</div>
            <div>
                <p class="font-semibold text-blue-900">{{ $obra->descricao }}</p>
                <p class="text-sm text-blue-700">📍 {{ $obra->endereco ?? 'Local não informado' }}</p>
            </div>
        </div>

        @if ($contratos->isEmpty())
            <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-4 rounded-xl">
                <p class="font-semibold">⚠️ Esta obra não possui contratos cadastrados.</p>
                <p class="text-sm mt-1">É necessário ter ao menos um contrato para registrar medições.</p>
                <a href="{{ route('contratos.create', ['obra_id' => $obra->id]) }}"
                    class="inline-block mt-3 text-sm px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                    ➕ Criar Contrato
                </a>
            </div>
        @else
            <div class="flex justify-center">
                <div class="w-full max-w-4xl">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                        <div class="px-8 py-6 border-b border-slate-200">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center text-xl">📊
                                </div>
                                <div>
                                    <h2 class="text-xl font-semibold text-slate-900">Nova Medição</h2>
                                    <p class="text-sm text-slate-600 mt-1">Registre o avanço financeiro desta obra</p>
                                </div>
                            </div>
                        </div>

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

                        <form method="POST" action="{{ route('obras.execucoes.store', $obra) }}"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="px-8 py-6 space-y-8">

                                {{-- 1. CONTRATO --}}
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4 flex items-center gap-2">
                                        <span
                                            class="w-5 h-5 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center font-bold">1</span>
                                        Contrato
                                    </h3>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Contrato *</label>
                                        <select name="contrato_id" required
                                            @change="selecionarContrato($event.target.value)"
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                            <option value="">— Selecione o contrato —</option>
                                            @foreach ($contratos as $c)
                                                <option value="{{ $c->id }}"
                                                    {{ old('contrato_id') == $c->id ? 'selected' : '' }}>
                                                    {{ $c->numero_contrato_ano ?? 'Contrato #' . $c->id }} —
                                                    {{ $c->empresa->nomeExibicao() }}
                                                    @if ($c->valor_contrato)
                                                        (R$ {{ number_format($c->valor_contrato, 2, ',', '.') }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div x-show="contratoSelecionado" x-cloak class="mt-4 grid grid-cols-3 gap-4">
                                        <div class="bg-slate-50 rounded-lg p-3 text-center border">
                                            <p class="text-xs text-slate-500 mb-1">Valor do contrato</p>
                                            <p class="font-semibold text-slate-800 text-sm"
                                                x-text="'R$ ' + fmt(contratoSelecionado?.valor_contrato ?? 0)"></p>
                                        </div>
                                        <div class="bg-green-50 rounded-lg p-3 text-center border border-green-200">
                                            <p class="text-xs text-slate-500 mb-1">Total já medido</p>
                                            <p class="font-semibold text-green-700 text-sm"
                                                x-text="'R$ ' + fmt(contratoSelecionado?.total_medido ?? 0)"></p>
                                        </div>
                                        <div class="rounded-lg p-3 text-center border"
                                            :class="(contratoSelecionado?.saldo ?? 1) <= 0 ? 'bg-red-50 border-red-200' :
                                                'bg-blue-50 border-blue-200'">
                                            <p class="text-xs text-slate-500 mb-1">Saldo disponível</p>
                                            <p class="font-semibold text-sm"
                                                :class="(contratoSelecionado?.saldo ?? 1) <= 0 ? 'text-red-700' :
                                                    'text-blue-700'"
                                                x-text="'R$ ' + fmt(contratoSelecionado?.saldo ?? 0)"></p>
                                        </div>
                                    </div>
                                </div>

                                {{-- 2. DADOS DA MEDIÇÃO --}}
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4 flex items-center gap-2">
                                        <span
                                            class="w-5 h-5 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center font-bold">2</span>
                                        Dados da Medição
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium">Data da Medição *</label>
                                            <input type="date" name="data_medicao"
                                                value="{{ old('data_medicao', date('Y-m-d')) }}" required
                                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium">Valor Medido (R$) *</label>
                                            <div class="relative">
                                                <span
                                                    class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm select-none">R$</span>
                                                <input type="text" x-ref="valorDisplay" placeholder="0,00" maxlength="16"
                                                    @input="mascaraMoeda($event)"
                                                    value="{{ old('valor_medido') ? number_format((float) old('valor_medido'), 2, ',', '.') : '' }}"
                                                    class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 text-right tabular-nums">
                                            </div>
                                            <input type="hidden" name="valor_medido" x-ref="valorHidden"
                                                value="{{ old('valor_medido') }}">
                                        </div>
                                    </div>

                                    {{-- PRÉVIA --}}
                                    <div x-show="contratoSelecionado && valorAtual > 0" x-cloak
                                        class="mt-5 bg-gradient-to-r from-slate-50 to-blue-50 border border-slate-200 rounded-xl p-5">
                                        <p class="text-sm font-semibold text-slate-600 mb-4">📊 Prévia após esta medição</p>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                            <div class="text-center">
                                                <p class="text-xs text-slate-500 mb-1">Esta medição</p>
                                                <p class="font-bold text-slate-800" x-text="'R$ '+fmt(valorAtual)"></p>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-xs text-slate-500 mb-1">Total acumulado</p>
                                                <p class="font-bold text-green-700"
                                                    x-text="'R$ '+fmt((contratoSelecionado?.total_medido??0)+valorAtual)">
                                                </p>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-xs text-slate-500 mb-1">Novo saldo</p>
                                                <p class="font-bold" :class="novoSaldo < 0 ? 'text-red-600' : 'text-blue-700'"
                                                    x-text="'R$ '+fmt(Math.max(novoSaldo,0))"></p>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-xs text-slate-500 mb-1">% Executado</p>
                                                <p class="font-bold text-indigo-700"
                                                    x-text="novoPercentual.toFixed(1)+'%'"></p>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <div class="w-full bg-slate-200 rounded-full h-2.5">
                                                <div class="h-2.5 rounded-full transition-all"
                                                    :class="novoPercentual >= 80 ? 'bg-green-500' : novoPercentual >= 40 ?
                                                        'bg-yellow-500' : 'bg-blue-600'"
                                                    :style="'width:' + Math.min(novoPercentual, 100) + '%'"></div>
                                            </div>
                                        </div>
                                        <div x-show="novoSaldo<0" x-cloak
                                            class="mt-3 bg-red-50 border border-red-300 text-red-700 px-3 py-2 rounded text-xs">
                                            ⚠️ Valor excede o saldo em R$ <span x-text="fmt(Math.abs(novoSaldo))"></span>.
                                        </div>
                                    </div>

                                    <div class="mt-4 space-y-2">
                                        <label class="text-sm font-medium">Observação</label>
                                        <textarea name="observacao" rows="3"
                                            placeholder="Número do boletim de medição, serviços executados, referências..."
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 resize-none">{{ old('observacao') }}</textarea>
                                    </div>
                                </div>

                                {{-- 3. RESPONSÁVEIS --}}
                                <div x-data="responsaveisManager()">
                                    <h3
                                        class="text-sm font-semibold text-slate-500 uppercase mb-4 flex items-center gap-2">
                                        <span
                                            class="w-5 h-5 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center font-bold">3</span>
                                        Responsáveis pela Medição
                                    </h3>

                                    <div class="space-y-2 mb-3">
                                        <template x-for="(resp, idx) in lista" :key="idx">
                                            <div
                                                class="flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5">
                                                <input type="hidden" :name="'responsaveis[' + idx + '][user_id]'"
                                                    :value="resp.user_id">
                                                <input type="hidden" :name="'responsaveis[' + idx + '][papel]'"
                                                    :value="resp.papel">
                                                <div class="flex-1 text-sm font-medium text-slate-800" x-text="resp.nome">
                                                </div>
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                                    :class="{ 'bg-blue-100 text-blue-700': resp.papel ===
                                                        'engenheiro', 'bg-green-100 text-green-700': resp.papel ===
                                                            'fiscal', 'bg-purple-100 text-purple-700': resp.papel ===
                                                            'supervisor' }"
                                                    x-text="papelLabel(resp.papel)"></span>
                                                <button type="button" @click="remover(idx)"
                                                    class="text-red-400 hover:text-red-600 text-xl leading-none">×</button>
                                            </div>
                                        </template>
                                        <p x-show="lista.length===0" class="text-sm text-slate-400 italic py-1">Nenhum
                                            responsável adicionado.</p>
                                    </div>

                                    <div
                                        class="flex flex-wrap gap-3 items-end bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <div class="flex-1 min-w-48 space-y-1">
                                            <label class="text-xs font-medium text-slate-600">Usuário</label>
                                            <select x-model="novoUserId"
                                                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                                                <option value="">— Selecione —</option>
                                                @foreach ($usuarios as $u)
                                                    <option value="{{ $u->id }}" data-nome="{{ $u->name }}">
                                                        {{ $u->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-xs font-medium text-slate-600">Papel</label>
                                            <select x-model="novoPapel"
                                                class="px-3 py-2 rounded-lg border border-slate-300 text-sm">
                                                <option value="engenheiro">Engenheiro</option>
                                                <option value="fiscal">Fiscal</option>
                                                <option value="supervisor">Supervisor</option>
                                            </select>
                                        </div>
                                        <button type="button" @click="adicionar()"
                                            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">➕
                                            Adicionar</button>
                                    </div>
                                </div>

                                {{-- 4. DOCUMENTOS --}}
                                <div x-data="documentosManager()">
                                    <h3
                                        class="text-sm font-semibold text-slate-500 uppercase mb-4 flex items-center gap-2">
                                        <span
                                            class="w-5 h-5 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center font-bold">4</span>
                                        Documentos Anexos
                                        <span class="text-xs font-normal text-slate-400 normal-case">(boletim, fotos,
                                            planilhas — opcional)</span>
                                    </h3>

                                    <div class="space-y-2 mb-3">
                                        <template x-for="(doc, idx) in docs" :key="idx">
                                            <div
                                                class="flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm">
                                                <span class="text-lg shrink-0" x-text="icone(doc.nome)"></span>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-medium text-slate-700 truncate" x-text="doc.nome"></p>
                                                    <p class="text-xs text-slate-400" x-text="tamanho(doc.tamanho)"></p>
                                                </div>
                                                <select :name="'arquivos_tipo[' + idx + ']'"
                                                    class="text-xs border border-slate-300 rounded px-2 py-1.5 shrink-0">
                                                    @foreach ($tiposDocumento as $key => $label)
                                                        <option value="{{ $key }}"
                                                            {{ $key === 'medicao' ? 'selected' : '' }}>{{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="text" :name="'arquivos_descricao[' + idx + ']'"
                                                    placeholder="Descrição..."
                                                    class="text-xs border border-slate-300 rounded px-2 py-1.5 w-36 shrink-0">
                                                <button type="button" @click="remover(idx)"
                                                    class="text-red-400 hover:text-red-600 text-xl leading-none shrink-0">×</button>
                                            </div>
                                        </template>
                                    </div>

                                    <input type="file" x-ref="fileInput" class="hidden" multiple
                                        accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.xlsx,.xls,.csv,.docx,.doc"
                                        name="arquivos[]" @change="adicionarArquivos($event)">

                                    <div @click="$refs.fileInput.click()" @dragover.prevent
                                        @drop.prevent="adicionarArquivosDrop($event)"
                                        class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition">
                                        <p class="text-slate-500 text-sm">📎 Clique ou arraste arquivos aqui</p>
                                        <p class="text-xs text-slate-400 mt-1">PDF, imagens, Excel, Word · Máx. 20
                                            MB/arquivo · Até 10 arquivos</p>
                                    </div>
                                </div>

                            </div>

                            <div class="px-8 py-5 border-t bg-slate-50 rounded-b-xl flex justify-between">
                                <a href="{{ route('obras.show', $obra) }}"
                                    class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-700 bg-white border">←
                                    Cancelar</a>
                                <button type="submit"
                                    class="px-6 py-2.5 rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700">✔
                                    Registrar Medição</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- MODAL AJUDA --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Registro de Medição</h3>
                </div>
                <div class="p-6 space-y-4 text-sm text-slate-700">
                    <p><strong>Contrato:</strong> O sistema exibe saldo disponível automaticamente.</p>
                    <p><strong>Valor:</strong> Prévia em tempo real de saldo e percentual.</p>
                    <p><strong>Responsáveis:</strong> Engenheiro, fiscal e/ou supervisor — essencial para auditoria.</p>
                    <p><strong>Documentos:</strong> Boletim assinado, fotos da obra, planilha de serviços. PDF, imagens,
                        Excel, Word.</p>
                    <div class="bg-slate-50 p-4 rounded text-xs">💡 Saldo e % são calculados automaticamente.</div>
                </div>
                <div class="px-6 py-4 border-t flex justify-end">
                    <button @click="modalAjuda=false" class="px-5 py-2 bg-green-600 text-white rounded-lg">Entendi
                        👍</button>
                </div>
            </div>
        </div>

    </div>

    <script>
        function medicaoForm(saldos) {
            return {
                modalAjuda: false,
                contratoSelecionado: null,
                valorAtual: 0,
                get novoSaldo() {
                    if (!this.contratoSelecionado) return 0;
                    return (this.contratoSelecionado.valor_contrato ?? 0) - (this.contratoSelecionado.total_medido ??
                        0) - this.valorAtual;
                },
                get novoPercentual() {
                    if (!this.contratoSelecionado?.valor_contrato) return 0;
                    return Math.min(((this.contratoSelecionado.total_medido + this.valorAtual) / this
                        .contratoSelecionado.valor_contrato) * 100, 100);
                },
                selecionarContrato(id) {
                    this.contratoSelecionado = saldos[id] ?? null;
                },
                mascaraMoeda(e) {
                    let d = e.target.value.replace(/\D/g, '').slice(0, 13);
                    if (!d || d === '0') {
                        e.target.value = '';
                        this.$refs.valorHidden.value = '';
                        this.valorAtual = 0;
                        return;
                    }
                    const c = BigInt(d),
                        r = c / 100n,
                        ct = c % 100n,
                        rf = r.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'),
                        cf = ct.toString().padStart(2, '0');
                    e.target.value = `${rf},${cf}`;
                    this.$refs.valorHidden.value = `${r}.${cf}`;
                    this.valorAtual = Number(`${r}.${cf}`);
                },
                fmt(v) {
                    return Number(v || 0).toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            }
        }

        function responsaveisManager() {
            return {
                lista: [],
                novoUserId: '',
                novoPapel: 'engenheiro',
                adicionar() {
                    if (!this.novoUserId) return;
                    if (this.lista.some(r => r.user_id == this.novoUserId)) {
                        alert('Já adicionado.');
                        return;
                    }
                    const sel = document.querySelector(`option[value="${this.novoUserId}"][data-nome]`);
                    this.lista.push({
                        user_id: this.novoUserId,
                        papel: this.novoPapel,
                        nome: sel?.dataset.nome ?? ''
                    });
                    this.novoUserId = '';
                },
                remover(i) {
                    this.lista.splice(i, 1);
                },
                papelLabel(p) {
                    return {
                        engenheiro: 'Engenheiro',
                        fiscal: 'Fiscal',
                        supervisor: 'Supervisor'
                    } [p] ?? p;
                }
            }
        }

        function documentosManager() {
            return {
                docs: [],
                adicionarArquivos(e) {
                    this._proc(e.target.files);
                    e.target.value = '';
                },
                adicionarArquivosDrop(e) {
                    this._proc(e.dataTransfer.files);
                },
                _proc(files) {
                    for (const f of files) {
                        if (this.docs.length >= 10) {
                            alert('Máximo 10 arquivos.');
                            break;
                        }
                        if (f.size > 20 * 1024 * 1024) {
                            alert(`${f.name} excede 20 MB.`);
                            continue;
                        }
                        this.docs.push({
                            nome: f.name,
                            tamanho: f.size
                        });
                    }
                    const dt = new DataTransfer(),
                        inp = this.$refs.fileInput;
                    if (inp.files)
                        for (const f of inp.files) dt.items.add(f);
                    for (const f of files) dt.items.add(f);
                    inp.files = dt.files;
                },
                remover(i) {
                    this.docs.splice(i, 1);
                    const dt = new DataTransfer(),
                        inp = this.$refs.fileInput;
                    Array.from(inp.files).forEach((f, j) => {
                        if (j !== i) dt.items.add(f);
                    });
                    inp.files = dt.files;
                },
                icone(n) {
                    const e = n.split('.').pop().toLowerCase();
                    return {
                        pdf: '📄',
                        jpg: '🖼️',
                        jpeg: '🖼️',
                        png: '🖼️',
                        gif: '🖼️',
                        webp: '🖼️',
                        xlsx: '📊',
                        xls: '📊',
                        csv: '📊',
                        docx: '📝',
                        doc: '📝'
                    } [e] ?? '📎';
                },
                tamanho(b) {
                    return b >= 1048576 ? (b / 1048576).toFixed(1) + ' MB' : b >= 1024 ? Math.round(b / 1024) + ' KB' : b +
                        ' bytes';
                }
            }
        }
    </script>

@endsection
