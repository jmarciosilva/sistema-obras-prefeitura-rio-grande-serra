# 🏗️ Sistema de Acompanhamento de Obras Públicas

**Prefeitura Municipal de Rio Grande da Serra — SP**

Sistema web desenvolvido em Laravel 12 para centralizar e acompanhar todas as obras municipais, convênios, contratos, medições de execução e documentos financiados por recursos próprios ou repasses estaduais e federais.

---

## 🎯 Objetivos

- Centralizar o cadastro de obras municipais em qualquer fase (planejamento, execução, concluída)
- Registrar convênios, categorias e órgãos financiadores com vínculo direto às obras
- Controlar contratos de licitação com empresa, valor, prazo e alertas de vigência
- Acompanhar execução financeira por medições com cálculo automático de saldo e percentual
- Registrar responsáveis por medição (engenheiro, fiscal, supervisor) para fins de auditoria
- Anexar documentos (boletins, fotos, planilhas, contratos, atas) a obras e medições
- Oferecer painel visual com KPIs, gráficos de evolução e projeção de conclusão por obra
- Exportar relatórios em PDF (com gráficos) e Excel (múltiplas abas)
- Controlar acesso por perfil de usuário (admin, técnico, secretário, operador)

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

M�dulo completo de geração de relatórios acessível em `/relatorios`:

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
| admin@riogrande.sp.gov.br     | Admin@2024!     | admin     |
| tecnico@riogrande.sp.gov.br   | Tecnico@2024!   | tecnico   |
| operador@riogrande.sp.gov.br  | Operador@2024!  | operador  |

> ⚠️ Altere as senhas após o primeiro acesso em produção.

---

## 📁 Estrutura de Arquivos

```
app/
├── Exports/
│   ├── RelatorioExport.php
│   └── Sheets/
│       ├── ResumoSheet.php
│       ├── ObrasSheet.php
│       ├── ExecucaoFinanceiraSheet.php
│       ├── ContratosVencendoSheet.php
│       └── PorEmpresaSheet.php
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── ObraController.php          ← + método grafico() (Fase 4)
│   │   ├── ConvenioController.php
│   │   ├── ContratoController.php
│   │   ├── ExecucaoObraController.php
│   │   ├── DocumentoController.php
│   │   ├── RelatorioController.php     ← Fase 3
│   │   └── Admin/
│   │       ├── UsuarioController.php
│   │       ├── EmpresaController.php
│   │       ├── StatusObraController.php
│   │       ├── CategoriaConvenioController.php
│   │       ├── OrgaoFinanciadorController.php
│   │       └── DemandaPropostaController.php
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
│   └── Empresa.php
database/
├── migrations/
└── seeders/
resources/views/
├── layouts/app.blade.php
├── dashboard.blade.php
├── obras/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php         ← Fase 4: gráficos Chart.js na aba Execuções
├── relatorios/                ← Fase 3
│   ├── index.blade.php
│   ├── preview.blade.php
│   └── pdf.blade.php
├── convenios/
│   └── vincular-obras.blade.php
├── contratos/
├── execucoes/
├── admin/
│   ├── usuarios/
│   └── empresas/
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

/admin/usuarios          (CRUD + toggle ativo)
/admin/empresas          (CRUD)
/admin/status-obras      (CRUD)
/admin/categorias-convenio
/admin/orgaos-financiadores
/admin/demandas-propostas
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