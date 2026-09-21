import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../shared/utils/formatters.dart';
import '../../../shared/widgets/componentes.dart';
import '../../../shared/widgets/estados.dart';
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
      appBar: AppBar(title: const Text('Detalhe da obra')),
      body: detalhe.when(
        skipLoadingOnRefresh: !detalhe.hasError,
        loading: () => const LoadingView(),
        error: (e, _) => ErrorView(
          erro: e,
          onRetry: () => ref.invalidate(obraDetalheProvider(id)),
        ),
        data: (d) => RefreshIndicator(
          onRefresh: () async {
            try {
              ref.invalidate(obraDetalheProvider(id));
              await ref.read(obraDetalheProvider(id).future);
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

  final ObraDetalhe detalhe;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    final obra = detalhe.obra;

    return ListView(
      padding: const EdgeInsets.all(12),
      children: [
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(obra.descricao, style: tema.textTheme.titleMedium),
                const SizedBox(height: 10),
                if (obra.status != null)
                  StatusChip(nome: obra.status!.nome, cor: obra.status!.cor),
                const SizedBox(height: 12),
                _IconeTexto(
                  icone: Icons.place_outlined,
                  texto: obra.endereco ?? 'Endereço não informado',
                ),
                if (detalhe.processoExecucao != null)
                  _IconeTexto(
                    icone: Icons.description_outlined,
                    texto: 'Processo ${detalhe.processoExecucao}',
                  ),
              ],
            ),
          ),
        ),
        SecaoCard(
          titulo: 'Execução financeira',
          icone: Icons.trending_up,
          child: Column(
            children: [
              BarraPercentual(percentual: obra.percentualExecutado, altura: 12),
              const SizedBox(height: 12),
              LinhaInfo(
                rotulo: 'Valor contratado',
                valor: Fmt.moeda(obra.valorContratado),
                destaque: true,
              ),
              LinhaInfo(
                rotulo: 'Valor medido',
                valor: Fmt.moeda(obra.valorMedido),
                destaque: true,
              ),
              LinhaInfo(
                rotulo: 'Saldo',
                valor: Fmt.moeda(obra.saldo),
                destaque: true,
              ),
            ],
          ),
        ),
        SecaoCard(
          titulo: detalhe.contratos.length > 1
              ? 'Contratos (${detalhe.contratos.length})'
              : 'Contrato',
          icone: Icons.assignment_outlined,
          child: detalhe.contratos.isEmpty
              ? const Text('Nenhum contrato cadastrado.')
              : Column(
                  children: [
                    for (final (i, c) in detalhe.contratos.indexed) ...[
                      if (i > 0) const Divider(height: 24),
                      _ContratoInfo(contrato: c),
                    ],
                  ],
                ),
        ),
        SecaoCard(
          titulo: 'Últimas medições',
          icone: Icons.straighten,
          child: detalhe.ultimasMedicoes.isEmpty
              ? const Text('Nenhuma medição registrada.')
              : Column(
                  children: [
                    for (final m in detalhe.ultimasMedicoes)
                      ListTile(
                        dense: true,
                        contentPadding: EdgeInsets.zero,
                        leading: const Icon(Icons.event_note_outlined),
                        title: Text(Fmt.data(m.dataMedicao)),
                        subtitle: m.numeroContrato == null
                            ? null
                            : Text('Contrato ${m.numeroContrato}'),
                        trailing: Text(
                          Fmt.moeda(m.valorMedido),
                          style: tema.textTheme.titleSmall,
                        ),
                      ),
                  ],
                ),
        ),
        const SizedBox(height: 12),
      ],
    );
  }
}

class _ContratoInfo extends StatelessWidget {
  const _ContratoInfo({required this.contrato});

  final Contrato contrato;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    final c = contrato;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Expanded(
              child: Text(
                c.numeroContrato != null
                    ? 'Contrato ${c.numeroContrato}'
                    : 'Contrato sem número',
                style: tema.textTheme.titleSmall,
              ),
            ),
            if (c.vencido)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: tema.colorScheme.errorContainer,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  'Vigência vencida',
                  style: tema.textTheme.labelSmall?.copyWith(
                    color: tema.colorScheme.onErrorContainer,
                  ),
                ),
              ),
          ],
        ),
        const SizedBox(height: 6),
        LinhaInfo(rotulo: 'Empresa', valor: c.empresa?.nome ?? '—'),
        if (c.empresa?.cnpj != null)
          LinhaInfo(rotulo: 'CNPJ', valor: c.empresa!.cnpj!),
        if (c.processoLicitacao != null)
          LinhaInfo(
            rotulo: 'Processo licitatório',
            valor: c.processoLicitacao!,
          ),
        LinhaInfo(rotulo: 'Assinatura', valor: Fmt.data(c.dataAssinatura)),
        LinhaInfo(rotulo: 'Ordem de início', valor: Fmt.data(c.ordemInicio)),
        LinhaInfo(rotulo: 'Vigência até', valor: Fmt.data(c.vigenciaContrato)),
        LinhaInfo(
          rotulo: 'Valor do contrato',
          valor: Fmt.moeda(c.valorContrato),
        ),
        LinhaInfo(rotulo: 'Valor medido', valor: Fmt.moeda(c.valorMedido)),
      ],
    );
  }
}

class _IconeTexto extends StatelessWidget {
  const _IconeTexto({required this.icone, required this.texto});

  final IconData icone;
  final String texto;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Padding(
      padding: const EdgeInsets.only(top: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icone, size: 18, color: tema.colorScheme.onSurfaceVariant),
          const SizedBox(width: 8),
          Expanded(child: Text(texto, style: tema.textTheme.bodyMedium)),
        ],
      ),
    );
  }
}
