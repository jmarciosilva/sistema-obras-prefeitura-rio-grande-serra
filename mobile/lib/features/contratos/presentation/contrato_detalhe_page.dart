import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../shared/utils/formatters.dart';
import '../../../shared/widgets/componentes.dart';
import '../../../shared/widgets/estados.dart';
import '../../obras/presentation/obra_detalhe_page.dart';
import '../data/contratos_api.dart';
import '../models/contrato.dart';
import 'situacao_chip.dart';

class ContratoDetalhePage extends ConsumerWidget {
  const ContratoDetalhePage({super.key, required this.id});

  final int id;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final detalhe = ref.watch(contratoDetalheProvider(id));

    return Scaffold(
      appBar: const InstitucionalAppBar(titulo: 'Detalhe do contrato'),
      body: detalhe.when(
        skipLoadingOnRefresh: !detalhe.hasError,
        loading: () => const LoadingView(),
        error: (e, _) => ErrorView(
          erro: e,
          onRetry: () => ref.invalidate(contratoDetalheProvider(id)),
        ),
        data: (d) => RefreshIndicator(
          onRefresh: () async {
            try {
              ref.invalidate(contratoDetalheProvider(id));
              await ref.read(contratoDetalheProvider(id).future);
            } catch (_) {
              // O erro já aparece na tela.
            }
          },
          child: _Conteudo(detalhe: d),
        ),
      ),
    );
  }
}

class _Conteudo extends StatelessWidget {
  const _Conteudo({required this.detalhe});

  final ContratoDetalhe detalhe;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    final c = detalhe.contrato;

    return ListView(
      padding: const EdgeInsets.fromLTRB(12, 8, 12, 24),
      children: [
        // 1 — Contrato
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  c.titulo,
                  style: tema.textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 10),
                SituacaoChip(situacao: c.situacao),
                const SizedBox(height: 10),
                LinhaIcone(
                  icone: Icons.gavel_outlined,
                  texto: c.processoLicitacao == null
                      ? 'Processo licitatório não informado'
                      : 'Processo licitatório ${c.processoLicitacao}',
                ),
              ],
            ),
          ),
        ),

        // 2 — Obra (toque abre o detalhe da obra já existente)
        SecaoCard(
          titulo: 'Obra',
          icone: Icons.apartment_outlined,
          child: c.obra == null
              ? const VazioInline(
                  texto: 'Nenhuma obra vinculada.',
                  icone: Icons.apartment_outlined,
                )
              : InkWell(
                  borderRadius: BorderRadius.circular(8),
                  onTap: () => Navigator.of(context).push(
                    MaterialPageRoute<void>(
                      builder: (_) => ObraDetalhePage(id: c.obra!.id),
                    ),
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              c.obra!.descricao,
                              style: tema.textTheme.bodyLarge?.copyWith(
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            if (c.obra!.status != null) ...[
                              const SizedBox(height: 8),
                              StatusChip(
                                nome: c.obra!.status!.nome,
                                cor: c.obra!.status!.cor,
                              ),
                            ],
                            const SizedBox(height: 4),
                            LinhaIcone(
                              icone: Icons.place_outlined,
                              texto:
                                  c.obra!.endereco ?? 'Endereço não informado',
                            ),
                          ],
                        ),
                      ),
                      Icon(
                        Icons.chevron_right,
                        color: tema.colorScheme.onSurfaceVariant,
                      ),
                    ],
                  ),
                ),
        ),

        // 3 — Empresa
        SecaoCard(
          titulo: 'Empresa',
          icone: Icons.business_outlined,
          child: c.empresa == null
              ? const VazioInline(
                  texto: 'Empresa não informada.',
                  icone: Icons.business_outlined,
                )
              : Column(
                  children: [
                    LinhaInfo(
                      rotulo: 'Razão social',
                      valor: c.empresa!.razaoSocial,
                    ),
                    LinhaInfo(
                      rotulo: 'Nome fantasia',
                      valor: c.empresa!.nomeFantasia ?? '—',
                    ),
                  ],
                ),
        ),

        // 4 — Financeiro
        SecaoCard(
          titulo: 'Execução financeira',
          icone: Icons.trending_up,
          child: ResumoFinanceiro(
            percentual: c.percentualExecutado,
            contratado: c.valorContrato,
            medido: c.valorMedido,
            saldo: c.saldo,
          ),
        ),

        // 5 — Vigência
        SecaoCard(
          titulo: 'Vigência',
          icone: Icons.event_outlined,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              if (c.situacao.requerAtencao) ...[
                _AvisoVigencia(situacao: c.situacao),
                const SizedBox(height: 8),
              ],
              LinhaInfo(
                rotulo: 'Assinatura',
                valor: Fmt.data(c.dataAssinatura),
              ),
              LinhaInfo(
                rotulo: 'Ordem de início',
                valor: Fmt.data(c.ordemInicio),
              ),
              LinhaInfo(
                rotulo: 'Vigência até',
                valor: Fmt.data(c.vigenciaContrato),
                destaque: c.situacao.requerAtencao,
              ),
            ],
          ),
        ),

        // 6 — Medições
        SecaoCard(
          titulo: 'Últimas medições',
          icone: Icons.straighten,
          child: detalhe.ultimasMedicoes.isEmpty
              ? const VazioInline(
                  texto: 'Nenhuma medição registrada.',
                  icone: Icons.event_note_outlined,
                )
              : Column(
                  children: [
                    for (final (i, m) in detalhe.ultimasMedicoes.indexed) ...[
                      if (i > 0) const Divider(height: 1),
                      LinhaMedicao(data: m.dataMedicao, valor: m.valorMedido),
                    ],
                  ],
                ),
        ),
      ],
    );
  }
}

class _AvisoVigencia extends StatelessWidget {
  const _AvisoVigencia({required this.situacao});

  final SituacaoVigencia situacao;

  @override
  Widget build(BuildContext context) {
    final cores = Theme.of(context).colorScheme;
    final vencido = situacao == SituacaoVigencia.vencido;
    final fundo = vencido ? cores.errorContainer : cores.tertiaryContainer;
    final texto = vencido ? cores.onErrorContainer : cores.onTertiaryContainer;

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: fundo,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Icon(
            vencido ? Icons.event_busy_outlined : Icons.schedule_outlined,
            color: texto,
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              vencido
                  ? 'Vigência do contrato encerrada.'
                  : 'A vigência termina nos próximos 30 dias.',
              style: TextStyle(color: texto),
            ),
          ),
        ],
      ),
    );
  }
}
