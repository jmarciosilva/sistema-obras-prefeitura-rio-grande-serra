# 🏗️ Sistema de Acompanhamento de Obras Públicas

**Prefeitura Municipal de Rio Grande da Serra — SP**

Sistema web desenvolvido em Laravel 12 para centralizar e acompanhar todas as obras municipais, convênios, contratos, medições de execução e documentos financiados por recursos próprios ou repasses estaduais e federais.

A partir da Fase 7, o sistema passou a cobrir também um segundo domínio, paralelo ao de Obras: o **licenciamento urbano** (alvarás, certidões, ligações de água/energia) tramitado pela Secretaria — ver [🗂️ Módulo Processos Administrativos](#-módulo-processos-administrativos-licenciamento-e-alvarás).

---

## 🎯 Objetivos

**Obras públicas:**
- Centralizar o cadastro de obras municipais em qualquer fase (planejamento, execução, concluída)
- Registrar convênios, categorias e órgãos financiadores com vínculo direto às obras
- Controlar contratos de licitação com empresa, valor, prazo e alertas de vigência
- Acompanhar execução financeira por medições com cálculo automático de saldo e percentual
- Registrar responsáveis por medição (engenheiro, fiscal, supervisor) para fins de auditoria
- Anexar documentos (boletins, fotos, planilhas, contratos, atas) a obras e medições
- Oferecer painel visual com KPIs, gráficos de evolução e projeção de conclusão por obra
- Exportar relatórios em PDF (com gráficos) e Excel (múltiplas abas)
- Controlar acesso por perfil de usuário (admin, técnico, secretário, operador)

**Processos administrativos (licenciamento):**
- Dar visibilidade ao Secretário de Obras sobre em qual fase está cada processo — e, se parado, por quê
- Substituir progressivamente o controle em planilha de texto livre por uma linha do tempo estruturada de trâmites
- Importar o histórico de mais de 2.000 processos já em andamento, sem perder o texto original

---

## 🚀 Tecnologias

| Camada         | Tecnologia                                          |
|----------------|-----------------------------------------------------|
| Backend        | PHP 8.2 + Laravel 12                                |
| Frontend       | Blade + Tailwind CSS v3 + Alpine.js v3              |
| Gráficos       | Chart.js 4.4 (CDN)                                  |
| PDF            | barryvdh/laravel-dompdf                             |
| Excel          | maatwebsite/excel                                   |
| Banco de dados | MySQL 8                                             |
| Build          | Vite + Laravel Vite Plugin                          |
| Autenticação   | Laravel Breeze                                      |
| Storage        | Laravel Storage (disco `public`)                    |
| Endereço (CEP) | API pública ViaCEP (`fetch` client-side, sem API key)|
| Localização    | `lang/pt_BR/*` — mensagens de validação e paginação em português |

---

## ✅ Fase 1 — Base do Sistema *(concluída)*

### 🏗️ Obras
Entidade central do sistema. Cada obra possui descrição, endereço, processo de execução, status colorido, vínculo N:M com convênios e demanda de origem. A tela de detalhe (`obras.show`) centraliza todas as informações em abas: Geral, Convênios, Contratos, Execuções e Documentos. KPIs de valor medido, saldo, percentual executado, contratos e convênios são calculados dinamicamente via accessors no model — sem colunas extras na tabela.

### 🤝 Convênios
Instrumentos jurídicos entre a prefeitura e órgãos financiadores. Um convênio pode financiar várias obras (N:M via pivot `obra_convenio`). Gerenciamento de vínculos com obras feito por tela dedicada com checkboxes, filtro inline e desvínculo individual. Alertas visuais de vigência vencida ou próxima do vencimento.

### 📋 Contratos
Cada obra pode ter um ou mais contratos vinculando a obra a uma empresa contratada, com valor, prazo, processo de licitação e vigência. Alertas de vencimento em 30 dias ou expirado. Modal de criação rápida de empresa diretamente no formulário de contrato para não interromper o fluxo do usuário.

### 📊 Execuções (Medições)
Registro histórico das medições de cada contrato. Ao informar o valor, o sistema calcula automaticamente saldo contratual e percentual executado acumulado — sem entrada manual. O formulário exibe em tempo real a prévia do novo saldo e percentual com barra de progresso animada. Alerta quando o valor ultrapassa o saldo disponível. Cada medição suporta múltiplos responsáveis com papel definido (engenheiro, fiscal, supervisor) e múltiplos documentos anexos.

### 📎 Documentos
Upload de arquivos (PDF, imagens, planilhas, Word) com até 20 MB por arquivo e até 10 arquivos por envio, via drag-and-drop ou seleção. Documentos são vinculados polimorficamente a obras ou medições. A aba Documentos da obra exibe tudo separado por origem: documentos diretos da obra e documentos de cada medição agrupados por data. Download e exclusão (somente admin) disponíveis em ambas as origens.

### 🏢 Empresas
CRUD completo de empresas contratadas com máscara de CNPJ, telefone e validação de unicidade. Proteção contra exclusão de empresa com contratos vinculados — verificação no controller antes de qualquer tentativa de deleção.

### ⚙️ Área Admin
CRUDs de: Usuários (com ativar/inativar), Empresas, Status de Obras, Categorias de Convênio, Órgãos Financiadores e Demandas/Propostas. Acesso restrito ao perfil `admin`.

### 👥 Usuários e Perfis
Quatro perfis de acesso com middleware `CheckPerfil`:

| Perfil       | Permissões                                                          |
|--------------|---------------------------------------------------------------------|
| `admin`      | CRUD completo, exclusões, gerenciamento de usuários e tabelas admin |
| `tecnico`    | Criar e editar obras, contratos, medições e documentos              |
| `secretario` | Mesmo acesso do técnico, pode ser responsável por medições          |
| `operador`   | Somente leitura                                                     |

---

## ✅ Fase 2 — Dashboard Executivo *(concluída)*

Painel central com visão consolidada de todas as obras do município:

- **KPIs globais:** total de obras, valor contratado, valor medido, percentual executado geral
- **Gráfico de pizza:** distribuição de obras por status com accordion expansível
- **Gráfico de barras mensais:** evolução do valor medido nos últimos 12 meses
- **Barras horizontais por status:** ranking visual de execução
- **Alertas em abas:** contratos vencidos, contratos vencendo em breve, convênios vencidos/vencendo e obras sem medição recente
- **Filtro por período:** restringe os dados do painel a um intervalo de datas

---

## ✅ Fase 3 — Relatórios Exportáveis *(concluída)*

Módulo completo de geração de relatórios acessível em `/relatorios`:

### Tipos de relatório
| Tipo | Conteúdo |
|------|----------|
| Geral | Todas as obras com KPIs financeiros |
| Execução Financeira | Contratado × Medido × Saldo por obra |
| Vencimentos | Contratos vencendo/vencidos em X dias |
| Por Status | Obras agrupadas por fase |
| Por Empresa | Ranking de empresas contratadas por valor |
| Por Órgão | Obras vinculadas a cada órgão financiador |

### Filtros disponíveis
Status · Órgão Financiador · Empresa Contratada · Período de medição · Prazo de vencimento (30/60/90/180 dias)

### Exportações
- **📄 PDF** — formato A4 paisagem com margens laterais generosas, capa institucional, gráficos de pizza (status, execução financeira, vencimentos, empresas, participação mensal) e gráficos de torre (barras verticais por obra e por mês), tabelas formatadas com zebra-striping e barras de progresso visuais. Todos os gráficos são gerados server-side em PHP puro via SVG, compatíveis com dompdf — sem dependência de JavaScript.
- **📊 Excel** — planilha com 5 abas: Resumo, Obras, Execução Financeira, Contratos Vencendo e Por Empresa. Cada aba tem cabeçalho colorido e larguras de coluna otimizadas.
- **👁 Pré-visualização HTML** — exibe exatamente os dados que serão exportados antes de gerar o arquivo. Botões de exportação fixos no rodapé durante a rolagem.

### Atalhos rápidos
Três cartões de acesso direto para os relatórios mais usados: contratos vencendo em 30 dias, execução financeira completa e obras em execução.

---

## ✅ Fase 4 — Gráficos de Evolução por Obra *(concluída)*

A aba **Execuções** de cada obra ganhou um painel visual completo com toggle entre vista de gráficos e tabela:

### Endpoint de dados
`GET /obras/visualizar/{obra}/grafico` → JSON com medições, série acumulada, dados por contrato e projeção calculada server-side.

### Gráficos (Chart.js 4.4 via CDN)
| Gráfico | Tipo | Conteúdo |
|---------|------|----------|
| Evolução do % Executado | Linha | Percentual acumulado por data de medição + meta 100% tracejada |
| Contratado × Medido × Saldo | Barras agrupadas | Comparativo financeiro por contrato |
| Valor por Medição | Misto (barras + linha) | Valor individual de cada medição + linha de acumulado |

### Projeção de conclusão
Calculada automaticamente a partir do ritmo das últimas 3 medições (R$/dia). Exibe data estimada de conclusão, dias restantes e ritmo médio mensal. Nível de confiança indicado: **Alta** (3+ medições) ou **Média** (2 medições). Modal explicativo com detalhes do algoritmo disponível ao usuário.

### Interface
- Toggle **Gráficos / Tabela** — a tabela original de medições permanece intacta
- KPI cards visíveis em ambas as vistas
- Loading spinner durante o fetch dos dados
- Tratamento de erros com mensagem real do servidor e botão "Tentar novamente"
- Modal "Como é calculado?" com explicação acessível do algoritmo de projeção

---

## 🗂️ Módulo Processos Administrativos (Licenciamento e Alvarás)

Domínio novo, **paralelo** ao módulo de Obras (não é a mesma coisa: Obras é gestão de investimento público; Processos é licenciamento urbano — alvarás, certidões, ligações de água/luz). Reaproveita a infraestrutura já pronta: autenticação, perfis (`CheckPerfil`), layout, exportação PDF/Excel. Roadmap completo e diagnóstico do problema em [`roadmap-modulo-processos-administrativos.md`](./roadmap-modulo-processos-administrativos.md) — leia esse arquivo antes de mexer nesse módulo, ele documenta o "porquê" de cada decisão de modelagem.

**Contexto de negócio, resumido:** a Secretaria de Obras controla os processos hoje numa planilha Excel em texto livre (`CONTROLE_PROCESSOS.xlsx`, 6 abas, mais de 2.000 processos ativos). O pedido central do Secretário é simples e específico: **saber em qual fase está cada processo e, se estiver parado, por qual motivo.** Esse pedido é o que orienta a arquitetura — `fase_atual_id` e `motivo_pendencia` em `processos` são os campos centrais do módulo, não um detalhe.

### ✅ Fase 7.1 — Fundação, Fases do Processo e CRUD Essencial *(concluída)*

CRUD completo de processos administrativos:

- **Processo**: número, requerente, endereço (estruturado — ver abaixo), tipo de serviço, responsável técnico, data de entrada, fase atual, setor/caixa físicos, motivo de pendência, situação (aberto/arquivado)
- **Trâmite**: linha do tempo de movimentações de um processo — mantém o hábito real de trabalho (texto livre) mas **exige escolher uma fase estruturada a cada lançamento**. É essa fase estruturada, atualizada a cada trâmite, que alimenta o dashboard e responde à pergunta do Secretário
- **Tipo de Processo**: os 12 serviços formais descritos pela Secretaria (alvará de construção, reforma, demolição, movimentação de terra, regularização, habite-se, certidão de uso do solo, diretrizes urbanísticas, ligação de água/energia, desdobro/unificação/desmembramento, muro de contenção, manutenção de iluminação pública) + 1 tipo de fallback ("Outros / A Classificar") usado pelo importador da Fase 7.2
- **Fase de Processo**: tabela de domínio (não enum fixo) com vocabulário inicial sugerido a partir do relato da cliente — **ainda não validado com as técnicas do Departamento de Obras Particulares**, ver seção 5 do roadmap
- **Responsável Técnico**: cadastro próprio, com atalho de criação rápida em modal a partir do formulário de processo (igual ao padrão já usado para "Nova Empresa" em Contratos), sem precisar sair da tela

**Endereço com busca por CEP**: ao digitar o CEP, busca automática via API pública ViaCEP (`fetch` client-side, sem custo de infraestrutura) preenchendo logradouro/bairro/cidade/UF. Número e complemento (apto, bloco, condomínio) são sempre manuais — a API não tem como saber esses dados. Accessor `endereco_completo` no model monta a linha única formatada para listagens e relatórios.

**Validação em português:** `lang/pt_BR/validation.php` traduz as mensagens padrão do Laravel para todo o sistema (não só Processos) — o projeto usava `APP_LOCALE=en` desde o início, então toda mensagem de erro de validação aparecia em inglês apesar da interface ser 100% em português. `APP_FALLBACK_LOCALE` continua `en` para não quebrar telas sem tradução própria (ex.: mensagens de autenticação do Breeze).

### ✅ Fase 7.2 — Importação do Histórico via Excel *(concluída)*

Importador idempotente da planilha legada, cobrindo as 6 abas reais (`CONTROLE`, `ÁGUA E LUZ`, `DESMEMBRAMENTOS_ACIMA_DE_02_LOT`, `SISOBRA`, `DESARQUIVAMENTO`, `RENOVAÇÃO_DE_ALVARÁ`):

- **Job em fila**: `App\Jobs\ImportarProcessosHistoricoJob` (`ShouldQueue`) + comando `php artisan processos:importar {caminho} {--sync}` (`--sync` roda na hora, sem precisar de worker — útil para testes locais)
- **Idempotente**: reprocessar o mesmo arquivo não duplica nada — chave natural `processos.processo_numero_normalizado` e `tramites.importacao_ref`
- **Classes auxiliares** em `App\Services\Importacao\`:
  - `NumeroProcessoNormalizer` — normaliza número de processo para dedup/busca
  - `TramiteCellParser` — interpreta célula de trâmite (número serial do Excel puro, texto livre com data embutida em qualquer posição, ou sem data nenhuma → fallback "data não informada")
  - `TipoProcessoMatcher` — casa o texto livre da coluna ASSUNTO com um dos 12 tipos formais por palavra-chave; o que não bate cai em "Outros / A Classificar" — **deliberadamente não tenta categorizar 100% automaticamente**
  - `ResponsavelTecnicoMatcher` — correspondência aproximada de nomes (limiar de 82% de similaridade, calibrado nos dados reais) para o problema real de grafias duplicadas (ex.: "PRISCILA DE JESUS GUERRA ANDRÉ" / "PRISCILA DE JESUS A. GUERRA")
  - Erros são isolados por linha (log + segue para a próxima), não abortam a importação inteira

> ⚠️ A planilha fonte (dados reais e sensíveis) **não fica no repositório** — salve em `storage/app/import/` (já no `.gitignore`) antes de rodar o comando.

### ✅ Fase 7.5 (parcial) — Dashboard e Relatórios *(concluída)*

**Dashboard executivo** (`/`, mesma tela do módulo de Obras): nova seção "Processos Administrativos" com KPIs de quantidade (total, abertos, arquivados, a classificar — cada card já linka para a listagem filtrada), gráfico de pizza por fase (a resposta visual direta ao pedido do Secretário), gráfico de barras por tipo de serviço, aba "Processos pendentes" no painel de alertas unificado (ao lado de contratos/convênios vencendo) e tabela de processos recentes.

**Relatórios** (`/relatorios/processos`) — hub próprio, mesma infraestrutura (dompdf + maatwebsite/excel) e mesmo padrão visual do hub de Obras (`/relatorios`), com seletor de módulo para trocar entre os dois:

| Tipo de relatório | Conteúdo |
|---|---|
| Geral | Todos os processos com tipo, fase, responsável e situação |
| Por Fase | Quantidade e % por fase de tramitação |
| Por Tipo | Quantidade e % por tipo de serviço |
| Pendências | Processos abertos com motivo de pendência registrado |
| Por Responsável | Ranking de responsáveis técnicos por quantidade de processos |

Filtros: tipo de processo · fase atual · responsável técnico · situação · período de entrada. Exportação em **PDF** (A4 paisagem, gráfico de distribuição por fase, tabelas) e **Excel** (5 abas: Resumo, Processos, Por Fase, Por Tipo, Pendências).

> ⚠️ **Nota técnica para quem mexer nisso depois:** a exportação em PDF limita a listagem completa a 500 linhas (`RelatorioProcessoController::LIMITE_LINHAS_PDF`) porque o dompdf estoura o limite de memória padrão do PHP (512 MB) em tabelas HTML muito grandes — descoberto rodando contra os ~2.275 processos reais importados na Fase 7.2. A exportação em Excel não tem esse limite (o `PhpSpreadsheet` lida bem com volume alto). Se precisar do PDF completo sem cortar, aumente `memory_limit` no PHP **e** ajuste a constante, não faça só uma das duas coisas.

Ainda não implementado da Fase 7.5: tempo médio de tramitação e processos com prazo vencido (dependem da Fase 7.3 — Prazos e Alertas, ainda não iniciada).

### 🔲 Próximas fases do módulo (ver roadmap dedicado)

- **Fase 7.3** — Prazos e alertas (regras de prazo por tipo de processo, dias sem movimentação, painel de vencidos)
- **Fase 7.4** — Sub-módulos específicos (Renovação de Alvará por mês, SISOBRA, Desarquivamento, Água e Luz com múltiplos relógios)
- **Fase 7.6** (opcional) — Consulta pública por número de processo, sem login

> Antes de fechar o vocabulário de fases/setores como definitivo, o roadmap recomenda uma chamada com as técnicas do Departamento de Obras Particulares — ainda não realizada.

---

## 🕓 Auditoria — Histórico de Atividades *(implementada localmente)*

Registra no sistema web **quem criou, alterou ou excluiu** registros de **Obras**, **Contratos** e **Medições** — inclusive os vínculos de **convênios** de cada obra (alterados via pivot, auditados no `ObraController`).

- **O que é gravado:** usuário, data/hora, ação (criou / alterou / excluiu), módulo, registro afetado, **valores anteriores e posteriores** (na alteração, apenas os campos que mudaram; na exclusão, o snapshot completo), IP e navegador. Campos técnicos (`id`, `created_at`, `updated_at`) e sensíveis (senhas, tokens) nunca são gravados.
- **Como funciona:** Observers (`app/Observers/`) + `App\Services\AuditoriaService`. A gravação acontece **após o commit** da transação — operação desfeita não gera histórico — e uma falha na auditoria só vai para o log, sem interromper a operação.
- **Exclusões em cascata:** ao excluir uma obra/contrato, o MySQL apaga contratos/medições vinculados sem eventos do Eloquent; a descrição do histórico informa quantos foram removidos junto.
- **Tela:** menu **Histórico de Atividades** (`/auditoria`), somente leitura, com filtros por usuário, período, módulo e ação, e detalhe "Campo | Antes | Depois" com nomes legíveis (status, empresa, obra, contrato, convênios).
- **Acesso:** apenas **Administrador** e **Secretário** (middleware `perfil:admin,secretario` + Gate `ver-auditoria`); técnico e operador recebem 403 mesmo digitando a URL.
- **Banco:** uma única tabela nova, `auditorias` — mudança aditiva, nenhuma tabela existente alterada. Em produção: `php artisan migrate --force`. O histórico é mantido indefinidamente (sem limpeza automática).
- **App executivo:** o histórico **não** aparece no aplicativo mobile, que continua somente consulta.

## 📱 FASE 8 — Aplicativo Mobile Executivo

App Flutter **somente leitura** para Prefeito e Secretário de Obras. O Laravel continua sendo a única fonte da verdade: o app apenas consome a API, sem regra de negócio própria.

- **MOB-01 — API Mobile MVP** *(implementada localmente, aguardando deploy)*: API REST versionada em `/api/v1` com Laravel Sanctum (tokens Bearer, validade de 30 dias). Endpoints: `POST login`, `POST logout`, `GET me`, `GET dashboard`, `GET obras` (`?search=`, `?status=`), `GET obras/{id}`, `GET contratos` (`?search=` número/obra/empresa, `?situacao=vigente|vence_em_breve|vencido|sem_vigencia`), `GET contratos/{id}`. Reaproveita os accessors de KPIs de `Obra`; os indicadores do dashboard ficam em `App\Services\DashboardObrasService`, com as mesmas regras do `DashboardController` web — se mudar uma regra lá, mude nos dois. Testes em `tests/Feature/Api/`.
- **MOB-02 — Flutter MVP** *(em andamento)*: app **Obras RGS** em [`mobile/`](mobile/) (somente consulta) — abas **Dashboard**, **Obras**, **Contratos** e **Perfil**. Obras e Contratos têm busca, filtro (status da obra / situação da vigência) e tela de detalhe; o detalhe do contrato abre a obra vinculada e os alertas de contratos no Dashboard abrem a lista já filtrada. O **Dashboard Executivo** consolida em uma única tela **Obras** (totais, execução financeira, status), **Contratos** (total, vigentes, vencendo, vencidos e execução financeira — cada indicador abre a lista de Contratos já filtrada) e **Alertas**; os dados vêm de `GET /api/v1/dashboard` (`obras`, `contratos`, `status`, `alertas`). Contratos são somente consulta (sem cadastro, edição, aditivos ou documentos). Token salvo apenas no `flutter_secure_storage`; 401 leva ao login. Dependências: `dio`, `flutter_secure_storage`, `flutter_riverpod`, `intl`.
- **MOB-03 — Offline First** *(implementado localmente)*: o app continua **somente consulta** — é cache de leitura, sem fila de escrita. Respostas da API ficam em **SQLite local** (`sqflite`, tabela genérica `cache_entries` com o JSON original, **separada por usuário**; `mobile/lib/core/cache/`). **Stale-while-revalidate**: Dashboard, Obras, Contratos e detalhes abrem na hora com o último dado salvo e atualizam em segundo plano. As listas de Obras e Contratos são sincronizadas **completas** (todas as páginas, `per_page=50`) e a busca/filtro rodam no aparelho; detalhes ficam salvos quando visitados (sem baixar todos). **Sessão offline**: com token ainda válido e usuário salvo, falha de conexão ou servidor fora do ar (5xx) no `GET /me` abre o app com os dados locais (a mesma regra vale para as consultas: rede ou 5xx → exibe o cache); **401 ou token vencido nunca abrem offline**. Sem conexão, as telas mostram "Modo offline" e a **última sincronização** (ex.: "Dados atualizados em 22/09/2026 às 08:42 (há 2 h)"). **Logout, 401 e sessão expirada apagam token, usuário salvo e todo o cache** do usuário. Login novo exige conexão. Nenhuma mudança no backend (usa os endpoints paginados existentes).

  ```bash
  cd mobile
  flutter pub get
  flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000   # Android Emulator + php artisan serve
  flutter test
  ```

  Sem `API_BASE_URL`, o app usa `http://10.0.2.2:8000` no Android e `http://localhost:8000` nas demais plataformas (`lib/core/config/app_config.dart`). HTTP sem TLS é liberado **só no build debug** (`android/app/src/debug/AndroidManifest.xml`); release exige HTTPS. O `applicationId` `com.example.obras_rgs` é provisório e precisa ser trocado antes de publicar na loja.
- **MOB-03 — Homologação e Produção** *(não iniciado)*: validação com usuários reais, deploy da API e distribuição do app.

**Pendências registradas:**

- **TECH-DEBT-MOB-01** — Centralizar futuramente as regras de indicadores do dashboard web e mobile em `DashboardObrasService` (hoje o `DashboardController` web mantém lógica equivalente).
- **SEC-01** — Bloquear autenticação/acesso web de usuários inativos (problema preexistente: o login do Breeze não verifica `ativo`; só as rotas com middleware `perfil:` bloqueiam). A API mobile já bloqueia.
- **BUG-01** — `Contrato::venceEm()` marca como "vence em breve" **qualquer** vigência futura: no Carbon 3, `diffInDays()` retorna valor com sinal (negativo para datas futuras). Afeta os selos das telas web de contratos e do detalhe da obra. A API de contratos não usa esse método (segue a regra do alerta do dashboard: vigência entre hoje e +30 dias). Correção sugerida: `now()->diffInDays($this->vigencia_contrato) <= $dias`.

---

## 🗄️ Banco de Dados

```
users
status_obras
categoria_convenios
orgaos_financiadores
empresas
demandas_propostas
obras
convenios
obra_convenio              ← pivot N:M obras ↔ convênios
contratos
execucao_obras
execucao_responsaveis      ← pivot medição ↔ usuário (com campo papel)
documentos                 ← polimórfico: obras, contratos, execucao_obras

-- Módulo Processos Administrativos (Fase 7.x) --
tipos_processo
fases_processo             ← domínio editável, não enum — vocabulário ainda não definitivo
responsaveis_tecnicos
processos                  ← fase_atual_id + motivo_pendencia = núcleo do módulo
tramites                   ← importacao_ref = chave de idempotência da importação Excel
desarquivamentos           ← processo_id nullable (ver Fase 7.2 no README)
renovacoes_alvara          ← processo_id nullable, agrupado por mes_referencia
```

---

## ⚙️ Instalação

### Pré-requisitos

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8+

### Passo a passo

```bash
# 1. Clonar o repositório
git clone https://github.com/seu-usuario/obras-prefeitura.git
cd obras-prefeitura

# 2. Instalar dependências PHP
composer install

# 3. Configurar o ambiente
cp .env.example .env
php artisan key:generate

# 4. Configurar o banco no .env
DB_DATABASE=prefeitura
DB_USERNAME=root
DB_PASSWORD=sua_senha

# 5. Rodar migrations
php artisan migrate --seed

# 6. Criar link de storage (uploads)
php artisan storage:link

# 7. Instalar dependências JS e compilar
npm install
npm run dev

# 8. Instalar dependências de relatórios (Fase 3)
composer require barryvdh/laravel-dompdf
composer require maatwebsite/excel
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
```

### Servidor de desenvolvimento

```bash
# Terminal 1 — assets (manter rodando)
npm run dev

# Terminal 2 — servidor Laravel
php artisan serve
```

Acesse: [http://localhost:8000](http://localhost:8000)

---

## 👤 Usuários padrão (seeders)

| E-mail                        | Senha           | Perfil    |
|-------------------------------|-----------------|-----------|
| jmarciosilva@gmail.com        | 12345678        | admin     |
| tecnico@riogrande.sp.gov.br   | Tecnico@2024!   | tecnico   |
| operador@riogrande.sp.gov.br  | Operador@2024!  | operador  |

> ⚠️ Altere as senhas após o primeiro acesso em produção.

---

## 📁 Estrutura de Arquivos

```
app/
├── Console/Commands/
│   └── ImportarProcessosHistorico.php  ← Fase 7.2
├── Exports/
│   ├── RelatorioExport.php
│   ├── RelatorioProcessoExport.php     ← Fase 7.5
│   └── Sheets/
│       ├── ResumoSheet.php
│       ├── ObrasSheet.php
│       ├── ExecucaoFinanceiraSheet.php
│       ├── ContratosVencendoSheet.php
│       ├── PorEmpresaSheet.php
│       └── Processos/                  ← Fase 7.5 (namespace próprio, não colide com as sheets acima)
│           ├── ResumoSheet.php
│           ├── ProcessosSheet.php
│           ├── PorFaseSheet.php
│           ├── PorTipoSheet.php
│           └── PendenciasSheet.php
├── Jobs/
│   └── ImportarProcessosHistoricoJob.php  ← Fase 7.2 (ShouldQueue)
├── Services/Importacao/                    ← Fase 7.2
│   ├── NumeroProcessoNormalizer.php
│   ├── TramiteCellParser.php
│   ├── TipoProcessoMatcher.php
│   └── ResponsavelTecnicoMatcher.php
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php     ← + seção Processos (Fase 7.5)
│   │   ├── ObraController.php          ← + método grafico() (Fase 4)
│   │   ├── ConvenioController.php
│   │   ├── ContratoController.php
│   │   ├── ExecucaoObraController.php
│   │   ├── DocumentoController.php
│   │   ├── RelatorioController.php     ← Fase 3
│   │   ├── ProcessoController.php      ← Fase 7.1
│   │   ├── TramiteController.php       ← Fase 7.1
│   │   ├── RelatorioProcessoController.php  ← Fase 7.5
│   │   └── Admin/
│   │       ├── UsuarioController.php
│   │       ├── EmpresaController.php
│   │       ├── StatusObraController.php
│   │       ├── CategoriaConvenioController.php
│   │       ├── OrgaoFinanciadorController.php
│   │       ├── DemandaPropostaController.php
│   │       └── ResponsavelTecnicoController.php  ← Fase 7.1
│   └── Middleware/
│       └── CheckPerfil.php
├── Models/
│   ├── User.php
│   ├── Obra.php               ← accessors: valor_medido, saldo_contratual, percentual_executado
│   ├── Convenio.php
│   ├── Contrato.php           ← estaVencido(), venceEm()
│   ├── ExecucaoObra.php       ← responsaveis (pivot), documentos (polimórfico)
│   ├── Documento.php          ← polimórfico, deleta arquivo físico no boot
│   ├── StatusObra.php
│   ├── CategoriaConvenio.php
│   ├── OrgaoFinanciador.php
│   ├── DemandaProposta.php
│   ├── Empresa.php
│   ├── Processo.php           ← Fase 7.1 — accessor endereco_completo
│   ├── Tramite.php            ← Fase 7.1
│   ├── TipoProcesso.php       ← Fase 7.1
│   ├── FaseProcesso.php       ← Fase 7.1 — domínio editável, não enum
│   ├── ResponsavelTecnico.php ← Fase 7.1 — $table explícito (plural irregular)
│   ├── Desarquivamento.php    ← Fase 7.2
│   └── RenovacaoAlvara.php    ← Fase 7.2
database/
├── migrations/
└── seeders/
    ├── TipoProcessoSeeder.php   ← Fase 7.1
    └── FaseProcessoSeeder.php   ← Fase 7.1 — vocabulário ainda não definitivo
lang/
├── pt_BR/
│   ├── validation.php  ← mensagens de validação em pt-BR (todo o sistema)
│   └── pagination.php
└── pt_BR.json           ← "Showing/to/of/results" da paginação
resources/views/
├── layouts/app.blade.php
├── dashboard.blade.php        ← + seção Processos (Fase 7.5)
├── obras/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php         ← Fase 4: gráficos Chart.js na aba Execuções
├── relatorios/                ← Fase 3
│   ├── index.blade.php        ← + seletor de módulo (Obras / Processos)
│   ├── preview.blade.php
│   ├── pdf.blade.php
│   └── processos/              ← Fase 7.5
│       ├── index.blade.php
│       ├── preview.blade.php
│       └── pdf.blade.php
├── processos/                   ← Fase 7.1
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── show.blade.php          ← linha do tempo de trâmites
│   └── _form.blade.php
├── convenios/
│   └── vincular-obras.blade.php
├── contratos/
├── execucoes/
├── admin/
│   ├── usuarios/
│   ├── empresas/
│   └── responsaveis-tecnicos/   ← Fase 7.1
└── components/
routes/
└── web.php
```

---

## 🗺️ Rotas principais

```
GET|POST   /obras                                        obras.index / store
GET        /obras/criar                                  obras.create
GET        /obras/visualizar/{obra}                      obras.show
GET        /obras/visualizar/{obra}/grafico              obras.grafico  ← Fase 4 (JSON)
GET|PUT    /obras/{obra}/editar                          obras.edit / update
DELETE     /obras/{obra}/excluir                         obras.destroy

GET|POST   /obras/{obra}/execucoes/criar                 obras.execucoes.create / store
GET|PUT    /obras/{obra}/execucoes/{ex}/editar           obras.execucoes.edit / update
DELETE     /obras/{obra}/execucoes/{ex}/excluir          obras.execucoes.destroy
GET        /obras/{obra}/execucoes/{ex}/documentos/{d}/download
DELETE     /obras/{obra}/execucoes/{ex}/documentos/{d}

POST       /obras/{obra}/documentos/upload               obras.documentos.store
GET        /obras/{obra}/documentos/{d}/download         obras.documentos.download
DELETE     /obras/{obra}/documentos/{d}/excluir          obras.documentos.destroy

GET|POST   /convenios                                    convenios.index / store
GET        /convenios/criar                              convenios.create
GET        /convenios/visualizar/{convenio}              convenios.show
GET|PUT    /convenios/{convenio}/editar                  convenios.edit / update
DELETE     /convenios/{convenio}/excluir                 convenios.destroy
GET        /convenios/{convenio}/obras                   convenios.obras
POST       /convenios/{convenio}/obras/vincular          convenios.vincular-obras
DELETE     /convenios/{convenio}/obras/{obra}            convenios.desvincular-obra

GET|POST   /contratos                                    contratos.index / store
GET        /contratos/criar                              contratos.create
GET        /contratos/visualizar/{contrato}              contratos.show
GET|PUT    /contratos/{contrato}/editar                  contratos.edit / update
DELETE     /contratos/{contrato}/excluir                 contratos.destroy

GET        /relatorios                                   relatorios.index   ← Fase 3
GET        /relatorios/preview                           relatorios.preview
GET        /relatorios/pdf                               relatorios.pdf
GET        /relatorios/excel                             relatorios.excel

GET|POST   /processos                                     processos.index / store  ← Fase 7.1
GET        /processos/criar                                processos.create
GET        /processos/visualizar/{processo}                processos.show
GET|PUT    /processos/{processo}/editar                    processos.edit / update
DELETE     /processos/{processo}/excluir                   processos.destroy
POST       /processos/{processo}/tramites/salvar           processos.tramites.store
DELETE     /processos/{processo}/tramites/{tramite}/excluir processos.tramites.destroy

GET        /relatorios/processos                           relatorios.processos.index   ← Fase 7.5
GET        /relatorios/processos/preview                   relatorios.processos.preview
GET        /relatorios/processos/pdf                       relatorios.processos.pdf
GET        /relatorios/processos/excel                     relatorios.processos.excel

/admin/usuarios              (CRUD + toggle ativo)
/admin/empresas              (CRUD)
/admin/status-obras          (CRUD)
/admin/categorias-convenio
/admin/orgaos-financiadores
/admin/demandas-propostas
/admin/responsaveis-tecnicos (CRUD — Fase 7.1; store também aceita perfil "tecnico", não só "admin")
```

---

## 📋 Roadmap

### ✅ Fase 1 — Base do sistema *(concluída)*
CRUD completo de obras, convênios, contratos, medições, empresas, usuários e documentos. Fluxo guiado, KPIs, controle de acesso por perfil e upload polimórfico de arquivos.

### ✅ Fase 2 — Dashboard executivo *(concluída)*
Painel com KPIs globais, gráficos de pizza e barras, alertas de vencimento e filtro por período.

### ✅ Fase 3 — Relatórios exportáveis *(concluída)*
PDF com gráficos SVG server-side e Excel com 5 abas. Filtros por status, empresa, órgão e período. Pré-visualização HTML antes da exportação.

### ✅ Fase 4 — Gráfico de evolução por obra *(concluída)*
Três gráficos Chart.js na aba Execuções: linha de evolução do percentual, barras de execução financeira por contrato e misto de valor por medição + acumulado. Projeção automática de conclusão com nível de confiança.

---

> ℹ️ **Numeração de fases:** a partir daqui, **Fase 5/6** continuam a numeração do módulo de **Obras**. O módulo de **Processos Administrativos** usa sua própria numeração, **Fase 7.x** (documentada em detalhe na seção [🗂️ Módulo Processos Administrativos](#-módulo-processos-administrativos-licenciamento-e-alvarás) acima e no roadmap dedicado). São duas frentes paralelas, não sequenciais entre si.

### ✅ Fase 7.1 — Fundação, Fases do Processo e CRUD Essencial *(concluída)*
### ✅ Fase 7.2 — Importação do Histórico via Excel *(concluída)*
### ✅ Fase 7.5 (parcial) — Dashboard e Relatórios *(concluída)*
Ver seção dedicada acima para detalhes. Pendente da Fase 7.5: métricas que dependem da Fase 7.3 (tempo médio de tramitação, prazo vencido).

### 🔲 Fase 7.3 — Prazos e alertas *(não iniciada)*
### 🔲 Fase 7.4 — Sub-módulos: Renovação de Alvará, SISOBRA, Desarquivamento, Água e Luz *(não iniciada)*
### 🔲 Fase 7.6 — Consulta pública por número de processo *(opcional, não iniciada)*

> Detalhamento completo, diagnóstico do problema real e riscos conhecidos em [`roadmap-modulo-processos-administrativos.md`](./roadmap-modulo-processos-administrativos.md).

---

### 🗺️ Fase 5 — Mapa de obras *(próxima)*
- Marcadores coloridos por status sobre OpenStreetMap (Leaflet.js, sem API key)
- Geocodificação automática pelo endereço via Nominatim
- Clique no marcador abre popup com resumo e link para o detalhe
- Migration adicional: `latitude` e `longitude` na tabela `obras`

---

### 🌐 Fase 6 — Portal público de transparência
- Página pública sem autenticação (`/portal`) para consulta pela população
- Exibe apenas obras e documentos marcados como `publico = true`
- Conformidade com a Lei de Acesso à Informação (LAI — Lei nº 12.527/2011)
- Cache com `Cache::remember()` para suportar acessos simultâneos
- Migration adicional: campo `publico` nas tabelas `obras` e `documentos`

---

## 👨‍💻 Autor

**José Márcio Ferreira da Silva**
Desenvolvedor Full Stack
Projeto desenvolvido para a Prefeitura Municipal de Rio Grande da Serra — SP.