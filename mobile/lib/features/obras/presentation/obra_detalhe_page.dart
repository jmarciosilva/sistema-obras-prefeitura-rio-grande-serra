import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/cache/consulta_offline.dart';
import '../../../shared/utils/formatters.dart';
import '../../../shared/widgets/aviso_offline.dart';
import '../../../shared/widgets/componentes.dart';
import '../../../shared/widgets/estados.dart';
import '../../contratos/presentation/contrato_detalhe_page.dart';
import '../data/obras_api.dart';
import '../models/obra_detalhe.dart';

class ObraDetalhePage extends ConsumerWidget {
  const ObraDetalhePage({super.key, required this.id, this.titulo});

  final int id;
  final String? titulo;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final detalhe = ref.watch(obraDetalheProvider(id));

    return Scaffold(
      appBar: const InstitucionalAppBar(titulo: 'Detalhe da obra'),
      body: detalhe.when(
        skipLoadingOnRefresh: !detalhe.hasError,
        loading: () => const LoadingView(),
        error: (e, _) => ErrorView(
          erro: e,
          onRetry: () => ref.invalidate(obraDetalheProvider(id)),
        ),
        data: (dados) => RefreshIndicator(
          onRefresh: () async {
            final ok = await ref
                .read(obraDetalheProvider(id).notifier)
                .atualizar();
            if (!ok && context.mounted) avisarSemConexao(context);
          },
          child: _Conteudo(dados: dados),
        ),
      ),
    );
  }
}

class _Conteudo extends StatelessWidget {
  const _Conteudo({required this.dados});

  final Dados<ObraDetalhe> dados;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    final detalhe = dados.valor;
    final obra = detalhe.obra;

    return ListView(
      padding: const EdgeInsets.fromLTRB(12, 8, 12, 24),
      children: [
        AvisoOffline(atualizadoEm: dados.atualizadoEm),
        if (dados.parcial) const AvisoDetalheParcial(),
        // 1 — Obra
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  obra.descricao,
                  // Descrições costumam ser longas (e em caixa alta)
                  style: tema.textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w600,
                    height: 1.25,
                  ),
                ),
                if (obra.status != null) ...[
                  const SizedBox(height: 12),
                  StatusChip(nome: obra.status!.nome, cor: obra.status!.cor),
                ],
                const SizedBox(height: 12),
                LinhaIcone(
                  icone: Icons.place_outlined,
                  texto: obra.endereco ?? 'Endereço não informado',
                ),
                if (detalhe.processoExecucao != null)
                  LinhaIcone(
                    icone: Icons.description_outlined,
                    texto: 'Processo ${detalhe.processoExecucao}',
                  ),
              ],
            ),
          ),
        ),

        // 2 — Execução financeira
        SecaoCard(
          titulo: 'Execução financeira',
          icone: Icons.trending_up,
          child: ResumoFinanceiro(
            percentual: obra.percentualExecutado,
            contratado: obra.valorContratado,
            medido: obra.valorMedido,
            saldo: obra.saldo,
          ),
        ),

        // Resumo da lista: contratos e medições não são conhecidos
        if (!dados.parcial) ...[
          // 3 — Contratos vinculados
          SecaoCard(
            titulo: detalhe.contratos.length > 1
                ? 'Contratos (${detalhe.contratos.length})'
                : 'Contrato',
            icone: Icons.receipt_long_outlined,
            child: detalhe.contratos.isEmpty
                ? const VazioInline(
                    texto: 'Nenhum contrato cadastrado.',
                    icone: Icons.receipt_long_outlined,
                  )
                : Column(
                    children: [
                      for (final (i, c) in detalhe.contratos.indexed) ...[
                        if (i > 0) const Divider(height: 16),
                        _ContratoResumo(contrato: c),
                      ],
                    ],
                  ),
          ),

          // 4 — Últimas medições
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
                        LinhaMedicao(
                          data: m.dataMedicao,
                          valor: m.valorMedido,
                          // Contrato só é útil quando a obra tem mais de um
                          contexto:
                              detalhe.contratos.length > 1 &&
                                  m.numeroContrato != null
                              ? 'Contrato ${m.numeroContrato}'
                              : null,
                        ),
                      ],
                    ],
                  ),
          ),
        ],
      ],
    );
  }
}

/// Resumo de um contrato vinculado; o toque abre o detalhe do contrato
/// (datas, processo e empresa completos ficam lá).
class _ContratoResumo extends StatelessWidget {
  const _ContratoResumo({required this.contrato});

  final Contrato contrato;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    final c = contrato;
    return InkWell(
      borderRadius: BorderRadius.circular(8),
      onTap: () => Navigator.of(context).push(
        MaterialPageRoute<void>(builder: (_) => ContratoDetalhePage(id: c.id)),
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 6),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          c.numeroContrato != null
                              ? 'Contrato ${c.numeroContrato}'
                              : 'Contrato sem número',
                          style: tema.textTheme.titleSmall?.copyWith(
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                      if (c.vencido) const _SeloVencido(),
                    ],
                  ),
                  if (c.empresa != null) ...[
                    const SizedBox(height: 2),
                    Text(
                      c.empresa!.nome,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: tema.textTheme.bodyMedium?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                  LinhaIcone(
                    icone: Icons.event_outlined,
                    texto: c.vigenciaContrato == null
                        ? 'Vigência não informada'
                        : 'Vigência até ${Fmt.data(c.vigenciaContrato)}',
                  ),
                  const SizedBox(height: 4),
                  LinhaInfo(
                    rotulo: 'Contratado',
                    valor: Fmt.moeda(c.valorContrato),
                  ),
                  LinhaInfo(rotulo: 'Medido', valor: Fmt.moeda(c.valorMedido)),
                ],
              ),
            ),
            const SizedBox(width: 4),
            Icon(Icons.chevron_right, color: tema.colorScheme.onSurfaceVariant),
          ],
        ),
      ),
    );
  }
}

/// Selo "Vencido" com ícone (não depende só da cor).
class _SeloVencido extends StatelessWidget {
  const _SeloVencido();

  @override
  Widget build(BuildContext context) {
    final cores = Theme.of(context).colorScheme;
    return Container(
      padding: const EdgeInsets.fromLTRB(6, 2, 8, 2),
      decoration: BoxDecoration(
        color: cores.errorContainer,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            Icons.event_busy_outlined,
            size: 14,
            color: cores.onErrorContainer,
          ),
          const SizedBox(width: 4),
          Text(
            'Vencido',
            style: Theme.of(context).textTheme.labelSmall?.copyWith(
              color: cores.onErrorContainer,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}
