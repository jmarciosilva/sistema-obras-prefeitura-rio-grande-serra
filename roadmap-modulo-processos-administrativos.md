# 🗂️ Roadmap — Módulo de Processos Administrativos (Licenciamento e Alvarás)

**Sistema de Acompanhamento de Obras Públicas — Prefeitura Municipal de Rio Grande da Serra — SP**

Este documento propõe o roadmap de desenvolvimento para uma **nova frente do sistema Laravel**, a partir da análise de dois materiais fornecidos pela Secretaria de Obras e Planejamento:

1. `Serviços001.docx` — descrição formal dos serviços, etapas de tramitação e prazos legais.
2. `CONTROLE_PROCESSOS.xlsx` — planilha real usada hoje para controlar os processos (6 abas, controle manual em texto livre).
3. **Áudio da cliente (Secretaria de Obras)** — relato de como o processo funciona na prática hoje e do que o Secretário está cobrando da equipe (v. seção 1.3).

---

## 1. Diagnóstico

### 1.1 O que o Word descreve (processo formal)

A Secretaria oferece 12 serviços principais, todos abertos via Setor de Protocolo e analisados pela Secretaria de Obras:

| Serviço | Prazo legal (resumo) |
|---|---|
| Alvará de Construção / Reforma / Demolição / Movimentação de Terra / Regularização | Vistoria em até 15 dias; análise completa e emissão em até 30 dias úteis; até 3 notificações de 30 dias cada em caso de pendência |
| Habite-se (Conclusão de Obra) | Encaminhamento à Fiscalização e vistoria em até 30 dias cada |
| Certidão de Uso e Ocupação do Solo / Diretrizes Urbanísticas | Até 30 dias; até 2 notificações |
| Ofício de Ligação de Água/Energia | Até 10 dias úteis |
| Alvará de Desdobro/Unificação/Desmembramento, Muro de Contenção, Manutenção de Iluminação Pública | Sem prazo detalhado no documento — a confirmar com a Secretaria |

Todos os fluxos convergem para um padrão comum: **Protocolo → Cadastro → Análise → Vistoria (quando aplicável) → Notificação/Correção (0 a 3 ciclos) → Emissão → CTM (boleto) → Pagamento → Retirada → Arquivo/SISOBRA**.

### 1.2 O que o Excel mostra (prática real)

A planilha é, na prática, um **log cronológico de tramitação em texto livre**, não um sistema de status. Principais achados:

- **Aba `CONTROLE`**: coluna por processo, com até **25 colunas "Nº Trâmite – Data"**, cada uma contendo texto livre misturando data, ação, setor e observações (ex.: `"NOTIFICADO VIA EMAIL EM 21/10/2025 - AGUARDAR CX 12 NOVEMBRO"`). Mais de mil processos cadastrados, alguns com décadas de histórico (desde 1988).
- **Aba `RENOVAÇÃO_DE_ALVARÁ`**: mesma estrutura, mas agrupada por **mês do ano** (janeiro a dezembro), controlando renovações anuais de alvará.
- **Aba `DESMEMBRAMENTOS_ACIMA_DE_02_LOTES`**: subconjunto de processos de desdobro/desmembramento com mais de 2 lotes — regra de negócio especial.
- **Aba `SISOBRA`**: lista simples (processo/nome/assunto) de processos cadastrados no Cadastro Federal da Construção.
- **Aba `DESARQUIVAMENTO`**: processo, data da solicitação e motivo do desarquivamento.
- **Aba `ÁGUA E LUZ`**: pedidos de ligação/religação de água e energia (1º, 2º, 3º relógio etc.), com o mesmo padrão de trâmites em texto livre.

Padrões recorrentes no texto livre que valem a pena estruturar como dados:
- **Setor de destino**: `CTM`, `SVMA`/`SECLIMA`, `Protocolo`, `Fiscalização`, `SAJ` (jurídico), `Secretário` (assinatura).
- **Tipo de evento**: notificação (correio/e-mail/telefone), vistoria, emissão de boletolo, juntada de documento, arquivamento, desarquivamento.
- **Localização física**: "Caixa X do mês Y" — controle de onde o processo está fisicamente guardado.
- **Processos relacionados**: referências como `AC 449/2020-5`, `Vol. I/II`, "acompanha processo XXXX" — hoje resolvidas só por texto.

### 1.3 O que o áudio esclarece (relato da cliente)

A cliente da Secretaria de Obras gravou um áudio explicando o fluxo real e, principalmente, **o que o Secretário Júnior está cobrando da equipe**. Pontos-chave:

- **Processo é 100% físico** (papel), não digital. A dificuldade relatada é justamente **acompanhar** onde cada processo está.
- Fluxo confirmado, com um detalhe novo importante — existem **dois sistemas/registros distintos hoje**:
  1. Quando o processo físico chega em Obras, a equipe **"dá um assento"** (registra a entrada) em **um sistema que a Prefeitura já possui** (a confirmar qual é — provavelmente um protocolo/malote genérico da Prefeitura, não o sistema Laravel).
  2. Em seguida, o processo é encaminhado ao **Departamento de Obras Particulares**, onde a técnica registra o processo **na planilha** (a planilha `CONTROLE_PROCESSOS.xlsx` analisada acima) — esse é o controle de fato usado no dia a dia.
- Fluxo de uma primeira análise, na fala da cliente:
  `Protocolo (abre processo, paga taxa) → Obras (dá entrada/"assento") → Departamento de Obras Particulares (registra na planilha) → Primeira Análise → [documentação OK → emite alvará] OU [documentação pendente → Notificação (e-mail/correspondência) → processo aguarda em caixa física até o técnico responsável responder → Reanálise → ...]`
  Esse ciclo pode se repetir e, segundo a cliente, **o processo todo às vezes leva meses**.
- **O pedido central do Secretário** (a razão de ser deste módulo) é: **ter acesso para saber em qual fase cada processo está**, e, se estiver parado, **por qual motivo**. Ela deu exemplos explícitos de fases/status que o dashboard precisa mostrar:
  - `Encaminhado para Primeira Análise`
  - `Notificado — aguardando devolutiva do técnico`
  - `Em Reanálise`
  - (implícito no restante do relato) `Emitido`, `Arquivado`, etc.
- **Volume real é maior do que a planilha principal sugere**: a cliente menciona **mais de 2.000 processos em andamento** hoje na Secretaria (a aba `CONTROLE` tem pouco mais de mil linhas — o restante provavelmente está distribuído nas outras abas e/ou em processos ainda não digitalizados em planilha nenhuma).
- Ela se ofereceu para uma **chamada de vídeo com as técnicas que operam os processos no dia a dia**, o que é fortemente recomendável antes de fechar o vocabulário definitivo de fases/setores.
- Ela também sinalizou que a Secretaria pode **fornecer uma planilha/modelo específico para cadastro dos processos daqui pra frente** — o que reforça a necessidade de um período de convivência entre planilha e sistema (já previsto na seção de riscos).

**Implicação direta para o roadmap**: o pedido do Secretário não é "ter um sistema completo de licenciamento" — é, primeiro, **visibilidade de fase e motivo de pendência por processo**. Isso muda a prioridade das fases abaixo (ver seção 4): o campo `situacao`/fase deixa de ser um "extra opcional" (como propus inicialmente) e passa a ser **o requisito central do MVP**.

---

## 2. Lacuna entre os dois documentos

| | Word (formal) | Excel (real) |
|---|---|---|
| Estrutura | Etapas fixas por tipo de serviço | Texto livre, sem categorização |
| Prazos | Definidos em dias | Não calculados, apenas mencionados no texto |
| Status | Implícito na etapa do fluxo | Inexistente — infere-se lendo a última observação |
| Múltiplos serviços | 12 serviços descritos | Aparecem outros não descritos no Word: SISOBRA, Desarquivamento, Renovação de Alvará, Água e Luz |

O sistema precisa **acomodar a realidade da planilha** (histórico livre, editável a qualquer momento) e, ao mesmo tempo, **aplicar as regras formais do Word** (prazos, alertas, etapas) de forma incremental — sem forçar os servidores a mudar o hábito de trabalho de uma vez.

---

## 3. Proposta de arquitetura (Laravel)

Um novo domínio, **"Processos"**, paralelo ao domínio "Obras" já existente, reaproveitando o que já está pronto (autenticação, perfis, upload polimórfico de documentos, dashboards, exportação PDF/Excel).

### 3.1 Modelagem sugerida

```
tipos_processo          ← seed com os 12 serviços do Word + SISOBRA, Água e Luz, Renovação, Desarquivamento
fases_processo           ← tabela de domínio: Protocolado, Recebido em Obras, Em Primeira Análise,
                            Notificado (aguardando devolutiva), Em Reanálise, Emitido, Aguardando Boleto/
                            Pagamento, Retirada de Documentação, Arquivado — ajustar junto com as técnicas
processos                processo_numero, processo_numero_normalizado, requerente,
                          endereco, tipo_processo_id, responsavel_tecnico_id (nullable),
                          data_entrada, fase_atual_id (FK fases_processo), setor_atual,
                          caixa_atual, motivo_pendencia (nullable), situacao (aberto/arquivado)
processo_relacionados    self-relationship (AC, volumes, "acompanha processo X")
tramites                 processo_id, data, descricao (texto livre, obrigatório),
                          fase_id (FK fases_processo, nullable), setor_destino (enum nullable),
                          tipo_evento (enum nullable)
responsaveis_tecnicos    nome, registro (CREA/CAU), telefone
desarquivamentos         processo_id, data_solicitacao, motivo
renovacoes_alvara        processo_id (alvará original), data_renovacao, mes_referencia
prazos_legais            tipo_processo_id, etapa, prazo_dias   ← base para cálculo de alertas
```

**Mudança de prioridade após o áudio**: inicialmente eu havia deixado `setor_destino`/`tipo_evento` como campos totalmente opcionais dentro do trâmite em texto livre. Como o pedido central do Secretário é **"em qual fase está o processo, e por quê"**, a `fase_atual_id` do processo passa a ser um campo **de preenchimento obrigatório a cada novo trâmite** (a técnica escolhe a fase num select ao registrar a movimentação) — o texto livre continua existindo para o detalhe, mas a fase estruturada é o que alimenta o dashboard que o Secretário pediu. Isso é uma mudança pequena de esforço técnico, mas é o núcleo de valor do projeto.

### 3.2 Por que não caber dentro do domínio "Obras"

Processos administrativos (alvará, certidão, ligação de água) e obras públicas (execução de contratos financiados) são conceitos distintos — o primeiro é **licenciamento urbano**, o segundo é **gestão de investimento público**. Recomendo módulo separado, mas no mesmo projeto Laravel, reaproveitando:
- Sistema de perfis (`admin`, `tecnico`, `secretario`, `operador`) e middleware `CheckPerfil`
- Upload polimórfico de `Documento`
- Infraestrutura de exportação (dompdf + maatwebsite/excel)
- Layout/dashboard existentes

---

## 4. Fases de desenvolvimento

> Reordenado após o áudio: o pedido explícito do Secretário (fase atual + motivo de pendência) vira o núcleo do MVP, antes de qualquer importação em massa do histórico. Assim a Secretaria já vê valor rodando com processos novos cadastrados manualmente, enquanto a importação do legado (mais trabalhosa) roda em paralelo.

### ✅ Fase 7.1 — Fundação, Fases do Processo e CRUD Essencial (MVP)
- Migrations: `tipos_processo`, `fases_processo`, `processos`, `tramites`, `responsaveis_tecnicos`
- Seeder de `tipos_processo` (12 serviços do Word) e `fases_processo` — **vocabulário a validar na chamada com as técnicas** (sugestão inicial: Protocolado → Recebido em Obras → Em Primeira Análise → Notificado/Aguardando Devolutiva → Em Reanálise → Emitido/Encaminhado ao CTM → Aguardando Pagamento → Retirada de Documentação → Arquivado)
- CRUD de `Processos` com `fase_atual` e `motivo_pendencia` sempre visíveis na listagem
- Tela de detalhe com **linha do tempo de trâmites**: cada novo trâmite exige escolher a fase (select) + texto livre da observação (mantendo o hábito atual de registrar em texto)
- Filtros por tipo, responsável técnico, endereço e, principalmente, **fase atual**
- **Esta fase sozinha já responde à pergunta do Secretário** ("em qual fase está o processo X, e por quê") para qualquer processo cadastrado a partir de agora

### ✅ Fase 7.2 — Importação do Histórico (Excel)
- **Script/Job de importação do Excel** (usando `maatwebsite/excel`, já presente no projeto):
  - "Despivotar" as colunas `1º Trâmite` a `25º Trâmite` em linhas da tabela `tramites`
  - Normalizar número do processo (regex para separar `1831/2019-5`, `2107/2023 Vol. 1`, `1045/92 AC 1130/1992` etc.)
  - Converter datas seriais do Excel (`43719`, `44106`) para `Carbon`, com fallback para "data não informada" quando o campo tem apenas texto
  - Importar as 6 abas para as tabelas corretas (`ÁGUA E LUZ` → `tipos_processo` específico; `DESARQUIVAMENTO` → tabela dedicada; `SISOBRA` → flag em `processos`)
  - Rodar via `Job` em fila (volume alto — cliente fala em 2.000+ processos ativos —, evitar timeout de request), com log de erros e possibilidade de reprocessar (idempotente)
  - **Mapear o texto livre importado para uma fase inicial "A classificar"**, já que o histórico da planilha não tem fase estruturada — a fase correta só passa a existir a partir do primeiro trâmite lançado no novo sistema
- Não tentar categorizar 100% do texto livre histórico automaticamente — permitir curadoria manual progressiva

### ✅ Fase 7.3 — Prazos e Alertas
- Regras de prazo por `tipo_processo` (baseadas no Word: vistoria, análise, notificações)
- Cálculo de "dias sem movimentação" desde o último trâmite e "dias na fase atual"
- Painel de alertas (mesmo padrão do dashboard de Obras): processos parados há mais de X dias, notificações com prazo vencido, aguardando boleto/pagamento

### ✅ Fase 7.4 — Sub-módulos Específicos
- **Renovação de Alvará**: tela dedicada, agrupada por mês (espelhando a aba atual), vinculada ao alvará original
- **SISOBRA**: flag + data de cadastro no processo
- **Desarquivamento**: histórico de solicitações e motivos
- **Água e Luz**: cadastro específico permitindo múltiplos "relógios" por endereço

### ✅ Fase 7.5 — Dashboard Executivo e Relatórios
- Visão consolidada por fase (quantos processos em cada etapa, tempo médio por fase)
- KPIs: processos por tipo/status, tempo médio de tramitação, processos com prazo vencido
- Exportação PDF/Excel reaproveitando a infraestrutura da Fase 3 do sistema de Obras

### 🔲 Fase 7.6 (opcional) — Consulta Pública
- Página para o cidadão consultar o andamento pelo número do processo, sem login — alinhado com a Fase 6 (Portal de Transparência) já planejada para o sistema de Obras

---

## 5. Riscos e pontos de atenção

- **Qualidade dos dados**: datas inconsistentes, número de processo reaproveitado/duplicado (ex.: `1508/2018-1` aparece em datas diferentes), abreviações e erros de digitação no texto livre. Migração deve preservar o texto original e permitir correção manual gradual.
- **Volume**: mais de mil processos só na aba `CONTROLE`, com até 25 trâmites cada — validar performance de índices (`processo_numero`, `tipo_processo_id`, `data_entrada`) desde já.
- **Responsáveis técnicos duplicados**: mesma pessoa aparece com grafias diferentes (ex. "PRISCILA DE JESUS GUERRA ANDRÉ" / "PRISCILA DE JESUS A. GUERRA") — considerar normalização ou correspondência aproximada (fuzzy matching) na importação, com revisão manual.
- **Convivência com a planilha**: definir um período de transição em paralelo (planilha + sistema) até a equipe confiar 100% no novo módulo. A cliente já sinalizou que pode fornecer um **novo modelo de planilha** para cadastro — vale entender se esse modelo deveria, na verdade, virar diretamente o formulário do sistema.
- **Dois registros de entrada distintos hoje** ("assento" no sistema genérico da Prefeitura ao chegar em Obras + registro na planilha do Departamento de Obras Particulares) — precisa esclarecer se esse "sistema" da Prefeitura tem alguma integração possível (número de protocolo, API, exportação) ou se é só um controle interno sem relação com este projeto.
- **Vocabulário de fases ainda não fechado**: os nomes de fase citados no áudio (`Primeira Análise`, `Notificado`, `Reanálise`...) são um bom ponto de partida, mas precisam ser validados com as técnicas que operam o processo no dia a dia antes de virar enum fixo no banco — mudar um enum depois de dados reais lançados é mais custoso do que ajustar antes.

## 6. Próximos passos imediatos

1. **Agendar a chamada de vídeo com as técnicas do Departamento de Obras Particulares** (oferecida pela cliente) — é o passo mais importante antes de codar, para fechar o vocabulário definitivo de fases e setores
2. Validar com a Secretaria o escopo mínimo do MVP (sugestão: Fase 7.1 isolada primeiro — fase/motivo visível — antes de partir para a importação do histórico)
3. Confirmar os prazos legais dos serviços que o Word não detalhou (Desdobro, Muro de Contenção, Manutenção de Iluminação)
4. Confirmar a lista definitiva de setores de destino (Protocolo, CTM, SVMA/SECLIMA, Fiscalização, SAJ) para modelar o enum `setor_destino`
5. Entender a diferença entre o "sistema" onde hoje é dado o "assento" do processo e o sistema Laravel — evitar duplicar cadastro sem necessidade
6. Rodar uma importação de teste com uma amostra da planilha para validar o parser de trâmites e datas antes de escrever o importador completo
