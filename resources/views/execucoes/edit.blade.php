@extends('layouts.app')

@section('title', 'Editar Medição')
@section('subtitle', 'Atualização de medição de obra')

@section('content')

    <div x-data="medicaoForm({{ $saldosPorContrato->toJson() }}, {{ (float)$execucao->valor_medido }}, {{ $execucao->contrato_id }})">

        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <a href="{{ route('obras.index') }}" class="hover:text-slate-900">Obras</a>
                <span>›</span>
                <a href="{{ route('obras.show', $obra) }}" class="hover:text-slate-900">{{ Str::limit($obra->descricao, 40) }}</a>
                <span>›</span>
                <span class="text-slate-900 font-medium">Editar Medição</span>
            </div>
            <button type="button" @click="modalAjuda=true" class="px-4 py-2 text-sm bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 border border-amber-200">❓ Ajuda</button>
        </div>

        <div class="mb-6">
            <a href="{{ route('obras.show', $obra) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-100 transition">← Voltar à Obra</a>
        </div>

        {{-- AVISO --}}
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-sm text-amber-800">
            ⚠️ Editando medição de <strong>{{ $execucao->data_medicao->format('d/m/Y') }}</strong>
            — R$ <strong>{{ number_format($execucao->valor_medido, 2, ',', '.') }}</strong>.
            Saldo e % serão recalculados automaticamente.
        </div>

        <div class="flex justify-center">
            <div class="w-full max-w-4xl">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                    <div class="px-8 py-6 border-b">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center text-xl">✏️</div>
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">Editar Medição</h2>
                                <p class="text-sm text-slate-600 mt-1">{{ $obra->descricao }}</p>
                            </div>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="mx-8 mt-6 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg">
                            <strong>Erro ao salvar:</strong>
                            <ul class="mt-2 text-sm list-disc ml-6">
                                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('obras.execucoes.update', [$obra, $execucao]) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="retorno" value="{{ old('retorno', request('retorno')) }}">

                        <div class="px-8 py-6 space-y-8">

                            {{-- 1. CONTRATO --}}
                            <div>
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4 flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-amber-500 text-white text-xs flex items-center justify-center font-bold">1</span>
                                    Contrato
                                </h3>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Contrato *</label>
                                    <select name="contrato_id" required @change="selecionarContrato($event.target.value)"
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                        <option value="">— Selecione —</option>
                                        @foreach ($contratos as $c)
                                            <option value="{{ $c->id }}" {{ old('contrato_id',$execucao->contrato_id)==$c->id?'selected':'' }}>
                                                {{ $c->numero_contrato_ano??'Contrato #'.$c->id }} — {{ $c->empresa->nomeExibicao() }}
                                                @if($c->valor_contrato) (R$ {{ number_format($c->valor_contrato,2,',','.') }}) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div x-show="contratoSelecionado" x-cloak class="mt-4 grid grid-cols-3 gap-4">
                                    <div class="bg-slate-50 rounded-lg p-3 text-center border">
                                        <p class="text-xs text-slate-500 mb-1">Valor do contrato</p>
                                        <p class="font-semibold text-slate-800 text-sm" x-text="'R$ '+fmt(contratoSelecionado?.valor_contrato??0)"></p>
                                    </div>
                                    <div class="bg-green-50 rounded-lg p-3 text-center border border-green-200">
                                        <p class="text-xs text-slate-500 mb-1">Total (excl. esta)</p>
                                        <p class="font-semibold text-green-700 text-sm" x-text="'R$ '+fmt(contratoSelecionado?.total_medido??0)"></p>
                                    </div>
                                    <div class="bg-blue-50 rounded-lg p-3 text-center border border-blue-200">
                                        <p class="text-xs text-slate-500 mb-1">Saldo disponível</p>
                                        <p class="font-semibold text-blue-700 text-sm" x-text="'R$ '+fmt(contratoSelecionado?.saldo??0)"></p>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. DADOS --}}
                            <div>
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4 flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-amber-500 text-white text-xs flex items-center justify-center font-bold">2</span>
                                    Dados da Medição
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Data da Medição *</label>
                                        <input type="date" name="data_medicao"
                                            value="{{ old('data_medicao',$execucao->data_medicao->format('Y-m-d')) }}" required
                                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Valor Medido (R$) *</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm select-none">R$</span>
                                            <input type="text" x-ref="valorDisplay" placeholder="0,00" maxlength="16"
                                                @input="mascaraMoeda($event)"
                                                value="{{ old('valor_medido', number_format((float)$execucao->valor_medido,2,',','.')) }}"
                                                class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 text-right tabular-nums">
                                        </div>
                                        <input type="hidden" name="valor_medido" x-ref="valorHidden"
                                            value="{{ old('valor_medido', number_format((float)$execucao->valor_medido,2,'.','' )) }}">
                                    </div>
                                </div>

                                {{-- PRÉVIA --}}
                                <div x-show="contratoSelecionado && valorAtual>0" x-cloak
                                    class="mt-5 bg-gradient-to-r from-slate-50 to-amber-50 border border-slate-200 rounded-xl p-5">
                                    <p class="text-sm font-semibold text-slate-600 mb-4">📊 Prévia após atualização</p>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                        <div class="text-center"><p class="text-xs text-slate-500 mb-1">Este valor</p><p class="font-bold text-slate-800" x-text="'R$ '+fmt(valorAtual)"></p></div>
                                        <div class="text-center"><p class="text-xs text-slate-500 mb-1">Total acumulado</p><p class="font-bold text-green-700" x-text="'R$ '+fmt((contratoSelecionado?.total_medido??0)+valorAtual)"></p></div>
                                        <div class="text-center"><p class="text-xs text-slate-500 mb-1">Novo saldo</p><p class="font-bold" :class="novoSaldo<0?'text-red-600':'text-blue-700'" x-text="'R$ '+fmt(Math.max(novoSaldo,0))"></p></div>
                                        <div class="text-center"><p class="text-xs text-slate-500 mb-1">% Executado</p><p class="font-bold text-indigo-700" x-text="novoPercentual.toFixed(1)+'%'"></p></div>
                                    </div>
                                    <div class="mt-4"><div class="w-full bg-slate-200 rounded-full h-2.5"><div class="h-2.5 rounded-full transition-all" :class="novoPercentual>=80?'bg-green-500':novoPercentual>=40?'bg-yellow-500':'bg-blue-600'" :style="'width:'+Math.min(novoPercentual,100)+'%'"></div></div></div>
                                </div>

                                <div class="mt-4 space-y-2">
                                    <label class="text-sm font-medium">Observação</label>
                                    <textarea name="observacao" rows="3"
                                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 resize-none">{{ old('observacao', $execucao->observacao) }}</textarea>
                                </div>
                            </div>

                            {{-- 3. RESPONSÁVEIS --}}
                            <div x-data="responsaveisManager({{ $responsaveisAtuais->toJson() }}, {{ collect($usuarios)->map(fn($u)=>['id'=>$u->id,'name'=>$u->name])->toJson() }})">
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4 flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-amber-500 text-white text-xs flex items-center justify-center font-bold">3</span>
                                    Responsáveis pela Medição
                                </h3>

                                <div class="space-y-2 mb-3">
                                    <template x-for="(resp, idx) in lista" :key="idx">
                                        <div class="flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5">
                                            <input type="hidden" :name="'responsaveis['+idx+'][user_id]'" :value="resp.user_id">
                                            <input type="hidden" :name="'responsaveis['+idx+'][papel]'" :value="resp.papel">
                                            <div class="flex-1 text-sm font-medium text-slate-800" x-text="resp.nome"></div>
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                                :class="{'bg-blue-100 text-blue-700':resp.papel==='engenheiro','bg-green-100 text-green-700':resp.papel==='fiscal','bg-purple-100 text-purple-700':resp.papel==='supervisor'}"
                                                x-text="papelLabel(resp.papel)"></span>
                                            <button type="button" @click="remover(idx)" class="text-red-400 hover:text-red-600 text-xl leading-none">×</button>
                                        </div>
                                    </template>
                                    <p x-show="lista.length===0" class="text-sm text-slate-400 italic py-1">Nenhum responsável.</p>
                                </div>

                                <div class="flex flex-wrap gap-3 items-end bg-amber-50 border border-amber-200 rounded-lg p-4">
                                    <div class="flex-1 min-w-48 space-y-1">
                                        <label class="text-xs font-medium text-slate-600">Usuário</label>
                                        <select x-model="novoUserId" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                                            <option value="">— Selecione —</option>
                                            @foreach ($usuarios as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-medium text-slate-600">Papel</label>
                                        <select x-model="novoPapel" class="px-3 py-2 rounded-lg border border-slate-300 text-sm">
                                            <option value="engenheiro">Engenheiro</option>
                                            <option value="fiscal">Fiscal</option>
                                            <option value="supervisor">Supervisor</option>
                                        </select>
                                    </div>
                                    <button type="button" @click="adicionar()" class="px-4 py-2 text-sm bg-amber-600 text-white rounded-lg hover:bg-amber-700">➕ Adicionar</button>
                                </div>
                            </div>

                            {{-- 4. DOCUMENTOS EXISTENTES --}}
                            @if ($execucao->documentos->isNotEmpty())
                            <div>
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4 flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-amber-500 text-white text-xs flex items-center justify-center font-bold">4</span>
                                    Documentos Já Anexados ({{ $execucao->documentos->count() }})
                                </h3>
                                <div class="space-y-2">
                                    @foreach ($execucao->documentos as $doc)
                                        <div class="flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm">
                                            <span class="text-lg">
                                                @php $ext = strtolower(pathinfo($doc->nome_original, PATHINFO_EXTENSION)); @endphp
                                                @if($ext==='pdf') 📄
                                                @elseif(in_array($ext,['jpg','jpeg','png','gif','webp'])) 🖼️
                                                @elseif(in_array($ext,['xlsx','xls','csv'])) 📊
                                                @elseif(in_array($ext,['docx','doc'])) 📝
                                                @else 📎
                                                @endif
                                            </span>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-slate-700 truncate">{{ $doc->descricao ?? $doc->nome_original }}</p>
                                                <p class="text-xs text-slate-400">
                                                    {{ $doc->tipo_label }} · {{ $doc->tamanho_legível }} · {{ $doc->created_at->format('d/m/Y H:i') }}
                                                </p>
                                            </div>
                                            <a href="{{ route('obras.execucoes.documentos.download', [$obra, $execucao, $doc]) }}"
                                                class="px-3 py-1.5 text-xs rounded border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100 shrink-0">
                                                ⬇️ Baixar
                                            </a>
                                            @if (auth()->user()->perfil === 'admin')
                                                <button type="submit" form="remover-doc-{{ $doc->id }}"
                                                    onclick="return confirm('Remover este documento? A remoção será registrada na auditoria.')"
                                                    class="px-2 py-1.5 text-xs rounded border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 shrink-0">
                                                    🗑
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            {{-- 5. NOVOS DOCUMENTOS --}}
                            <div x-data="{ docs: [] }">
                                <h3 class="text-sm font-semibold text-slate-500 uppercase mb-4 flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-amber-500 text-white text-xs flex items-center justify-center font-bold">
                                        {{ $execucao->documentos->isNotEmpty() ? '5' : '4' }}
                                    </span>
                                    Adicionar Novos Documentos
                                    <span class="text-xs font-normal text-slate-400 normal-case">(opcional)</span>
                                </h3>

                                <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:border-amber-400 hover:bg-amber-50 transition">
                                    <p class="text-slate-500 text-sm mb-3">📎 Selecione os arquivos a anexar</p>
                                    <input type="file"
                                        name="arquivos[]"
                                        multiple
                                        accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.xlsx,.xls,.csv,.docx,.doc"
                                        @change="docs = Array.from($event.target.files).map(f => ({nome: f.name, tamanho: f.size}))"
                                        class="block w-full text-sm text-slate-600
                                               file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                                               file:text-sm file:font-medium file:bg-amber-50 file:text-amber-700
                                               hover:file:bg-amber-100 cursor-pointer">
                                    <p class="text-xs text-slate-400 mt-2">PDF, imagens, Excel, Word · Máx. 20 MB/arquivo</p>
                                </div>

                                <div x-show="docs.length > 0" x-cloak class="mt-4 space-y-3">
                                    <template x-for="(doc, idx) in docs" :key="idx">
                                        <div class="flex flex-wrap gap-3 items-center bg-slate-50 border border-slate-200 rounded-lg px-4 py-3">
                                            <span class="text-sm font-medium text-slate-700 flex-1 min-w-32" x-text="doc.nome"></span>
                                            <select :name="'arquivos_tipo['+idx+']'"
                                                class="text-xs border border-slate-300 rounded px-2 py-1.5">
                                                @foreach ($tiposDocumento as $key => $label)
                                                    <option value="{{ $key }}" {{ $key==='medicao'?'selected':'' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" :name="'arquivos_descricao['+idx+']'"
                                                placeholder="Descrição (opcional)"
                                                class="text-xs border border-slate-300 rounded px-2 py-1.5 w-44">
                                        </div>
                                    </template>
                                </div>
                            </div>

                        </div>

                        {{-- MOTIVO DA CORREÇÃO (vai para a auditoria) --}}
                        <div class="px-8 pb-6">
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 space-y-2">
                                <label for="motivo" class="text-sm font-semibold text-amber-900">
                                    Motivo da correção <span class="text-red-600">*</span>
                                </label>
                                <textarea id="motivo" name="motivo" rows="2" required minlength="10" maxlength="1000"
                                    placeholder="Ex.: valor lançado com erro de digitação — correto conforme boletim nº 3"
                                    class="w-full px-4 py-2.5 rounded-lg border focus:ring-2 focus:ring-amber-500 resize-none {{ $errors->has('motivo') ? 'border-red-400' : 'border-slate-300' }}">{{ old('motivo') }}</textarea>
                                @error('motivo')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                                <p class="text-xs text-amber-800">
                                    🔒 A alteração (valores antes/depois), o usuário e este motivo ficam registrados no Histórico de Atividades.
                                </p>
                            </div>
                        </div>

                        <div class="px-8 py-5 border-t bg-slate-50 rounded-b-xl flex justify-between">
                            <a href="{{ old('retorno', request('retorno')) === 'contrato' ? route('contratos.show', $execucao->contrato_id) : route('obras.show', $obra) }}" class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-700 bg-white border">← Cancelar</a>
                            <button type="submit" class="px-6 py-2.5 rounded-lg text-sm font-medium text-white bg-amber-600 hover:bg-amber-700">✔ Atualizar Medição</button>
                        </div>
                    </form>

                    @if (auth()->user()->perfil === 'admin')
                        @foreach ($execucao->documentos as $doc)
                            <form id="remover-doc-{{ $doc->id }}" method="POST" class="hidden"
                                action="{{ route('obras.execucoes.documentos.destroy', [$obra, $execucao, $doc]) }}">
                                @csrf @method('DELETE')
                            </form>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        {{-- MODAL AJUDA --}}
        <div x-show="modalAjuda" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl overflow-hidden">
                <div class="bg-gradient-to-r from-amber-500 to-orange-500 text-white px-6 py-4">
                    <h3 class="text-lg font-semibold">📘 Ajuda — Edição de Medição</h3>
                </div>
                <div class="p-6 space-y-3 text-sm text-slate-700">
                    <p>Saldo e percentual são recalculados automaticamente excluindo o valor atual desta medição.</p>
                    <p>Documentos existentes podem ser baixados ou removidos (somente admin). Novos documentos são adicionados na seção inferior.</p>
                    <p>Responsáveis são substituídos pelos novos selecionados.</p>
                </div>
                <div class="px-6 py-4 border-t flex justify-end">
                    <button @click="modalAjuda=false" class="px-5 py-2 bg-amber-600 text-white rounded-lg">Entendi 👍</button>
                </div>
            </div>
        </div>

    </div>

    <script>
        function medicaoForm(saldos, valorInicial, contratoIdInicial) {
            return {
                modalAjuda:false, contratoSelecionado:saldos[contratoIdInicial]??null, valorAtual:valorInicial,
                get novoSaldo(){if(!this.contratoSelecionado)return 0;return(this.contratoSelecionado.valor_contrato??0)-(this.contratoSelecionado.total_medido??0)-this.valorAtual;},
                get novoPercentual(){if(!this.contratoSelecionado?.valor_contrato)return 0;return Math.min(((this.contratoSelecionado.total_medido+this.valorAtual)/this.contratoSelecionado.valor_contrato)*100,100);},
                selecionarContrato(id){this.contratoSelecionado=saldos[id]??null;},
                mascaraMoeda(e){
                    let d=e.target.value.replace(/\D/g,'').slice(0,13);
                    if(!d||d==='0'){e.target.value='';this.$refs.valorHidden.value='';this.valorAtual=0;return;}
                    const c=BigInt(d),r=c/100n,ct=c%100n,rf=r.toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.'),cf=ct.toString().padStart(2,'0');
                    e.target.value=`${rf},${cf}`;this.$refs.valorHidden.value=`${r}.${cf}`;this.valorAtual=Number(`${r}.${cf}`);
                },
                fmt(v){return Number(v||0).toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2});}
            }
        }
        function responsaveisManager(atuais, todosUsuarios) {
            // atuais: { user_id: papel }
            const lista = Object.entries(atuais).map(([uid, papel]) => {
                const u = todosUsuarios.find(x => x.id == uid);
                return { user_id: uid, papel, nome: u?.name ?? '' };
            });
            return {
                lista, novoUserId:'', novoPapel:'engenheiro', todosUsuarios,
                adicionar(){
                    if(!this.novoUserId)return;
                    if(this.lista.some(r=>r.user_id==this.novoUserId)){alert('Já adicionado.');return;}
                    const u=this.todosUsuarios.find(x=>x.id==this.novoUserId);
                    this.lista.push({user_id:this.novoUserId,papel:this.novoPapel,nome:u?.name??''});
                    this.novoUserId='';
                },
                remover(i){this.lista.splice(i,1);},
                papelLabel(p){return{engenheiro:'Engenheiro',fiscal:'Fiscal',supervisor:'Supervisor'}[p]??p;}
            }
        }
    </script>

@endsection