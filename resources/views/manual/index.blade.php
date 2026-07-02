@extends('layouts.app')

@section('title', 'Manual do Sistema')
@section('subtitle', 'Como usar o Sistema de Obras')

@section('content')

    <div x-data="manual('{{ $perfil }}')" x-init="init()" class="max-w-4xl mx-auto">

        {{-- CABEÇALHO --}}
        <div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">📘 Manual do Sistema</h1>
                <p class="text-slate-500 text-sm mt-1">
                    Oi, <strong>{{ $nome }}</strong>! Vamos aprender juntos a usar o sistema. É mais fácil do que
                    parece. 😊
                </p>
            </div>
            <span
                class="shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold
            @if ($perfil === 'admin') bg-red-100 text-red-700
            @elseif($perfil === 'tecnico') bg-blue-100 text-blue-700
            @elseif($perfil === 'secretario') bg-indigo-100 text-indigo-700
            @else bg-slate-100 text-slate-600 @endif">
                @if ($perfil === 'admin')
                    🔑 Administrador
                @elseif($perfil === 'tecnico')
                    🔧 Técnico
                @elseif($perfil === 'secretario')
                    📋 Secretário
                @else
                    👁 Operador
                @endif
            </span>
        </div>

        {{-- PROGRESSO --}}
        <div class="mb-2 flex justify-between text-xs text-slate-500">
            <span>Passo <span x-text="etapaAtual+1"></span> de <span x-text="etapasFiltradas.length"></span></span>
            <span x-text="Math.round(((etapaAtual+1)/etapasFiltradas.length)*100)+'% concluído'"></span>
        </div>
        <div class="w-full bg-slate-200 rounded-full h-2.5 mb-6">
            <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500"
                :style="'width:' + Math.round(((etapaAtual + 1) / etapasFiltradas.length) * 100) + '%'"></div>
        </div>

        {{-- ÍNDICE --}}
        <div class="mb-6 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <button @click="mostrarIndice=!mostrarIndice"
                class="w-full px-5 py-3 flex items-center justify-between text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                <span>📑 Ver todos os módulos</span>
                <span class="text-slate-400 text-xs" x-text="mostrarIndice?'▲':'▼'"></span>
            </button>
            <div x-show="mostrarIndice" x-cloak class="border-t border-slate-100">
                <template x-for="(e,i) in etapasFiltradas" :key="i">
                    <button @click="irPara(i);mostrarIndice=false"
                        class="w-full text-left px-5 py-2.5 text-sm flex items-center gap-3 hover:bg-slate-50 transition border-b border-slate-50 last:border-0"
                        :class="i === etapaAtual ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600'">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                            :class="i < etapaAtual ? 'bg-green-100 text-green-700' : (i === etapaAtual ?
                                'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500')">
                            <span x-show="i<etapaAtual">✓</span>
                            <span x-show="i>=etapaAtual" x-text="i+1"></span>
                        </span>
                        <span x-text="e.titulo"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- CARD DO MÓDULO --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- Cabeçalho colorido --}}
            <div class="px-8 py-6 border-b border-slate-100"
                :class="{
                    'bg-gradient-to-r from-blue-600 to-indigo-600': etapasFiltradas[etapaAtual]?.cor==='blue',
                    'bg-gradient-to-r from-green-600 to-teal-600': etapasFiltradas[etapaAtual]?.cor==='green',
                    'bg-gradient-to-r from-amber-500 to-orange-500': etapasFiltradas[etapaAtual]?.cor==='amber',
                    'bg-gradient-to-r from-indigo-600 to-violet-600': etapasFiltradas[etapaAtual]?.cor==='indigo',
                    'bg-gradient-to-r from-slate-600 to-slate-700': etapasFiltradas[etapaAtual]?.cor==='slate',
                    'bg-gradient-to-r from-red-600 to-rose-600': etapasFiltradas[etapaAtual]?.cor==='red',
                    'bg-gradient-to-r from-teal-600 to-cyan-600': etapasFiltradas[etapaAtual]?.cor==='teal',
                    'bg-gradient-to-r from-purple-600 to-pink-600': etapasFiltradas[etapaAtual]?.cor==='purple',
                }">
                <div class="flex items-center gap-4">
                    <span class="text-4xl" x-text="etapasFiltradas[etapaAtual]?.icone"></span>
                    <div>
                        <p class="text-xs text-white/70 uppercase tracking-widest font-medium"
                            x-text="'Módulo '+(etapaAtual+1)+' de '+etapasFiltradas.length"></p>
                        <h2 class="text-xl font-bold text-white mt-0.5" x-text="etapasFiltradas[etapaAtual]?.titulo"></h2>
                    </div>
                </div>
            </div>

            {{-- Conteúdo --}}
            <div class="px-8 py-6" x-html="etapasFiltradas[etapaAtual]?.conteudo"></div>

            {{-- Navegação --}}
            <div class="px-8 py-5 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                <button @click="anterior()" x-show="etapaAtual>0"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-medium hover:bg-slate-100 transition">
                    ← Voltar
                </button>
                <div x-show="etapaAtual===0"></div>

                <div class="flex items-center gap-3">
                    <div class="flex gap-1.5">
                        <template x-for="(e,i) in etapasFiltradas" :key="i">
                            <button @click="irPara(i)" class="h-2 rounded-full transition-all"
                                :class="i === etapaAtual ? 'bg-blue-600 w-4' : (i < etapaAtual ? 'bg-green-400 w-2' :
                                    'bg-slate-300 w-2')">
                            </button>
                        </template>
                    </div>
                    <button @click="proximo()" x-show="etapaAtual<etapasFiltradas.length-1"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">
                        Próximo →
                    </button>
                    <div x-show="etapaAtual===etapasFiltradas.length-1">
                        <a href="{{ route('dashboard') }}"
                            class="flex items-center gap-2 px-5 py-2.5 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700 transition">
                            ✅ Ir para o sistema!
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-slate-400 mt-4">
            Clique nos pontinhos ou no índice para pular direto para qualquer módulo.
        </p>

    </div>

    <script>
        function manual(perfil) {
            return {
                perfil,
                etapaAtual: 0,
                mostrarIndice: false,
                etapasFiltradas: [],

                init() {
                    this.etapasFiltradas = this.modulos().filter(m =>
                        m.perfis.includes('todos') || m.perfis.includes(perfil)
                    );
                },
                irPara(i) {
                    this.etapaAtual = i;
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                },
                proximo() {
                    if (this.etapaAtual < this.etapasFiltradas.length - 1) this.irPara(this.etapaAtual + 1);
                },
                anterior() {
                    if (this.etapaAtual > 0) this.irPara(this.etapaAtual - 1);
                },

                modulos() {
                    return [

                        // ═══════════════════════════════════════════════════════
                        // MÓDULO 1 — BEM-VINDO
                        // ═══════════════════════════════════════════════════════
                        {
                            titulo: 'Seja bem-vindo ao Sistema de Obras!',
                            icone: '🏛️',
                            cor: 'blue',
                            perfis: ['todos'],
                            conteudo: `
<div class="space-y-5 text-slate-700 text-sm leading-relaxed">

  <p>Esse sistema foi feito pra centralizar tudo sobre as obras da prefeitura em um único lugar. Chega de planilha espalhada, e-mail perdido ou pasta difícil de achar!</p>

  <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
    <p class="font-semibold text-blue-800 mb-2">Com ele você consegue:</p>
    <ul class="space-y-1.5 text-blue-700 text-sm">
      <li>✔ Ver o andamento de qualquer obra do município</li>
      <li>✔ Saber quanto foi gasto e quanto ainda falta executar</li>
      <li>✔ Acompanhar se algum contrato tá perto de vencer</li>
      <li>✔ Guardar e acessar documentos de qualquer computador</li>
      <li>✔ Gerar relatórios em PDF ou Excel com poucos cliques</li>
      <li>✔ Acompanhar processos administrativos (alvarás, certidões) e saber em qual fase cada um está</li>
    </ul>
  </div>

  <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-xl">
    <p class="font-semibold text-indigo-800 mb-1">🗂️ Duas frentes num sistema só</p>
    <p class="text-indigo-700 text-sm">Além das <strong>Obras</strong>, o sistema também tem o módulo de <strong>Processos Administrativos</strong> (licenciamento — alvarás, certidões, ligações de água/energia). São coisas diferentes: obra é investimento público, processo é licenciamento urbano. Cada um tem seu próprio conjunto de módulos neste manual.</p>
  </div>

  <p class="font-semibold text-slate-800">Quem usa o sistema e o que cada um pode fazer:</p>

  <div class="grid grid-cols-2 gap-3">
    <div class="p-4 bg-red-50 rounded-xl border border-red-200">
      <p class="text-lg mb-1">🔑</p>
      <p class="font-semibold text-slate-800">Administrador</p>
      <p class="text-xs text-slate-500 mt-1">Faz tudo. Cria usuários, apaga registros, acessa relatórios e configura o sistema.</p>
    </div>
    <div class="p-4 bg-blue-50 rounded-xl border border-blue-200">
      <p class="text-lg mb-1">🔧</p>
      <p class="font-semibold text-slate-800">Técnico</p>
      <p class="text-xs text-slate-500 mt-1">Cadastra obras, contratos, medições e faz upload de documentos.</p>
    </div>
    <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-200">
      <p class="text-lg mb-1">📋</p>
      <p class="font-semibold text-slate-800">Secretário</p>
      <p class="text-xs text-slate-500 mt-1">Mesmas permissões do técnico. Também pode ser listado como responsável em medições.</p>
    </div>
    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
      <p class="text-lg mb-1">👁</p>
      <p class="font-semibold text-slate-800">Operador</p>
      <p class="text-xs text-slate-500 mt-1">Vê tudo, mas não altera nada. Ideal pra quem só precisa consultar.</p>
    </div>
  </div>

  <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-800">
    <p class="font-semibold mb-1">💡 Uma coisa importante</p>
    <p class="text-sm">Se você não ver algum botão ou opção de menu, não se preocupe — ele simplesmente não está disponível pro seu perfil. Cada perfil só vê o que precisa usar.</p>
  </div>
</div>
`,
                        },

                        // ═══════════════════════════════════════════════════════
                        // MÓDULO 2 — A INTERFACE
                        // ═══════════════════════════════════════════════════════
                        {
                            titulo: 'Como o sistema está organizado',
                            icone: '🖥️',
                            cor: 'slate',
                            perfis: ['todos'],
                            conteudo: `
<div class="space-y-5 text-slate-700 text-sm leading-relaxed">

  <p>Antes de sair clicando em tudo, deixa eu te mostrar como a tela está organizada. É bem simples!</p>

  {{-- Ilustração da tela --}}
  <div class="rounded-xl border-2 border-slate-200 overflow-hidden text-xs shadow-sm">
    <div class="bg-slate-800 text-white px-4 py-2.5 flex items-center gap-2">
      <span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span>
      <span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span>
      <span class="w-3 h-3 rounded-full bg-green-400 inline-block"></span>
      <span class="ml-3 text-slate-400 text-xs">Sistema de Obras — Prefeitura de Rio Grande da Serra</span>
    </div>
    <div class="flex bg-white" style="min-height:180px">
      {{-- Menu lateral --}}
      <div class="bg-slate-900 text-slate-300 px-3 py-4 w-44 shrink-0 text-xs space-y-1">
        <div class="text-slate-500 text-xs uppercase tracking-wider mb-3 px-2">Menu</div>
        <div class="flex items-center gap-2 text-white bg-blue-600 rounded-lg px-2 py-1.5">📊 Dashboard</div>
        <div class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-700 rounded-lg cursor-pointer">🏗️ Obras</div>
        <div class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-700 rounded-lg cursor-pointer">🤝 Convênios</div>
        <div class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-700 rounded-lg cursor-pointer">📋 Contratos</div>
        <div class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-700 rounded-lg cursor-pointer">🗂️ Processos</div>
        <div class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-700 rounded-lg cursor-pointer">📊 Relatórios</div>
        <div class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-700 rounded-lg cursor-pointer">📘 Manual</div>
        <div class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-700 rounded-lg cursor-pointer">⚙️ Admin</div>
      </div>
      {{-- Conteúdo principal --}}
      <div class="flex-1 bg-slate-100 p-3 space-y-2">
        <div class="flex justify-between items-center mb-1">
          <div class="h-3 bg-slate-400 rounded w-24"></div>
          <div class="h-6 w-20 bg-blue-500 rounded text-white text-xs flex items-center justify-center">➕ Novo</div>
        </div>
        <div class="bg-white rounded-lg p-2 border border-slate-200 shadow-sm">
          <div class="grid grid-cols-4 gap-2 mb-2">
            <div class="h-12 bg-blue-50 rounded border border-blue-100 flex items-center justify-center text-xs text-blue-500">KPI</div>
            <div class="h-12 bg-green-50 rounded border border-green-100 flex items-center justify-center text-xs text-green-500">KPI</div>
            <div class="h-12 bg-amber-50 rounded border border-amber-100 flex items-center justify-center text-xs text-amber-500">KPI</div>
            <div class="h-12 bg-indigo-50 rounded border border-indigo-100 flex items-center justify-center text-xs text-indigo-500">KPI</div>
          </div>
          <div class="h-2 bg-slate-100 rounded mb-1"></div>
          <div class="h-2 bg-slate-100 rounded w-3/4"></div>
        </div>
        <div class="bg-white rounded-lg p-2 border border-slate-200 shadow-sm h-16 flex items-center justify-center text-slate-300 text-xs">Tabela / gráficos</div>
      </div>
    </div>
  </div>

  <div class="space-y-3">
    <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
      <div class="w-8 h-8 bg-slate-700 rounded-lg flex items-center justify-center shrink-0">
        <span class="text-white text-xs">☰</span>
      </div>
      <div>
        <p class="font-semibold text-slate-800">Menu lateral — sempre à esquerda</p>
        <p class="text-slate-500 text-xs mt-0.5">É por aqui que você navega entre os módulos. O item em azul indica onde você está agora.</p>
      </div>
    </div>

    <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
      <div class="flex gap-1 shrink-0 items-center">
        <span class="px-2 py-1 bg-blue-600 text-white text-xs rounded font-medium">Azul</span>
        <span class="px-2 py-1 bg-green-600 text-white text-xs rounded font-medium">Verde</span>
        <span class="px-2 py-1 bg-red-600 text-white text-xs rounded font-medium">Vermelho</span>
      </div>
      <div>
        <p class="font-semibold text-slate-800">Cor dos botões</p>
        <p class="text-slate-500 text-xs mt-0.5"><span class="text-blue-600 font-medium">Azul</span> = criar ou salvar · <span class="text-green-600 font-medium">Verde</span> = confirmar · <span class="text-red-600 font-medium">Vermelho</span> = apagar · Cinza = cancelar</p>
      </div>
    </div>

    <div class="flex gap-3 p-3 bg-amber-50 rounded-xl border border-amber-200">
      <span class="text-xl shrink-0">❓</span>
      <div>
        <p class="font-semibold text-amber-800">Botão de Ajuda — seu melhor amigo</p>
        <p class="text-amber-700 text-xs mt-0.5">Quase toda tela tem um botão <strong>❓ Ajuda</strong> no canto superior direito. Se travar em alguma tela, clique nele primeiro. As instruções são específicas pra cada situação.</p>
      </div>
    </div>

    <div class="flex gap-3 p-3 bg-green-50 rounded-xl border border-green-200">
      <span class="text-xl shrink-0">✅</span>
      <div>
        <p class="font-semibold text-green-800">Mensagens de confirmação</p>
        <p class="text-green-700 text-xs mt-0.5">Quando você salva alguma coisa, aparece uma faixa <span class="text-green-700 font-medium">verde</span> no topo confirmando. Se der algo errado, aparece em <span class="text-red-600 font-medium">vermelho</span> explicando o que aconteceu.</p>
      </div>
    </div>
  </div>
</div>
`,
                        },

                        // ═══════════════════════════════════════════════════════
                        // MÓDULO 3 — DASHBOARD
                        // ═══════════════════════════════════════════════════════
                        {
                            titulo: 'O Painel Principal',
                            icone: '📊',
                            cor: 'indigo',
                            perfis: ['todos'],
                            conteudo: `
<div class="space-y-5 text-slate-700 text-sm leading-relaxed">

  <p>Quando você faz login, cai direto no <strong>Painel Principal</strong> (também chamado de Dashboard). Pensa nele como o "resumo do dia" das obras do município.</p>

  {{-- Simulação do dashboard --}}
  <div class="rounded-xl border border-slate-200 overflow-hidden shadow-sm text-xs">
    <div class="bg-slate-50 border-b border-slate-200 px-4 py-2.5 flex items-center justify-between">
      <span class="font-semibold text-slate-700">Painel Principal</span>
      <div class="flex gap-2 text-slate-400">
        <span class="px-2 py-0.5 border border-slate-300 rounded">📅 Período</span>
      </div>
    </div>
    <div class="p-4 bg-white space-y-3">
      <div class="grid grid-cols-4 gap-2">
        <div class="p-3 bg-blue-50 rounded-lg border border-blue-100 text-center">
          <div class="text-xl font-bold text-blue-700">14</div>
          <div class="text-blue-500 text-xs mt-0.5">Obras</div>
        </div>
        <div class="p-3 bg-green-50 rounded-lg border border-green-100 text-center">
          <div class="text-sm font-bold text-green-700">R$ 54mi</div>
          <div class="text-green-500 text-xs mt-0.5">Contratado</div>
        </div>
        <div class="p-3 bg-amber-50 rounded-lg border border-amber-100 text-center">
          <div class="text-sm font-bold text-amber-700">R$ 537k</div>
          <div class="text-amber-500 text-xs mt-0.5">Medido</div>
        </div>
        <div class="p-3 bg-indigo-50 rounded-lg border border-indigo-100 text-center">
          <div class="text-xl font-bold text-indigo-700">65%</div>
          <div class="text-indigo-500 text-xs mt-0.5">Executado</div>
        </div>
      </div>
      <div class="grid grid-cols-3 gap-2">
        <div class="col-span-1 p-3 bg-slate-50 rounded-lg border border-slate-200">
          <div class="text-xs text-slate-500 mb-2">Por status</div>
          <div class="flex items-end gap-1 h-10">
            <div class="bg-slate-400 rounded-t flex-1" style="height:90%"></div>
            <div class="bg-blue-500 rounded-t flex-1" style="height:40%"></div>
            <div class="bg-green-500 rounded-t flex-1" style="height:20%"></div>
            <div class="bg-red-400 rounded-t flex-1" style="height:10%"></div>
          </div>
        </div>
        <div class="col-span-2 p-3 bg-red-50 rounded-lg border border-red-200">
          <div class="text-xs font-semibold text-red-700 mb-1">⚠️ Alertas</div>
          <div class="space-y-1">
            <div class="text-xs text-red-600">🔴 6 contratos vencidos</div>
            <div class="text-xs text-amber-600">⏳ 1 contrato vence em 5 dias</div>
            <div class="text-xs text-red-600">🔴 3 convênios vencidos</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="space-y-3">
    <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
      <span class="text-xl shrink-0">🔢</span>
      <div>
        <p class="font-semibold">Os 4 números no topo</p>
        <p class="text-slate-500 text-xs mt-0.5">Total de obras, valor total contratado, quanto já foi medido e o percentual geral de execução. É um resumo rápido de tudo.</p>
      </div>
    </div>
    <div class="flex gap-3 p-3 bg-red-50 rounded-xl border border-red-200">
      <span class="text-xl shrink-0">⚠️</span>
      <div>
        <p class="font-semibold text-red-800">Os alertas — olhe isso todo dia!</p>
        <p class="text-red-700 text-xs mt-0.5">Essa caixinha vermelha avisa sobre contratos e convênios que já venceram ou que vencem em breve. Se aparecer algo ali, precisa de ação rápida pra não comprometer a obra.</p>
      </div>
    </div>
    <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
      <span class="text-xl shrink-0">📅</span>
      <div>
        <p class="font-semibold">Filtro de período</p>
        <p class="text-slate-500 text-xs mt-0.5">Você pode filtrar o painel por data — útil pra ver como foi o mês de outubro, por exemplo, ou o último trimestre.</p>
      </div>
    </div>
  </div>
</div>
`,
                        },

                        // ═══════════════════════════════════════════════════════
                        // MÓDULO 4 — CONSULTANDO OBRAS
                        // ═══════════════════════════════════════════════════════
                        {
                            titulo: 'Consultando e acompanhando obras',
                            icone: '🏗️',
                            cor: 'green',
                            perfis: ['todos'],
                            conteudo: `
<div class="space-y-5 text-slate-700 text-sm leading-relaxed">

  <p>Clica em <strong>🏗️ Obras</strong> no menu e você vê a lista de todas as obras cadastradas. É por aqui que começa quase tudo no sistema.</p>

  {{-- Simulação da lista --}}
  <div class="rounded-xl border border-slate-200 overflow-hidden shadow-sm text-xs">
    <div class="bg-slate-50 border-b px-4 py-2.5 flex items-center justify-between">
      <span class="font-semibold text-slate-700">🏗️ Obras</span>
      <span class="px-3 py-1 bg-blue-600 text-white rounded-lg text-xs">➕ Nova Obra</span>
    </div>
    <div class="p-3 bg-white">
      <div class="flex gap-2 mb-3">
        <div class="flex-1 h-8 bg-slate-50 rounded-lg border border-slate-200 flex items-center px-3 text-slate-400">🔍 Buscar obra pelo nome...</div>
        <div class="px-4 h-8 bg-blue-600 text-white rounded-lg flex items-center">Buscar</div>
      </div>
      <table class="w-full">
        <thead><tr class="bg-slate-50 text-slate-500 text-xs">
          <th class="text-left p-2">Obra</th>
          <th class="p-2 text-center">Status</th>
          <th class="p-2 text-center">Execução</th>
          <th class="p-2 text-right">Ações</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
          <tr>
            <td class="p-2">
              <div class="font-medium text-slate-800">Reforma UBS Vila Conde Siciliano</div>
              <div class="text-slate-400 text-xs">Proc: 1602/2024</div>
            </td>
            <td class="p-2 text-center">
              <span class="px-2 py-0.5 bg-blue-500 text-white rounded-full text-xs">Em Execução</span>
            </td>
            <td class="p-2">
              <div class="w-full bg-slate-200 rounded-full h-1.5 mb-0.5">
                <div class="bg-yellow-500 h-1.5 rounded-full" style="width:65.9%"></div>
              </div>
              <div class="text-center text-slate-600">65,9%</div>
            </td>
            <td class="p-2 text-right">
              <span class="px-2 py-1 border border-indigo-200 text-indigo-700 bg-indigo-50 rounded text-xs">👁 Ver</span>
            </td>
          </tr>
          <tr>
            <td class="p-2">
              <div class="font-medium text-slate-800">Terminal Rodoviário — Etapa 07</div>
              <div class="text-slate-400 text-xs">Proc: 1867/2024</div>
            </td>
            <td class="p-2 text-center">
              <span class="px-2 py-0.5 bg-slate-400 text-white rounded-full text-xs">Planejamento</span>
            </td>
            <td class="p-2">
              <div class="w-full bg-slate-200 rounded-full h-1.5 mb-0.5">
                <div class="bg-blue-500 h-1.5 rounded-full" style="width:28%"></div>
              </div>
              <div class="text-center text-slate-600">28%</div>
            </td>
            <td class="p-2 text-right">
              <span class="px-2 py-1 border border-indigo-200 text-indigo-700 bg-indigo-50 rounded text-xs">👁 Ver</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="space-y-3">
    <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
      <span class="text-xl shrink-0">🔍</span>
      <div>
        <p class="font-semibold">Como buscar uma obra</p>
        <p class="text-slate-500 text-xs mt-0.5">Digite qualquer parte do nome da obra no campo de busca e clique em <strong>Buscar</strong>. Não precisa digitar o nome completo — "rua valinhos" já encontra a obra certa.</p>
      </div>
    </div>

    <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
      <span class="text-xl shrink-0">👁</span>
      <div>
        <p class="font-semibold">Abrindo uma obra</p>
        <p class="text-slate-500 text-xs mt-0.5">Clica no botão <strong>👁 Ver</strong> pra abrir a tela completa da obra. Lá dentro você vê tudo: convênios, contratos, medições, gráficos e documentos.</p>
      </div>
    </div>

    <div class="flex gap-3 p-3 bg-blue-50 rounded-xl border border-blue-200">
      <span class="text-xl shrink-0">📂</span>
      <div>
        <p class="font-semibold text-blue-800">As 5 abas dentro da obra</p>
        <div class="flex gap-1 flex-wrap mt-1.5">
          <span class="px-2 py-0.5 bg-white border border-blue-200 text-blue-700 rounded text-xs">📋 Geral</span>
          <span class="px-2 py-0.5 bg-white border border-blue-200 text-blue-700 rounded text-xs">🤝 Convênios</span>
          <span class="px-2 py-0.5 bg-white border border-blue-200 text-blue-700 rounded text-xs">📄 Contratos</span>
          <span class="px-2 py-0.5 bg-white border border-blue-200 text-blue-700 rounded text-xs">📊 Execuções</span>
          <span class="px-2 py-0.5 bg-white border border-blue-200 text-blue-700 rounded text-xs">📎 Documentos</span>
        </div>
        <p class="text-blue-700 text-xs mt-1.5">Clique em cada aba pra alternar. Tudo sobre a obra num só lugar!</p>
      </div>
    </div>
  </div>
</div>
`,
                        },

                        // ═══════════════════════════════════════════════════════
                        // MÓDULO 5 — CADASTRANDO OBRAS
                        // ═══════════════════════════════════════════════════════
                        {
                            titulo: 'Cadastrando e editando obras',
                            icone: '✏️',
                            cor: 'amber',
                            perfis: ['admin', 'tecnico', 'secretario'],
                            conteudo: `
<div class="space-y-5 text-slate-700 text-sm leading-relaxed">

  <p>Pra cadastrar uma obra nova, clica em <strong>➕ Nova Obra</strong> na tela de listagem. O formulário é dividido em <strong>4 passos simples</strong> — tipo um assistente que te guia.</p>

  <div class="space-y-2">
    <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 items-start">
      <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold shrink-0">1</span>
      <div>
        <p class="font-semibold">Dados básicos</p>
        <p class="text-slate-500 text-xs mt-0.5">Nome completo da obra e o status atual (Em Planejamento, Em Execução, etc.). Campos obrigatórios.</p>
      </div>
    </div>
    <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 items-start">
      <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold shrink-0">2</span>
      <div>
        <p class="font-semibold">Localização 📍</p>
        <p class="text-slate-500 text-xs mt-0.5">Digite o CEP e o sistema preenche tudo sozinho (rua, bairro, cidade). O mapa já mostra o local automaticamente.</p>
      </div>
    </div>
    <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 items-start">
      <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold shrink-0">3</span>
      <div>
        <p class="font-semibold">Processo e observações</p>
        <p class="text-slate-500 text-xs mt-0.5">Número do processo administrativo e qualquer observação relevante sobre a obra.</p>
      </div>
    </div>
    <div class="flex gap-3 p-3 bg-green-50 rounded-xl border border-green-200 items-start">
      <span class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center text-sm font-bold shrink-0">4</span>
      <div>
        <p class="font-semibold text-green-800">Revisão — confira antes de salvar!</p>
        <p class="text-green-700 text-xs mt-0.5">No último passo você vê um resumo de tudo que preencheu. Se tiver algo errado, use o botão Voltar pra corrigir.</p>
      </div>
    </div>
  </div>

  <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
    <p class="font-semibold text-blue-800 mb-2">💡 A ordem certa depois de cadastrar:</p>
    <div class="flex items-center gap-1 flex-wrap text-xs text-blue-700">
      <span class="px-2 py-1 bg-white rounded border border-blue-200">1️⃣ Obra</span>
      <span>→</span>
      <span class="px-2 py-1 bg-white rounded border border-blue-200">2️⃣ Convênios (se tiver)</span>
      <span>→</span>
      <span class="px-2 py-1 bg-white rounded border border-blue-200">3️⃣ Empresa</span>
      <span>→</span>
      <span class="px-2 py-1 bg-white rounded border border-blue-200">4️⃣ Contrato</span>
      <span>→</span>
      <span class="px-2 py-1 bg-white rounded border border-blue-200">5️⃣ Medições</span>
    </div>
    <p class="text-blue-600 text-xs mt-2">Seguindo essa ordem, você não fica preso em nenhuma tela por falta de dados.</p>
  </div>

  <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
    <span class="text-xl shrink-0">✏️</span>
    <div>
      <p class="font-semibold">Editando uma obra</p>
      <p class="text-slate-500 text-xs mt-0.5">Na lista de obras ou dentro da obra, clica em <strong>✏️ Editar</strong>. O formulário de edição é mais simples — tudo numa página só, sem os passos. Ótimo pra atualizações rápidas como mudar o status.</p>
    </div>
  </div>
</div>
`,
                        },

                        // ═══════════════════════════════════════════════════════
                        // MÓDULO 6 — CONTRATOS
                        // ═══════════════════════════════════════════════════════
                        {
                            titulo: 'Criando e acompanhando contratos',
                            icone: '📄',
                            cor: 'indigo',
                            perfis: ['admin', 'tecnico', 'secretario'],
                            conteudo: `
<div class="space-y-5 text-slate-700 text-sm leading-relaxed">

  <p>O contrato é o que liga a obra à empresa que vai executar. Sem contrato, não dá pra registrar medições.</p>

  <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-xl">
    <p class="font-semibold text-indigo-800 mb-3">Como criar um contrato:</p>
    <div class="space-y-2 text-indigo-700 text-xs">
      <div class="flex gap-2"><span class="font-bold shrink-0">①</span><span>Abre a obra e clica no botão <strong>➕ Contrato</strong> lá no topo da página</span></div>
      <div class="flex gap-2"><span class="font-bold shrink-0">②</span><span>Escolhe a empresa. Se ela ainda não tá cadastrada, clica em <strong>"+ Nova empresa"</strong> — abre um formulário rápido sem precisar sair da tela!</span></div>
      <div class="flex gap-2"><span class="font-bold shrink-0">③</span><span>Preenche: número do contrato, processo de licitação, valor total, data de assinatura, data de início e <strong>data de vigência</strong> (essa é importante!)</span></div>
      <div class="flex gap-2"><span class="font-bold shrink-0">④</span><span>Clica em <strong>Salvar</strong></span></div>
    </div>
  </div>

  {{-- Simulação dos alertas de vencimento --}}
  <div class="space-y-2 text-xs">
    <p class="font-semibold text-slate-700">O que acontece com os prazos:</p>
    <div class="flex items-center gap-3 p-3 bg-red-50 rounded-xl border-l-4 border-red-500">
      <span class="text-lg">🔴</span>
      <div>
        <p class="font-semibold text-red-800">Contrato vencido</p>
        <p class="text-red-600 text-xs">Aparece com borda vermelha e alerta na aba Contratos da obra. Também fica nos alertas do painel principal.</p>
      </div>
    </div>
    <div class="flex items-center gap-3 p-3 bg-amber-50 rounded-xl border-l-4 border-amber-400">
      <span class="text-lg">⏳</span>
      <div>
        <p class="font-semibold text-amber-800">Vence nos próximos 30 dias</p>
        <p class="text-amber-600 text-xs">Aparece com borda amarela. Hora de verificar se precisa de aditivo!</p>
      </div>
    </div>
    <div class="flex items-center gap-3 p-3 bg-green-50 rounded-xl border-l-4 border-green-400">
      <span class="text-lg">✅</span>
      <div>
        <p class="font-semibold text-green-800">Em dia</p>
        <p class="text-green-600 text-xs">Sem alertas. Tudo certo!</p>
      </div>
    </div>
  </div>

  <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
    <span class="text-xl shrink-0">💡</span>
    <div>
      <p class="font-semibold">Uma obra pode ter mais de um contrato</p>
      <p class="text-slate-500 text-xs mt-0.5">Isso é normal quando tem aditivos ou quando uma segunda empresa é contratada pra uma etapa diferente da mesma obra.</p>
    </div>
  </div>
</div>
`,
                        },

                        // ═══════════════════════════════════════════════════════
                        // MÓDULO 7 — MEDIÇÕES
                        // ═══════════════════════════════════════════════════════
                        {
                            titulo: 'Registrando medições de execução',
                            icone: '📏',
                            cor: 'green',
                            perfis: ['admin', 'tecnico', 'secretario'],
                            conteudo: `
<div class="space-y-5 text-slate-700 text-sm leading-relaxed">

  <p>A medição é o registro de quanto foi executado e pago em um determinado período. É ela que atualiza o percentual de execução da obra.</p>

  <div class="p-4 bg-green-50 border border-green-200 rounded-xl">
    <p class="font-semibold text-green-800 mb-3">Passo a passo pra registrar uma medição:</p>
    <div class="space-y-2 text-green-700 text-xs">
      <div class="flex gap-2"><span class="font-bold shrink-0">①</span><span>Abre a obra e clica em <strong>➕ Medição</strong> lá no topo</span></div>
      <div class="flex gap-2"><span class="font-bold shrink-0">②</span><span>Escolhe o <strong>contrato</strong> — se a obra tiver mais de um, tem que especificar qual</span></div>
      <div class="flex gap-2"><span class="font-bold shrink-0">③</span><span>Informa a <strong>data da medição</strong> e o <strong>valor em R$</strong></span></div>
      <div class="flex gap-2"><span class="font-bold shrink-0">④</span><span>Enquanto você digita, o sistema já mostra ao vivo o novo saldo e o percentual — não precisa calcular nada!</span></div>
      <div class="flex gap-2"><span class="font-bold shrink-0">⑤</span><span>Adiciona os <strong>responsáveis</strong> (engenheiro, fiscal, supervisor) — isso fica registrado pra fins de auditoria</span></div>
      <div class="flex gap-2"><span class="font-bold shrink-0">⑥</span><span>Anexa documentos se tiver (boletim de medição, fotos, laudos)</span></div>
      <div class="flex gap-2"><span class="font-bold shrink-0">⑦</span><span>Clica em <strong>Salvar medição</strong></span></div>
    </div>
  </div>

  {{-- Preview em tempo real --}}
  <div class="rounded-xl border border-slate-200 overflow-hidden shadow-sm text-xs">
    <div class="bg-slate-50 border-b border-slate-200 px-4 py-2 font-semibold text-slate-600 flex items-center gap-2">
      <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse inline-block"></span>
      Prévia calculada automaticamente enquanto você digita
    </div>
    <div class="p-4 space-y-2.5 bg-white">
      <div class="flex justify-between items-center">
        <span class="text-slate-500">Valor do contrato</span>
        <span class="font-semibold text-slate-800">R$ 814.885,00</span>
      </div>
      <div class="flex justify-between items-center">
        <span class="text-slate-500">Já medido anteriormente</span>
        <span class="font-semibold text-slate-600">R$ 427.000,00</span>
      </div>
      <div class="flex justify-between items-center">
        <span class="text-slate-500">Esta medição</span>
        <span class="font-semibold text-blue-700">R$ 110.000,00</span>
      </div>
      <div class="border-t border-slate-100 pt-2 flex justify-between items-center">
        <span class="text-slate-500">Saldo que vai sobrar</span>
        <span class="font-semibold text-slate-700">R$ 277.885,00</span>
      </div>
      <div>
        <div class="flex justify-between mb-1">
          <span class="text-slate-500">Novo percentual executado</span>
          <span class="font-bold text-green-700">65,9%</span>
        </div>
        <div class="w-full bg-slate-200 rounded-full h-2.5">
          <div class="bg-yellow-500 h-2.5 rounded-full transition-all" style="width:65.9%"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="flex gap-3 p-3 bg-amber-50 rounded-xl border border-amber-200">
    <span class="text-xl shrink-0">⚠️</span>
    <div>
      <p class="font-semibold text-amber-800">Se o valor ultrapassar o saldo disponível</p>
      <p class="text-amber-700 text-xs mt-0.5">O sistema avisa com um alerta laranja, mas ainda deixa salvar. Isso pode acontecer quando tem um aditivo contratual. Se não tiver aditivo, confira o valor antes de salvar.</p>
    </div>
  </div>
</div>
`,
                        },

                        // ═══════════════════════════════════════════════════════
                        // MÓDULO 8 — GRÁFICOS
                        // ═══════════════════════════════════════════════════════
                        {
                            titulo: 'Gráficos de evolução da obra',
                            icone: '📈',
                            cor: 'teal',
                            perfis: ['todos'],
                            conteudo: `
<div class="space-y-5 text-slate-700 text-sm leading-relaxed">

  <p>Na aba <strong>Execuções</strong> de cada obra, você pode ver a evolução financeira em forma de gráficos. Basta clicar no botão <strong>📈 Gráficos</strong>.</p>

  {{-- Simulação do toggle --}}
  <div class="flex rounded-lg border border-slate-200 overflow-hidden text-xs w-fit">
    <div class="px-4 py-2 bg-blue-600 text-white font-medium">📈 Gráficos</div>
    <div class="px-4 py-2 bg-white text-slate-600 border-l border-slate-200">📋 Tabela</div>
  </div>

  <div class="space-y-3">
    <div class="p-4 bg-blue-50 rounded-xl border border-blue-200">
      <div class="flex items-center gap-2 mb-2">
        <span class="text-lg">📈</span>
        <p class="font-semibold text-blue-800">Gráfico de linha — evolução do %</p>
      </div>
      <p class="text-blue-700 text-xs">Mostra como o percentual executado foi crescendo ao longo das medições. Você vê se o ritmo tá bom ou se teve parada.</p>
      {{-- Simulação mini gráfico de linha --}}
      <div class="mt-3 h-16 bg-white rounded-lg border border-blue-100 p-2 flex items-end gap-1 overflow-hidden">
        <div class="text-xs text-slate-300 mr-1">0%</div>
        <svg viewBox="0 0 200 50" class="flex-1 h-full">
          <polyline points="0,50 40,42 80,35 120,20 160,12 200,5"
                    fill="none" stroke="#2563eb" stroke-width="2"/>
          <polyline points="0,50 40,42 80,35 120,20 160,12 200,5"
                    fill="rgba(37,99,235,0.1)" stroke="none"/>
          <line x1="0" y1="0" x2="200" y2="0" stroke="#94a3b8" stroke-width="1" stroke-dasharray="4,3"/>
          <circle cx="40" cy="42" r="3" fill="#2563eb"/>
          <circle cx="80" cy="35" r="3" fill="#2563eb"/>
          <circle cx="120" cy="20" r="3" fill="#2563eb"/>
          <circle cx="160" cy="12" r="3" fill="#2563eb"/>
        </svg>
        <div class="text-xs text-slate-300">100%</div>
      </div>
    </div>

    <div class="p-4 bg-green-50 rounded-xl border border-green-200">
      <div class="flex items-center gap-2 mb-2">
        <span class="text-lg">📊</span>
        <p class="font-semibold text-green-800">Gráfico de barras — financeiro por contrato</p>
      </div>
      <p class="text-green-700 text-xs">Compara o valor contratado, o que já foi medido e o saldo restante. Ótimo pra ver rapidamente a situação de cada contrato.</p>
      {{-- Simulação mini gráfico de barras --}}
      <div class="mt-3 flex items-end gap-3 justify-center h-16 bg-white rounded-lg border border-green-100 p-2">
        <div class="flex flex-col items-center gap-0.5">
          <div class="w-8 bg-blue-200 rounded-t" style="height:52px"></div>
          <span class="text-xs text-slate-400">Cont.</span>
        </div>
        <div class="flex flex-col items-center gap-0.5">
          <div class="w-8 bg-green-500 rounded-t" style="height:34px"></div>
          <span class="text-xs text-slate-400">Med.</span>
        </div>
        <div class="flex flex-col items-center gap-0.5">
          <div class="w-8 bg-slate-300 rounded-t" style="height:18px"></div>
          <span class="text-xs text-slate-400">Saldo</span>
        </div>
      </div>
    </div>

    <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-200">
      <div class="flex items-center gap-2 mb-2">
        <span class="text-lg">🎯</span>
        <p class="font-semibold text-indigo-800">Projeção de conclusão</p>
      </div>
      <p class="text-indigo-700 text-xs">Com base no ritmo das últimas medições, o sistema calcula uma <strong>data estimada de conclusão</strong>. Não é uma garantia, mas é uma boa referência pra planejamento.</p>
      <div class="mt-2 grid grid-cols-3 gap-2 text-xs">
        <div class="p-2 bg-white rounded-lg border border-indigo-100 text-center">
          <p class="font-bold text-indigo-700">15/08/2026</p>
          <p class="text-slate-400 text-xs">Data estimada</p>
        </div>
        <div class="p-2 bg-white rounded-lg border border-indigo-100 text-center">
          <p class="font-bold text-slate-700">133 dias</p>
          <p class="text-slate-400 text-xs">Dias restantes</p>
        </div>
        <div class="p-2 bg-white rounded-lg border border-green-100 text-center">
          <span class="px-1.5 py-0.5 bg-green-100 text-green-700 rounded text-xs font-semibold">🟢 Alta</span>
          <p class="text-slate-400 text-xs mt-0.5">Confiança</p>
        </div>
      </div>
    </div>
  </div>

  <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
    <span class="text-xl shrink-0">💡</span>
    <div>
      <p class="font-semibold">Confiança Alta vs. Média</p>
      <p class="text-slate-500 text-xs mt-0.5"><span class="text-green-600 font-medium">🟢 Alta</span> = calculada com 3 ou mais medições (mais precisa). <span class="text-amber-600 font-medium">🟡 Média</span> = só 2 medições disponíveis (use como referência).</p>
    </div>
  </div>
</div>
`,
                        },

                        // ═══════════════════════════════════════════════════════
                        // MÓDULO 9 — DOCUMENTOS
                        // ═══════════════════════════════════════════════════════
                        {
                            titulo: 'Guardando documentos e arquivos',
                            icone: '📎',
                            cor: 'slate',
                            perfis: ['admin', 'tecnico', 'secretario'],
                            conteudo: `
<div class="space-y-5 text-slate-700 text-sm leading-relaxed">

  <p>Você pode guardar arquivos de dois jeitos: direto na obra (contratos, atas, ofícios) ou numa medição específica (boletins, fotos, laudos).</p>

  <div class="grid grid-cols-2 gap-3">
    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
      <p class="font-semibold text-slate-800 mb-1.5">📁 Na obra</p>
      <p class="text-xs text-slate-500">Vá na aba <strong>Documentos</strong> e clique em <strong>📎 Anexar à Obra</strong>. Use pra: contrato assinado, atas, ofícios, processos.</p>
    </div>
    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
      <p class="font-semibold text-slate-800 mb-1.5">📊 Numa medição</p>
      <p class="text-xs text-slate-500">Ao criar ou editar uma medição, arraste ou selecione os arquivos. Use pra: boletim de medição, fotos do avanço, laudos técnicos.</p>
    </div>
  </div>

  {{-- Simulação do upload --}}
  <div class="rounded-xl border-2 border-dashed border-slate-300 p-6 text-center bg-slate-50">
    <div class="text-3xl mb-2">📎</div>
    <p class="font-semibold text-slate-700 text-sm">Arraste os arquivos aqui</p>
    <p class="text-xs text-slate-400 mt-1">ou clique pra selecionar do computador</p>
    <div class="mt-3 flex justify-center gap-2 flex-wrap text-xs text-slate-400">
      <span class="px-2 py-0.5 bg-white rounded border">📄 PDF</span>
      <span class="px-2 py-0.5 bg-white rounded border">📝 Word</span>
      <span class="px-2 py-0.5 bg-white rounded border">📊 Excel</span>
      <span class="px-2 py-0.5 bg-white rounded border">🖼️ JPG/PNG</span>
    </div>
    <p class="text-xs text-slate-400 mt-2">Até <strong>20 MB</strong> por arquivo · até <strong>10 arquivos</strong> de uma vez</p>
  </div>

  <div class="space-y-3">
    <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
      <span class="text-xl shrink-0">⬇️</span>
      <div>
        <p class="font-semibold">Baixar um documento</p>
        <p class="text-slate-500 text-xs mt-0.5">Qualquer usuário pode baixar. Clica em <strong>⬇️ Baixar</strong> do lado do arquivo.</p>
      </div>
    </div>
    <div class="flex gap-3 p-3 bg-red-50 rounded-xl border border-red-200">
      <span class="text-xl shrink-0">🗑️</span>
      <div>
        <p class="font-semibold text-red-800">Apagar um documento</p>
        <p class="text-red-700 text-xs mt-0.5">Só o <strong>Administrador</strong> pode apagar. E é permanente — o arquivo some do servidor. Então confirme bem antes de clicar!</p>
      </div>
    </div>
  </div>
</div>
`,
                        },

                        // ═══════════════════════════════════════════════════════
                        // MÓDULO 10 — RELATÓRIOS
                        // ═══════════════════════════════════════════════════════
                        {
                            titulo: 'Gerando relatórios',
                            icone: '📊',
                            cor: 'purple',
                            perfis: ['admin', 'tecnico', 'secretario'],
                            conteudo: `
<div class="space-y-5 text-slate-700 text-sm leading-relaxed">

  <p>Vai em <strong>📊 Relatórios</strong> no menu. Você pode gerar um PDF com gráficos pra apresentar ou uma planilha Excel pra analisar os dados.</p>

  <div class="p-4 bg-purple-50 border border-purple-200 rounded-xl">
    <p class="font-semibold text-purple-800 mb-3">Como gerar um relatório em 4 cliques:</p>
    <div class="space-y-2 text-purple-700 text-xs">
      <div class="flex gap-2"><span class="font-bold">①</span><span>Escolhe o <strong>tipo</strong> nas abas (Geral, Financeiro, Vencimentos, Por Empresa...)</span></div>
      <div class="flex gap-2"><span class="font-bold">②</span><span>Aplica os <strong>filtros</strong> se quiser filtrar por status, empresa ou período</span></div>
      <div class="flex gap-2"><span class="font-bold">③</span><span>Clica em <strong>👁 Pré-visualizar</strong> pra ver os dados antes de exportar</span></div>
      <div class="flex gap-2"><span class="font-bold">④</span><span>Clica em <strong>📄 PDF</strong> ou <strong>📊 Excel</strong> pra baixar</span></div>
    </div>
  </div>

  <div class="grid grid-cols-2 gap-3">
    <div class="p-4 bg-red-50 rounded-xl border border-red-200">
      <p class="text-lg mb-1.5">📄</p>
      <p class="font-semibold text-red-800">PDF</p>
      <p class="text-red-700 text-xs mt-1">Vem com gráficos de pizza e barras. Bom pra apresentações, reuniões e impressão.</p>
    </div>
    <div class="p-4 bg-green-50 rounded-xl border border-green-200">
      <p class="text-lg mb-1.5">📊</p>
      <p class="font-semibold text-green-800">Excel</p>
      <p class="text-green-700 text-xs mt-1">Planilha com 5 abas: Resumo, Obras, Execução Financeira, Vencimentos e Por Empresa. Bom pra cruzar dados.</p>
    </div>
  </div>

  <div class="space-y-2">
    <p class="font-semibold text-slate-700 text-xs uppercase tracking-wide">Relatórios disponíveis:</p>
    <div class="grid grid-cols-2 gap-2 text-xs">
      <div class="p-2 bg-slate-50 rounded-lg border border-slate-200">📋 <strong>Geral</strong> — todas as obras</div>
      <div class="p-2 bg-slate-50 rounded-lg border border-slate-200">💰 <strong>Financeiro</strong> — contratado × medido</div>
      <div class="p-2 bg-slate-50 rounded-lg border border-slate-200">⏳ <strong>Vencimentos</strong> — contratos por vencer</div>
      <div class="p-2 bg-slate-50 rounded-lg border border-slate-200">🚦 <strong>Por Status</strong> — obras agrupadas</div>
      <div class="p-2 bg-slate-50 rounded-lg border border-slate-200">🏢 <strong>Por Empresa</strong> — ranking</div>
      <div class="p-2 bg-slate-50 rounded-lg border border-slate-200">🏛️ <strong>Por Órgão</strong> — por financiador</div>
    </div>
  </div>
</div>
`,
                        },

                        // ═══════════════════════════════════════════════════════
                        // MÓDULO 10-A — PROCESSOS ADMINISTRATIVOS: VISÃO GERAL
                        // ═══════════════════════════════════════════════════════
                        {
                            titulo: 'Processos Administrativos — o que é isso?',
                            icone: '🗂️',
                            cor: 'indigo',
                            perfis: ['todos'],
                            conteudo: `
<div class="space-y-5 text-slate-700 text-sm leading-relaxed">

  <p>Além das obras públicas, o sistema também acompanha os <strong>Processos Administrativos</strong> da Secretaria — alvarás, certidões, ligações de água e energia. É uma frente separada das obras, com sua própria tela, mas usa o mesmo login e o mesmo menu lateral.</p>

  <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-xl">
    <p class="font-semibold text-indigo-800 mb-2">Qual é o objetivo desse módulo?</p>
    <p class="text-indigo-700 text-sm">Hoje o controle desses processos é feito numa planilha, em texto livre. O pedido principal do Secretário é simples: <strong>saber em qual fase está cada processo — e, se estiver parado, por qual motivo.</strong> É exatamente isso que essa tela resolve.</p>
  </div>

  <p class="font-semibold text-slate-800">Os 5 conceitos principais:</p>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
      <p class="text-lg mb-1">🗂️</p>
      <p class="font-semibold text-slate-800">Processo</p>
      <p class="text-xs text-slate-500 mt-1">O registro principal: número, requerente, endereço, tipo de serviço, responsável técnico e situação (aberto/arquivado).</p>
    </div>
    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
      <p class="text-lg mb-1">🔄</p>
      <p class="font-semibold text-slate-800">Trâmite</p>
      <p class="text-xs text-slate-500 mt-1">Cada movimentação do processo, registrada como numa linha do tempo. Toda vez que algo acontece, você lança um novo trâmite.</p>
    </div>
    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
      <p class="text-lg mb-1">📍</p>
      <p class="font-semibold text-slate-800">Fase</p>
      <p class="text-xs text-slate-500 mt-1">Em qual etapa da tramitação o processo está agora (Protocolado, Em Análise, Notificado, Arquivado...). Atualizada automaticamente a cada trâmite.</p>
    </div>
    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
      <p class="text-lg mb-1">🏷️</p>
      <p class="font-semibold text-slate-800">Tipo de Processo</p>
      <p class="text-xs text-slate-500 mt-1">O serviço solicitado: alvará de construção, certidão de uso do solo, ligação de água/energia, entre outros.</p>
    </div>
    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
      <p class="text-lg mb-1">👷</p>
      <p class="font-semibold text-slate-800">Responsável Técnico</p>
      <p class="text-xs text-slate-500 mt-1">O engenheiro ou arquiteto (CREA/CAU) vinculado ao processo.</p>
    </div>
    <div class="p-4 bg-amber-50 rounded-xl border border-amber-200">
      <p class="text-lg mb-1">📥</p>
      <p class="font-semibold text-amber-800">"A Classificar"</p>
      <p class="text-xs text-amber-700 mt-1">Processos importados do histórico antigo (planilha) começam nessa fase e tipo genéricos, até alguém revisar e classificar corretamente.</p>
    </div>
  </div>
</div>
`,
                        },

                        // ═══════════════════════════════════════════════════════
                        // MÓDULO 10-B — CONSULTANDO PROCESSOS
                        // ═══════════════════════════════════════════════════════
                        {
                            titulo: 'Consultando processos',
                            icone: '🔍',
                            cor: 'green',
                            perfis: ['todos'],
                            conteudo: `
<div class="space-y-5 text-slate-700 text-sm leading-relaxed">

  <p>Clica em <strong>🗂️ Processos</strong> no menu lateral e você vê a lista completa, com o mesmo estilo de busca e filtros da tela de Obras.</p>

  {{-- Simulação da lista --}}
  <div class="rounded-xl border border-slate-200 overflow-hidden shadow-sm text-xs">
    <div class="bg-slate-50 border-b px-4 py-2.5 flex items-center justify-between">
      <span class="font-semibold text-slate-700">🗂️ Processos</span>
      <span class="px-3 py-1 bg-blue-600 text-white rounded-lg text-xs">➕ Novo Processo</span>
    </div>
    <div class="p-3 bg-white">
      <div class="grid grid-cols-4 gap-1.5 mb-3 text-xs">
        <div class="h-7 bg-slate-50 rounded border border-slate-200 flex items-center px-2 text-slate-400">Tipo</div>
        <div class="h-7 bg-slate-50 rounded border border-slate-200 flex items-center px-2 text-slate-400">Fase</div>
        <div class="h-7 bg-slate-50 rounded border border-slate-200 flex items-center px-2 text-slate-400">Responsável</div>
        <div class="h-7 bg-slate-50 rounded border border-slate-200 flex items-center px-2 text-slate-400">Situação</div>
      </div>
      <table class="w-full">
        <tbody class="divide-y divide-slate-100">
          <tr>
            <td class="p-2">
              <div class="font-medium text-slate-800">1831/2019-5</div>
              <div class="text-slate-400 text-xs">Eliezer Aparecido da Silva</div>
            </td>
            <td class="p-2 text-center">
              <span class="px-2 py-0.5 bg-amber-500 text-white rounded-full text-xs">Notificado</span>
              <div class="text-red-500 text-xs mt-0.5">⚠️ aguardando devolutiva</div>
            </td>
            <td class="p-2 text-right">
              <span class="px-2 py-1 border border-indigo-200 text-indigo-700 bg-indigo-50 rounded text-xs">👁 Ver</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="space-y-3">
    <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
      <span class="text-xl shrink-0">🔍</span>
      <div>
        <p class="font-semibold">Filtros disponíveis</p>
        <p class="text-slate-500 text-xs mt-0.5">Número/requerente, endereço, tipo de processo, fase atual, responsável técnico e situação (aberto/arquivado). Combine quantos quiser.</p>
      </div>
    </div>
    <div class="flex gap-3 p-3 bg-indigo-50 rounded-xl border border-indigo-200">
      <span class="text-xl shrink-0">📍</span>
      <div>
        <p class="font-semibold text-indigo-800">A cor da fase importa</p>
        <p class="text-indigo-700 text-xs mt-0.5">Cada fase tem uma cor própria no badge. Quando o processo tem um <strong>motivo de pendência</strong> registrado, aparece um aviso ⚠️ vermelho embaixo do badge — é isso que indica "está parado, e por quê".</p>
      </div>
    </div>
    <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
      <span class="text-xl shrink-0">👁</span>
      <div>
        <p class="font-semibold">Abrindo um processo</p>
        <p class="text-slate-500 text-xs mt-0.5">Clica em <strong>👁 Ver</strong> pra abrir a tela de detalhe, com todos os dados e a <strong>linha do tempo completa de trâmites</strong>, do mais recente pro mais antigo.</p>
      </div>
    </div>
  </div>
</div>
`,
                        },

                        // ═══════════════════════════════════════════════════════
                        // MÓDULO 10-C — CADASTRANDO PROCESSOS E TRÂMITES
                        // ═══════════════════════════════════════════════════════
                        {
                            titulo: 'Cadastrando processos e trâmites',
                            icone: '✏️',
                            cor: 'amber',
                            perfis: ['admin', 'tecnico'],
                            conteudo: `
<div class="space-y-5 text-slate-700 text-sm leading-relaxed">

  <p>Pra cadastrar um processo novo, clica em <strong>➕ Novo Processo</strong> na tela de listagem. É um formulário só, sem passos — mais rápido que o de obras.</p>

  <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl">
    <p class="font-semibold text-amber-800 mb-2">📍 Endereço com CEP automático</p>
    <p class="text-amber-700 text-sm">Digite o CEP e o sistema preenche sozinho rua, bairro, cidade e UF. Número e complemento (apto, bloco, condomínio) você preenche na mão — isso a API de CEP não sabe.</p>
  </div>

  <div class="space-y-2">
    <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 items-start">
      <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold shrink-0">1</span>
      <div>
        <p class="font-semibold">Dados básicos</p>
        <p class="text-slate-500 text-xs mt-0.5">Número do processo, requerente e endereço (com CEP).</p>
      </div>
    </div>
    <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 items-start">
      <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold shrink-0">2</span>
      <div>
        <p class="font-semibold">Tipo e responsável técnico</p>
        <p class="text-slate-500 text-xs mt-0.5">Escolhe o tipo de serviço. Se o responsável técnico ainda não tiver cadastro, clica em <strong>"➕ Novo Responsável"</strong> — abre um formulário rápido sem sair da tela, igual ao de empresas em Contratos.</p>
      </div>
    </div>
    <div class="flex gap-3 p-3 bg-green-50 rounded-xl border border-green-200 items-start">
      <span class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center text-sm font-bold shrink-0">3</span>
      <div>
        <p class="font-semibold text-green-800">Fase inicial e situação</p>
        <p class="text-green-700 text-xs mt-0.5">Pra processo novo, normalmente a fase é <strong>"Protocolado"</strong> e a situação é <strong>"Aberto"</strong>.</p>
      </div>
    </div>
  </div>

  <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-xl">
    <p class="font-semibold text-indigo-800 mb-2">🔄 Depois de criado: registrando trâmites</p>
    <p class="text-indigo-700 text-sm mb-3">Na tela de detalhe do processo, use o formulário <strong>"Registrar Trâmite"</strong> toda vez que algo acontecer — é exatamente como anotar na planilha, só que agora estruturado.</p>
    <div class="space-y-1.5 text-indigo-700 text-xs">
      <div class="flex gap-2"><span class="font-bold shrink-0">Fase*</span><span>Obrigatório. Escolha a fase atual — isso <strong>atualiza automaticamente</strong> a fase do processo na listagem e no dashboard.</span></div>
      <div class="flex gap-2"><span class="font-bold shrink-0">Descrição*</span><span>Obrigatório. Texto livre, igual ao hábito da planilha — ex.: "Notificado via e-mail em 21/10, aguardar CX 12 novembro".</span></div>
      <div class="flex gap-2"><span class="font-bold shrink-0">Motivo da pendência</span><span>Opcional. Preencha quando o trâmite deixar o processo parado — esse texto aparece como alerta na listagem e no painel principal.</span></div>
    </div>
  </div>

  <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
    <span class="text-xl shrink-0">💡</span>
    <div>
      <p class="font-semibold">Editar processo × registrar trâmite</p>
      <p class="text-slate-500 text-xs mt-0.5">Use <strong>✏️ Editar</strong> só pra corrigir dados cadastrais (número, requerente, endereço). Pra mudar de fase no dia a dia, sempre prefira <strong>registrar um novo trâmite</strong> — assim fica um histórico completo.</p>
    </div>
  </div>
</div>
`,
                        },

                        // ═══════════════════════════════════════════════════════
                        // MÓDULO 10-D — DASHBOARD E RELATÓRIOS DE PROCESSOS
                        // ═══════════════════════════════════════════════════════
                        {
                            titulo: 'Dashboard e relatórios de Processos',
                            icone: '📊',
                            cor: 'purple',
                            perfis: ['admin', 'tecnico', 'secretario'],
                            conteudo: `
<div class="space-y-5 text-slate-700 text-sm leading-relaxed">

  <p>O Painel Principal (aquele mesmo dashboard das obras) também mostra um resumo dos processos administrativos, mais abaixo na mesma página.</p>

  {{-- Simulação do painel de processos --}}
  <div class="rounded-xl border border-slate-200 overflow-hidden shadow-sm text-xs">
    <div class="bg-slate-50 border-b border-slate-200 px-4 py-2.5">
      <span class="font-semibold text-slate-700">🗂️ Processos Administrativos</span>
    </div>
    <div class="p-4 bg-white space-y-3">
      <div class="grid grid-cols-4 gap-2">
        <div class="p-2 bg-blue-50 rounded-lg border border-blue-100 text-center">
          <div class="text-lg font-bold text-blue-700">2.275</div>
          <div class="text-blue-500 text-xs">Total</div>
        </div>
        <div class="p-2 bg-green-50 rounded-lg border border-green-100 text-center">
          <div class="text-lg font-bold text-green-700">2.274</div>
          <div class="text-green-500 text-xs">Abertos</div>
        </div>
        <div class="p-2 bg-slate-50 rounded-lg border border-slate-200 text-center">
          <div class="text-lg font-bold text-slate-600">1</div>
          <div class="text-slate-500 text-xs">Arquivados</div>
        </div>
        <div class="p-2 bg-amber-50 rounded-lg border border-amber-100 text-center">
          <div class="text-lg font-bold text-amber-700">704</div>
          <div class="text-amber-500 text-xs">A Classificar</div>
        </div>
      </div>
      <div class="text-xs text-slate-500 text-center pt-1">+ gráfico de pizza por fase e barras por tipo de serviço</div>
    </div>
  </div>

  <div class="space-y-3">
    <div class="flex gap-3 p-3 bg-purple-50 rounded-xl border border-purple-200">
      <span class="text-xl shrink-0">📍</span>
      <div>
        <p class="font-semibold text-purple-800">O gráfico mais importante</p>
        <p class="text-purple-700 text-xs mt-0.5">A pizza "Processos por Fase" é a resposta visual direta ao pedido do Secretário: mostra de cara quantos processos estão em cada etapa. Clique numa fatia da legenda pra ver os processos daquela fase.</p>
      </div>
    </div>
    <div class="flex gap-3 p-3 bg-red-50 rounded-xl border border-red-200">
      <span class="text-xl shrink-0">⚠️</span>
      <div>
        <p class="font-semibold text-red-800">Aba "Processos pendentes"</p>
        <p class="text-red-700 text-xs mt-0.5">Fica junto com os outros alertas do painel (contratos e convênios vencendo) — lista os processos abertos que têm motivo de pendência registrado.</p>
      </div>
    </div>
  </div>

  <p>Pra relatórios completos, vai em <strong>📊 Relatórios</strong> no menu e clica no seletor <strong>🗂️ Processos Administrativos</strong> no topo da tela pra trocar do módulo de Obras pro de Processos.</p>

  <div class="grid grid-cols-2 gap-2 text-xs">
    <div class="p-2 bg-slate-50 rounded-lg border border-slate-200">📋 <strong>Geral</strong> — todos os processos</div>
    <div class="p-2 bg-slate-50 rounded-lg border border-slate-200">📍 <strong>Por Fase</strong> — quantidade por etapa</div>
    <div class="p-2 bg-slate-50 rounded-lg border border-slate-200">🏷️ <strong>Por Tipo</strong> — quantidade por serviço</div>
    <div class="p-2 bg-slate-50 rounded-lg border border-slate-200">⚠️ <strong>Pendências</strong> — processos parados</div>
    <div class="p-2 bg-slate-50 rounded-lg border border-slate-200 col-span-2">👷 <strong>Por Responsável</strong> — ranking de responsáveis técnicos</div>
  </div>

  <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
    <span class="text-xl shrink-0">💡</span>
    <div>
      <p class="font-semibold">Mesmo jeito de exportar</p>
      <p class="text-slate-500 text-xs mt-0.5">Pré-visualize, depois exporte em <strong>📄 PDF</strong> ou <strong>📊 Excel</strong> — o mesmo fluxo dos relatórios de Obras, com o mesmo visual institucional.</p>
    </div>
  </div>
</div>
`,
                        },

                        // ═══════════════════════════════════════════════════════
                        // MÓDULO 11 — ADMINISTRAÇÃO (só admin)
                        // ═══════════════════════════════════════════════════════
                        {
                            titulo: 'Administrando o sistema',
                            icone: '⚙️',
                            cor: 'red',
                            perfis: ['admin'],
                            conteudo: `
<div class="space-y-5 text-slate-700 text-sm leading-relaxed">

  <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-800 text-xs flex items-center gap-2">
    🔑 <span>Esse módulo é só pra você, <strong>Administrador</strong>.</span>
  </div>

  <p>Clica em <strong>⚙️ Administração</strong> no menu pra gerenciar as configurações do sistema.</p>

  <div class="space-y-3">
    <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
      <span class="text-xl shrink-0">👥</span>
      <div>
        <p class="font-semibold">Usuários</p>
        <p class="text-slate-500 text-xs mt-0.5">Cadastra novos usuários, define o perfil de cada um e ativa/inativa contas. Um usuário inativo não consegue mais fazer login — mas o histórico dele fica salvo.</p>
      </div>
    </div>

    <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
      <span class="text-xl shrink-0">🏢</span>
      <div>
        <p class="font-semibold">Empresas</p>
        <p class="text-slate-500 text-xs mt-0.5">Cadastro completo de empresas contratadas. Atenção: se a empresa tiver contratos vinculados, ela não pode ser apagada.</p>
      </div>
    </div>

    <div class="flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
      <span class="text-xl shrink-0">🚦</span>
      <div>
        <p class="font-semibold">Status de Obras e Categorias</p>
        <p class="text-slate-500 text-xs mt-0.5">Configure os status e suas cores. Você também gerencia as categorias de convênio e os órgãos financiadores por aqui.</p>
      </div>
    </div>

    <div class="flex gap-3 p-3 bg-red-50 rounded-xl border border-red-200">
      <span class="text-xl shrink-0">⛔</span>
      <div>
        <p class="font-semibold text-red-800">Cuidado ao apagar obras!</p>
        <p class="text-red-700 text-xs mt-0.5">Ao apagar uma obra, <strong>tudo</strong> que está vinculado a ela some junto: contratos, medições e documentos. Não tem como desfazer. Confirme bem antes de clicar em Excluir.</p>
      </div>
    </div>
  </div>
</div>
`,
                        },

                        // ═══════════════════════════════════════════════════════
                        // MÓDULO 12 — DICAS FINAIS
                        // ═══════════════════════════════════════════════════════
                        {
                            titulo: 'Dicas pra usar bem o sistema',
                            icone: '💡',
                            cor: 'amber',
                            perfis: ['todos'],
                            conteudo: `
<div class="space-y-4 text-slate-700 text-sm leading-relaxed">

  <p>Antes de ir pro sistema, deixa eu te passar as principais dicas de quem já sabe como ele funciona:</p>

  <div class="space-y-3">
    <div class="flex gap-3 p-4 bg-green-50 rounded-xl border border-green-200">
      <span class="text-2xl shrink-0">📅</span>
      <div>
        <p class="font-semibold text-green-800">Registre medições todo mês</p>
        <p class="text-green-700 text-xs mt-1">Quanto mais regular for o registro, mais precisa fica a projeção de conclusão e mais úteis ficam os relatórios. Uma medição por mês já é ótimo.</p>
      </div>
    </div>

    <div class="flex gap-3 p-4 bg-red-50 rounded-xl border border-red-200">
      <span class="text-2xl shrink-0">⚠️</span>
      <div>
        <p class="font-semibold text-red-800">Olhe os alertas todo dia</p>
        <p class="text-red-700 text-xs mt-1">A caixinha de alertas no painel principal é a parte mais importante do sistema. Contrato vencido sem aditivo pode travar uma obra inteira.</p>
      </div>
    </div>

    <div class="flex gap-3 p-4 bg-blue-50 rounded-xl border border-blue-200">
      <span class="text-2xl shrink-0">📎</span>
      <div>
        <p class="font-semibold text-blue-800">Anexe documentos no momento certo</p>
        <p class="text-blue-700 text-xs mt-1">Na hora de registrar uma medição, aproveita e anexa o boletim e as fotos. Fica tudo junto, fácil de achar depois em qualquer auditoria.</p>
      </div>
    </div>

    <div class="flex gap-3 p-4 bg-amber-50 rounded-xl border border-amber-200">
      <span class="text-2xl shrink-0">🔒</span>
      <div>
        <p class="font-semibold text-amber-800">Não compartilhe sua senha</p>
        <p class="text-amber-700 text-xs mt-1">Cada ação no sistema fica registrada com o nome do usuário. Se precisar dar acesso pra alguém, peça pro administrador criar uma conta nova.</p>
      </div>
    </div>

    <div class="flex gap-3 p-4 bg-slate-50 rounded-xl border border-slate-200">
      <span class="text-2xl shrink-0">❓</span>
      <div>
        <p class="font-semibold text-slate-800">Travou? Clique no botão Ajuda</p>
        <p class="text-slate-500 text-xs mt-1">Toda tela tem um botão <strong>❓ Ajuda</strong> com instruções específicas para aquela situação. É o primeiro lugar pra olhar quando tiver dúvida.</p>
      </div>
    </div>
  </div>

  <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl text-white text-center mt-2">
    <p class="text-3xl mb-3">🎉</p>
    <p class="text-lg font-bold">Treinamento concluído!</p>
    <p class="text-sm opacity-85 mt-1.5">Agora você já sabe o suficiente pra usar o sistema com confiança.</p>
    <p class="text-xs opacity-70 mt-1">Pode voltar a este manual a qualquer hora pelo menu lateral — <strong>📘 Manual</strong>.</p>
  </div>
</div>
`,
                        },

                    ];
                } // fim modulos()
            }; // fim return
        } // fim manual()
    </script>

@endsection
