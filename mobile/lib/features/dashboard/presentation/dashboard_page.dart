import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../shared/utils/formatters.dart';
import '../../../shared/widgets/componentes.dart';
import '../../../shared/widgets/estados.dart';
import '../../contratos/models/contrato.dart';
import '../../contratos/presentation/contratos_page.dart';
import '../data/dashboard_api.dart';
import '../models/dashboard.dart';

class DashboardPage extends ConsumerWidget {
  const DashboardPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final dashboard = ref.watch(dashboardProvider);

    return dashboard.when(
      // Puxar para atualizar mantém os dados na tela; "Tentar novamente"
      // após erro mostra o carregando.
      skipLoadingOnRefresh: !dashboard.hasError,
      loading: () => const LoadingView(),
      error: (e, _) =>
          ErrorView(erro: e, onRetry: () => ref.invalidate(dashboardProvider)),
      data: (d) => RefreshIndicator(
        onRefresh: () async {
          try {
            ref.invalidate(dashboardProvider);
            await ref.read(dashboardProvider.future);
          } catch (_) {
            // O erro já aparece na tela via dashboardProvider.
          }
        },
        child: ListView(
          padding: const EdgeInsets.all(12),
          children: [
            _Titulo(atualizadoEm: d.atualizadoEm),
            _PercentualGeral(resumo: d.obras),
            const SizedBox(height: 4),
            _Contagens(resumo: d.obras),
            const SizedBox(height: 4),
            _Financeiro(resumo: d.obras),
            _PorStatus(status: d.status, total: d.obras.total),
            _AlertasCard(alertas: d.alertas),
            const SizedBox(height: 12),
          ],
        ),
      ),
    );
  }
}

class _Titulo extends StatelessWidget {
  const _Titulo({this.atualizadoEm});

  final DateTime? atualizadoEm;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Padding(
      padding: const EdgeInsets.fromLTRB(4, 4, 4, 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          Expanded(
            child: Text('Painel Executivo', style: tema.textTheme.titleLarge),
          ),
          if (atualizadoEm != null)
            Text(
              'Atualizado ${Fmt.dataHora(atualizadoEm)}',
              style: tema.textTheme.bodySmall,
            ),
        ],
      ),
    );
  }
}

class _PercentualGeral extends StatelessWidget {
  const _PercentualGeral({required this.resumo});

  final ResumoObras resumo;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Card(
      color: tema.colorScheme.primaryContainer,
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Execução financeira geral',
              style: tema.textTheme.titleSmall?.copyWith(
                color: tema.colorScheme.onPrimaryContainer,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              Fmt.percentual(resumo.percentualExecutado),
              style: tema.textTheme.displaySmall?.copyWith(
                fontWeight: FontWeight.w700,
                color: tema.colorScheme.onPrimaryContainer,
              ),
            ),
            const SizedBox(height: 12),
            BarraPercentual(
              percentual: resumo.percentualExecutado,
              altura: 12,
              mostrarTexto: false,
            ),
            const SizedBox(height: 8),
            Text(
              'Medido ${Fmt.moeda(resumo.valorMedido)} '
              'de ${Fmt.moeda(resumo.valorContratado)}',
              style: tema.textTheme.bodySmall?.copyWith(
                color: tema.colorScheme.onPrimaryContainer,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _Contagens extends StatelessWidget {
  const _Contagens({required this.resumo});

  final ResumoObras resumo;

  @override
  Widget build(BuildContext context) => Row(
    children: [
      _Numero(
        rotulo: 'Total de obras',
        valor: resumo.total,
        icone: Icons.apartment_outlined,
      ),
      _Numero(
        rotulo: 'Em execução',
        valor: resumo.emExecucao,
        icone: Icons.engineering_outlined,
      ),
      _Numero(
        rotulo: 'Concluídas',
        valor: resumo.concluidas,
        icone: Icons.task_alt_outlined,
      ),
    ],
  );
}

class _Numero extends StatelessWidget {
  const _Numero({
    required this.rotulo,
    required this.valor,
    required this.icone,
  });

  final String rotulo;
  final int valor;
  final IconData icone;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Expanded(
      child: Card(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 8),
          child: Column(
            children: [
              Icon(icone, color: tema.colorScheme.primary),
              const SizedBox(height: 6),
              Text(
                Fmt.inteiro(valor),
                style: tema.textTheme.headlineSmall?.copyWith(
                  fontWeight: FontWeight.w700,
                ),
              ),
              Text(
                rotulo,
                textAlign: TextAlign.center,
                style: tema.textTheme.bodySmall,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _Financeiro extends StatelessWidget {
  const _Financeiro({required this.resumo});

  final ResumoObras resumo;

  @override
  Widget build(BuildContext context) => SecaoCard(
    titulo: 'Financeiro',
    icone: Icons.account_balance_wallet_outlined,
    child: Column(
      children: [
        LinhaInfo(
          rotulo: 'Valor contratado',
          valor: Fmt.moeda(resumo.valorContratado),
          destaque: true,
        ),
        LinhaInfo(
          rotulo: 'Valor medido',
          valor: Fmt.moeda(resumo.valorMedido),
          destaque: true,
        ),
        LinhaInfo(
          rotulo: 'Saldo',
          valor: Fmt.moeda(resumo.saldo),
          destaque: true,
        ),
      ],
    ),
  );
}

class _PorStatus extends StatelessWidget {
  const _PorStatus({required this.status, required this.total});

  final List<StatusQuantidade> status;
  final int total;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return SecaoCard(
      titulo: 'Obras por status',
      icone: Icons.donut_small_outlined,
      child: status.isEmpty
          ? const Text('Nenhum status cadastrado.')
          : Column(
              children: [
                for (final s in status)
                  Padding(
                    padding: const EdgeInsets.symmetric(vertical: 6),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              width: 10,
                              height: 10,
                              decoration: BoxDecoration(
                                color: Fmt.cor(s.cor),
                                shape: BoxShape.circle,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                s.nome,
                                style: tema.textTheme.bodyMedium,
                              ),
                            ),
                            Text(
                              Fmt.inteiro(s.total),
                              style: tema.textTheme.titleSmall,
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(4),
                          child: LinearProgressIndicator(
                            value: total > 0 ? s.total / total : 0,
                            minHeight: 6,
                            color: Fmt.cor(s.cor),
                            backgroundColor:
                                tema.colorScheme.surfaceContainerHighest,
                          ),
                        ),
                      ],
                    ),
                  ),
              ],
            ),
    );
  }
}

class _AlertasCard extends StatelessWidget {
  const _AlertasCard({required this.alertas});

  final Alertas alertas;

  @override
  Widget build(BuildContext context) => SecaoCard(
    titulo: 'Alertas',
    icone: Icons.notification_important_outlined,
    child: Column(
      children: [
        _LinhaAlerta(
          rotulo: 'Contratos vencidos',
          valor: alertas.contratosVencidos,
          icone: Icons.event_busy_outlined,
          situacaoContratos: SituacaoVigencia.vencido,
        ),
        _LinhaAlerta(
          rotulo: 'Contratos vencendo em 30 dias',
          valor: alertas.contratosVencendo,
          icone: Icons.schedule_outlined,
          situacaoContratos: SituacaoVigencia.venceEmBreve,
        ),
        _LinhaAlerta(
          rotulo: 'Obras em execução sem medição há 60 dias',
          valor: alertas.obrasSemMedicao,
          icone: Icons.pending_actions_outlined,
        ),
      ],
    ),
  );
}

class _LinhaAlerta extends StatelessWidget {
  const _LinhaAlerta({
    required this.rotulo,
    required this.valor,
    required this.icone,
    this.situacaoContratos,
  });

  final String rotulo;
  final int valor;
  final IconData icone;

  /// Se informado (e houver alertas), o toque abre a lista de contratos
  /// já filtrada por essa situação.
  final SituacaoVigencia? situacaoContratos;

  void _abrirContratos(BuildContext context) {
    Navigator.of(context).push(
      MaterialPageRoute<void>(
        builder: (_) => Scaffold(
          // Título genérico: o usuário pode trocar o filtro nesta tela
          appBar: AppBar(title: const Text('Contratos')),
          body: ContratosPage(situacaoInicial: situacaoContratos),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    final ativo = valor > 0;
    final linha = _linha(tema, ativo);
    if (situacaoContratos == null || !ativo) return linha;
    return InkWell(
      borderRadius: BorderRadius.circular(8),
      onTap: () => _abrirContratos(context),
      child: linha,
    );
  }

  Widget _linha(ThemeData tema, bool ativo) {
    final cor = ativo ? tema.colorScheme.error : tema.colorScheme.outline;
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        children: [
          Icon(icone, color: cor, size: 22),
          const SizedBox(width: 12),
          Expanded(child: Text(rotulo, style: tema.textTheme.bodyMedium)),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 2),
            decoration: BoxDecoration(
              color: ativo
                  ? tema.colorScheme.errorContainer
                  : tema.colorScheme.surfaceContainerHighest,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              Fmt.inteiro(valor),
              style: tema.textTheme.labelLarge?.copyWith(
                color: ativo
                    ? tema.colorScheme.onErrorContainer
                    : tema.colorScheme.onSurfaceVariant,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
