# 🏗️ Sistema de Acompanhamento de Obras

## Prefeitura Municipal de Rio Grande da Serra — SP

Sistema web desenvolvido em Laravel para centralizar e acompanhar todas as obras municipais, convênios, contratos e execuções financiadas por recursos próprios ou repasses estaduais e federais.

---

## 🎯 Objetivos

- Centralizar o cadastro de obras municipais (em planejamento, execução e concluídas)
- Registrar convênios e seus respectivos órgãos financiadores
- Controlar contratos de licitação vinculados a cada obra
- Acompanhar o percentual de execução por meio de registros de medição
- Permitir o anexo de documentos (contratos, ART, fotos, atas) a obras e contratos
- Oferecer painel visual com indicadores (KPIs) para gestão municipal
- Controlar acesso por perfil de usuário (admin, técnico, operador)

---

## 🚀 Tecnologias

| Camada         | Tecnologia                             |
| -------------- | -------------------------------------- |
| Backend        | PHP 8.2 + Laravel 12                   |
| Frontend       | Blade + Tailwind CSS v3 + Alpine.js v3 |
| Banco de dados | MySQL 8                                |
| Build          | Vite + Laravel Vite Plugin             |
| Autenticação   | Laravel Breeze                         |

---

## 📦 Módulos da Fase 1

### 🏗️ Obras

Entidade central do sistema. Cada obra possui descrição, endereço, processo de execução, status, vínculo com convênios e demanda de origem.

### 📄 Convênios

Instrumentos jurídicos entre a prefeitura e os órgãos financiadores. Um convênio pode financiar várias obras (N:M via tabela `obra_convenio`).

### 📋 Contratos

Cada obra pode ter um ou mais contratos de licitação, vinculando a obra a uma empresa contratada com valor, prazo e número do processo.

### 📊 Execuções (Medições)

Registro histórico das medições de execução de cada contrato — data, valor medido, saldo contratual e percentual executado acumulado.

### 📎 Documentos

Upload de arquivos (PDF, imagens, planilhas) vinculados a obras, convênios ou contratos via relação polimórfica.

### 👥 Usuários e Perfis

Três perfis de acesso: **admin** (acesso total), **tecnico** (criar e editar), **operador** (somente leitura).

---

## 🗄️ Estrutura do Banco de Dados

```
users
status_obras
categoria_convenios
orgaos_financiadores
empresas
demandas_propostas
obras
convenios
obra_convenio          ← pivot N:M
contratos
execucao_obras
documentos
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

# 5. Rodar migrations e seeders
php artisan migrate --seed

# 6. Criar link de storage (para uploads)
php artisan storage:link

# 7. Instalar dependências JS e compilar
npm install
npm run dev
```

### Rodando o servidor

Abra **dois terminais**:

```bash
# Terminal 1 — assets (manter rodando durante o desenvolvimento)
npm run dev

# Terminal 2 — servidor Laravel
php artisan serve
```

Acesse: [http://localhost:8000](http://localhost:8000)

---

## 👤 Usuários padrão (seeders)

| E-mail                       | Senha          | Perfil   |
| ---------------------------- | -------------- | -------- |
| admin@riogrande.sp.gov.br    | Admin@2024!    | admin    |
| tecnico@riogrande.sp.gov.br  | Tecnico@2024!  | tecnico  |
| operador@riogrande.sp.gov.br | Operador@2024! | operador |

> ⚠️ Altere as senhas após o primeiro acesso em produção.

---

## 🔐 Controle de Acesso

O middleware `CheckPerfil` protege as rotas por perfil:

```php
// Apenas admin e técnico podem criar/editar
Route::middleware('perfil:admin,tecnico')->group(function () { ... });

// Apenas admin pode excluir
Route::middleware('perfil:admin')->group(function () { ... });
```

| Perfil     | Permissões                                                     |
| ---------- | -------------------------------------------------------------- |
| `admin`    | CRUD completo, gerenciamento de usuários e tabelas de apoio    |
| `tecnico`  | Criar e editar obras, contratos, medições e documentos         |
| `operador` | Somente leitura (visualização de obras, convênios e contratos) |

---

## 📁 Estrutura de Arquivos Relevantes

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── ObraController.php
│   │   ├── ConvenioController.php
│   │   ├── ContratoController.php
│   │   ├── ExecucaoObraController.php
│   │   ├── DocumentoController.php
│   │   └── Admin/
│   └── Middleware/
│       └── CheckPerfil.php
├── Models/
│   ├── User.php
│   ├── Obra.php
│   ├── Convenio.php
│   ├── Contrato.php
│   ├── ExecucaoObra.php
│   ├── Documento.php
│   ├── StatusObra.php
│   ├── CategoriaConvenio.php
│   ├── OrgaoFinanciador.php
│   ├── DemandaProposta.php
│   └── Empresa.php
database/
├── migrations/
└── seeders/
resources/
├── views/
│   ├── layouts/app.blade.php
│   ├── dashboard.blade.php
│   ├── components/
│   └── obras/
routes/
└── web.php
```

---

## 🧩 Componentes Blade

| Componente                                    | Uso                                 |
| --------------------------------------------- | ----------------------------------- |
| `<x-badge-status :status="$obra->status" />`  | Badge colorido com o status da obra |
| `<x-card-stat titulo="..." valor="..." />`    | Card de KPI para o dashboard        |
| `<x-form-input name="..." label="..." />`     | Input com validação automática      |
| `<x-form-select name="..." :options="..." />` | Select com validação automática     |
| `<x-confirm-delete :action="..." />`          | Modal de confirmação de exclusão    |

---

## 🗺️ Rotas principais

```
GET    /                               → dashboard
GET    /obras                          → obras.index
GET    /obras/{obra}                   → obras.show
GET    /obras/criar                    → obras.create
POST   /obras                          → obras.store
GET    /obras/{obra}/editar            → obras.edit
PUT    /obras/{obra}                   → obras.update
DELETE /obras/{obra}                   → obras.destroy

GET    /convenios                      → convenios.index
GET    /contratos                      → contratos.index

POST   /obras/{obra}/documentos        → obras.documentos.store
GET    /obras/{obra}/documentos/{doc}/download

/admin/usuarios
/admin/empresas
/admin/status-obras
/admin/categorias-convenio
/admin/orgaos-financiadores
/admin/demandas-propostas
```

---

# 📋 Roadmap — Próximas Fases

## ✅ Fase 1 — Base do sistema

### 🏗️ Obras

Entidade central do sistema. Cada obra possui descrição, endereço, processo de execução, status, vínculo com convênios e demanda de origem.

### 📄 Convênios

Instrumentos jurídicos entre a prefeitura e os órgãos financiadores. Um convênio pode financiar várias obras (N:M via tabela `obra_convenio`).

### 📋 Contratos

Cada obra pode ter um ou mais contratos de licitação, vinculando a obra a uma empresa contratada com valor, prazo e número do processo.

### 📊 Execuções (Medições)

Registro histórico das medições de execução de cada contrato — data, valor medido, saldo contratual e percentual executado acumulado.

### 📎 Documentos

Upload de arquivos (PDF, imagens, planilhas) vinculados a obras, convênios ou contratos via relação polimórfica.

### 👥 Usuários e Perfis

Três perfis de acesso: **admin** (acesso total), **tecnico** (criar e editar), **operador** (somente leitura).

---

### 🔧 Fase 2 — Views de Convênios, Contratos e área Admin completa

Objetivo: completar todas as telas de CRUD que ainda não possuem views, tornando o sistema totalmente operacional para uso diário.

**Convênios**

- Listagem com filtros por órgão financiador, categoria e período de vigência
- Formulário de criação e edição com campos: número, descrição, categoria, órgão, valor de repasse, data de assinatura, vigência e número PRESCON
- Tela de detalhe mostrando obras vinculadas e documentos anexados

**Contratos**

- Listagem com filtros por obra e empresa
- Formulário de criação e edição com vinculação à obra e à empresa contratada
- Tela de detalhe com histórico de medições e linha do tempo de execução

**Área Admin**

- CRUD de Usuários com ativação/inativação e redefinição de senha
- CRUD de Empresas contratadas
- CRUD de Status de Obras com definição de cor e ordem de exibição
- CRUD de Categorias de Convênio
- CRUD de Órgãos Financiadores com classificação por esfera (federal, estadual, municipal)
- CRUD de Demandas e Propostas com controle de situação

---

### 📊 Fase 3 — Relatórios exportáveis (PDF e Excel)

Objetivo: oferecer aos gestores relatórios prontos para apresentações, auditorias e prestações de contas.

**Relatórios previstos**

- **Por status:** lista todas as obras agrupadas por status com totalizadores
- **Por período:** obras cadastradas ou atualizadas em um intervalo de datas
- **Por órgão financiador:** obras e valores agrupados por órgão e esfera de governo
- **Por empresa contratada:** contratos e valores por empresa, com situação de cada contrato
- **Execução financeira:** valor total contratado vs. valor medido vs. saldo, por obra ou por período
- **Obras com vigência próxima do vencimento:** alerta de contratos vencendo nos próximos 30/60/90 dias

**Tecnologia**

- PDF via [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf) com templates Blade
- Excel via [maatwebsite/laravel-excel](https://laravel-excel.com/) com formatação de células e cabeçalhos
- Filtros de período, status e órgão diretamente na tela antes de exportar

```bash
composer require barryvdh/laravel-dompdf
composer require maatwebsite/excel
```

---

### 📈 Fase 4 — Gráfico de evolução por medição na tela de detalhe da obra

Objetivo: tornar visível o progresso financeiro e físico de cada obra ao longo do tempo.

**Funcionalidades**

- Gráfico de linha (Chart.js) na tela `obras.show` mostrando a evolução do percentual executado a cada medição registrada
- Gráfico de barras comparando valor contratado × valor medido acumulado × saldo contratual
- Tabela detalhada de todas as medições com data, valor, percentual e observações
- Indicador visual de progresso (barra horizontal) no card de resumo da obra
- Alerta automático quando o percentual executado ultrapassar 90% do valor contratado

**Dados exibidos por obra**

- Data da primeira e da última medição
- Total medido acumulado em R$
- Percentual executado atual
- Saldo contratual restante
- Projeção de conclusão baseada no ritmo das últimas medições

**Tecnologia**

- [Chart.js v4](https://www.chartjs.org/) já incluso no projeto via CDN
- Dados passados pelo controller como JSON via `@json()` no Blade
- Nenhuma dependência adicional necessária

---

### 🗺️ Fase 5 — Mapa de obras integrado

Objetivo: permitir visualização geográfica de todas as obras do município em um mapa interativo.

**Funcionalidades**

- Mapa interativo na tela de listagem de obras com marcadores coloridos por status
- Clique no marcador abre um popup com nome, status, endereço e link para o detalhe da obra
- Filtro por status diretamente no mapa (ex: exibir apenas obras em execução)
- Visualização por bairro ou região com agrupamento de marcadores (cluster)
- Campos de latitude e longitude adicionados ao cadastro de obra com geocodificação automática pelo endereço

**Migration adicional**

```php
// Adicionar à tabela obras
$table->decimal('latitude', 10, 7)->nullable();
$table->decimal('longitude', 10, 7)->nullable();
```

**Tecnologia**

- [Leaflet.js](https://leafletjs.com/) com tiles do OpenStreetMap — gratuito, sem necessidade de API key
- Plugin [Leaflet.markercluster](https://github.com/Leaflet/Leaflet.markercluster) para agrupamento de marcadores
- Geocodificação via API do Nominatim (OpenStreetMap) ou Google Geocoding API
- Alternativa paga: Google Maps JavaScript API

---

### 🌐 Fase 6 — Portal público de consulta de obras

Objetivo: oferecer à população de Rio Grande da Serra uma página pública para consultar as obras do município, promovendo transparência e controle social em conformidade com a Lei de Acesso à Informação (LAI — Lei nº 12.527/2011).

**Funcionalidades**

- Página pública acessível sem autenticação em rota separada (`/portal` ou `/obras-publicas`)
- Listagem de obras com filtros por status, bairro e categoria de convênio
- Tela de detalhe de cada obra exibindo: descrição, localização, status, órgão financiador, valor contratado, percentual executado e documentos públicos anexados
- Barra de progresso visual do percentual executado
- Mapa com a localização da obra (integração com a Fase 5)
- Campo de busca por endereço ou nome da obra
- Seção de transparência com valor total investido em obras por ano e por órgão financiador

**Segurança e separação de dados**

- O portal exibe apenas obras e documentos marcados como `publico = true`
- Dados sensíveis (usuários, processos internos, contratos sigilosos) nunca são expostos
- Layout próprio e simplificado, sem sidebar de administração
- Responsivo para acesso via celular pela população

**Migration adicional**

```php
// Adicionar à tabela obras
$table->boolean('publico')->default(false);

// Adicionar à tabela documentos
$table->boolean('publico')->default(false);
```

**Tecnologia**

- Rotas sem middleware `auth`, protegidas apenas pelo escopo `publico`
- Cache de consultas com `Cache::remember()` para melhorar a performance em acessos simultâneos
- Possibilidade futura de API REST pública para integração com o portal oficial da prefeitura

---

## 👨‍💻 Autor

**José Márcio Ferreira da Silva**
Desenvolvedor Full Stack
Projeto desenvolvido para a Prefeitura Municipal de Rio Grande da Serra — SP.
