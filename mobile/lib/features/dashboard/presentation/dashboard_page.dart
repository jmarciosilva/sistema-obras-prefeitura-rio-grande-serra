import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../shared/utils/formatters.dart';
import '../../../shared/widgets/componentes.dart';
import '../../../shared/widgets/estados.dart';
import '../../contratos/models/contrato.dart';
import '../../contratos/presentation/contratos_page.dart';
import '../../obras/presentation/obras_page.dart';
import '../data/dashboard_api.dart';
import '../models/dashboard.dart';

/// Painel Executivo: Resumo · Execução financeira · Obras · Contratos ·
/// Pontos de atenção. Tudo vem de uma única chamada (`GET /dashboard`).
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
          padding: const EdgeInsets.fromLTRB(12, 12, 12, 24),
          children: [
            _Cabecalho(atualizadoEm: d.atualizadoEm),
            const TituloSecao('Resumo geral'),
            _ResumoGeral(dashboard: d),
            const TituloSecao('Execução financeira'),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: ResumoFinanceiro(
                  percentual: d.obras.percentualExecutado,
                  contratado: d.obras.valorContratado,
                  medido: d.obras.valorMedido,
                  saldo: d.obras.saldo,
                ),
              ),
            ),
            const TituloSecao('Obras'),
            _ObrasCard(resumo: d.obras, status: d.status),
            const TituloSecao('Contratos'),
            _ContratosCard(resumo: d.contratos),
            const TituloSecao('Pontos de atenção'),
            _PontosDeAtencao(alertas: d.alertas),
          ],
        ),
      ),
    );
  }
}

// ── Navegação (somente consulta: abre listas já existentes) ──────────

/// Abre a lista de Contratos (opcionalmente já filtrada), usada pela seção
/// de Contratos e pelos pontos de atenção.
void _abrirContratos(BuildContext context, SituacaoVigencia? situacao) {
  Navigator.of(context).push(
    MaterialPageRoute<void>(
      builder: (_) => Scaffold(
        // Título genérico: o usuário pode trocar o filtro nesta tela
        appBar: const InstitucionalAppBar(titulo: 'Contratos'),
        body: ContratosPage(situacaoInicial: situacao),
      ),
    ),
  );
}

/// Abre a lista de Obras (opcionalmente já filtrada por status).
void _abrirObras(BuildContext context, int? statusId) {
  Navigator.of(context).push(
    MaterialPageRoute<void>(
      builder: (_) => Scaffold(
        appBar: const InstitucionalAppBar(titulo: 'Obras'),
        body: ObrasPage(statusInicial: statusId),
      ),
    ),
  );
}

/// Status cujo nome contém [trecho] — mesma regra do backend (`like %Execu%`).
/// Só devolve o id quando há exatamente um status correspondente; caso
/// contrário a lista de Obras não tem um filtro equivalente.
int? _statusUnico(List<StatusQuantidade> status, String trecho) {
  final achados = status
      .where((s) => s.nome.toLowerCase().contains(trecho))
      .toList();
  return achados.length == 1 ? achados.first.id : null;
}

// ── Cabeçalho ────────────────────────────────────────────────────────

class _Cabecalho extends StatelessWidget {
  const _Cabecalho({this.atualizadoEm});

  final DateTime? atualizadoEm;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 4),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Painel Executivo',
            style: tema.textTheme.headlineSmall?.copyWith(
              fontWeight: FontWeight.w700,
            ),
          ),
          if (atualizadoEm != null) ...[
            const SizedBox(height: 2),
            Row(
              children: [
                Icon(
                  Icons.update,
                  size: 16,
                  color: tema.colorScheme.onSurfaceVariant,
                ),
                const SizedBox(width: 4),
                Text(
                  'Atualizado em ${Fmt.dataHora(atualizadoEm)}',
                  style: tema.textTheme.bodySmall?.copyWith(
                    color: tema.colorScheme.onSurfaceVariant,
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }
}

// ── 1. Resumo geral ──────────────────────────────────────────────────

class _ResumoGeral extends StatelessWidget {
  const _ResumoGeral({required this.dashboard});

  final Dashboard dashboard;

  @override
  Widget build(BuildContext context) {
    final obras = dashboard.obras;
    final emExecucao = _statusUnico(dashboard.status, 'execu');
    final concluidas = _statusUnico(dashboard.status, 'conclu');

    return Column(
      children: [
        Row(
          children: [
            _KpiTile(
              rotulo: 'Total de obras',
              valor: obras.total,
              icone: Icons.apartment_outlined,
              onTap: () => _abrirObras(context, null),
            ),
            const SizedBox(width: 8),
            _KpiTile(
              rotulo: 'Obras em execução',
              valor: obras.emExecucao,
              icone: Icons.engineering_outlined,
              onTap: emExecucao == null
                  ? null
                  : () => _abrirObras(context, emExecucao),
            ),
          ],
        ),
        const SizedBox(height: 8),
        Row(
          children: [
            _KpiTile(
              rotulo: 'Obras concluídas',
              valor: obras.concluidas,
              icone: Icons.task_alt_outlined,
              onTap: concluidas == null
                  ? null
                  : () => _abrirObras(context, concluidas),
            ),
            const SizedBox(width: 8),
            _KpiTile(
              rotulo: 'Total de contratos',
              valor: dashboard.contratos.total,
              icone: Icons.receipt_long_outlined,
              onTap: () => _abrirContratos(context, null),
            ),
          ],
        ),
      ],
    );
  }
}

class _KpiTile extends StatelessWidget {
  const _KpiTile({
    required this.rotulo,
    required this.valor,
    required this.icone,
    this.onTap,
  });

  final String rotulo;
  final int valor;
  final IconData icone;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Expanded(
      child: Card(
        margin: EdgeInsets.zero,
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.fromLTRB(14, 14, 10, 14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: tema.colorScheme.primaryContainer,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Icon(
                        icone,
                        size: 20,
                        color: tema.colorScheme.onPrimaryContainer,
                      ),
                    ),
                    const Spacer(),
                    if (onTap != null)
                      Icon(
                        Icons.chevron_right,
                        size: 20,
                        color: tema.colorScheme.outline,
                      ),
                  ],
                ),
                const SizedBox(height: 12),
                Text(
                  Fmt.inteiro(valor),
                  style: tema.textTheme.headlineMedium?.copyWith(
                    fontWeight: FontWeight.w700,
                  ),
                ),
                Text(
                  rotulo,
                  maxLines: 2,
                  style: tema.textTheme.bodyMedium?.copyWith(
                    color: tema.colorScheme.onSurfaceVariant,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

// ── 3. Obras ─────────────────────────────────────────────────────────

class _ObrasCard extends StatelessWidget {
  const _ObrasCard({required this.resumo, required this.status});

  final ResumoObras resumo;
  final List<StatusQuantidade> status;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Card(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              '${Fmt.inteiro(resumo.total)} obras · '
              '${Fmt.inteiro(resumo.emExecucao)} em execução · '
              '${Fmt.inteiro(resumo.concluidas)} concluídas',
              style: tema.textTheme.bodyMedium?.copyWith(
                fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              'Distribuição por status',
              style: tema.textTheme.bodySmall?.copyWith(
                color: tema.colorScheme.onSurfaceVariant,
              ),
            ),
            const SizedBox(height: 8),
            if (status.isEmpty)
              const VazioInline(texto: 'Nenhum status cadastrado.')
            else
              for (final s in status)
                _LinhaStatus(status: s, total: resumo.total),
          ],
        ),
      ),
    );
  }
}

/// Linha de status; o toque abre a lista de Obras filtrada por ele.
class _LinhaStatus extends StatelessWidget {
  const _LinhaStatus({required this.status, required this.total});

  final StatusQuantidade status;
  final int total;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    final s = status;
    final vazio = s.total == 0;
    final cor = Fmt.cor(s.cor);

    return InkWell(
      borderRadius: BorderRadius.circular(8),
      onTap: vazio ? null : () => _abrirObras(context, s.id),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 10,
                  height: 10,
                  decoration: BoxDecoration(color: cor, shape: BoxShape.circle),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    s.nome,
                    style: tema.textTheme.bodyMedium?.copyWith(
                      color: vazio ? tema.colorScheme.onSurfaceVariant : null,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  Fmt.inteiro(s.total),
                  style: tema.textTheme.titleSmall?.copyWith(
                    fontWeight: FontWeight.w700,
                    color: vazio ? tema.colorScheme.onSurfaceVariant : null,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 6),
            Padding(
              padding: const EdgeInsets.only(left: 20),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(4),
                child: LinearProgressIndicator(
                  value: total > 0 ? s.total / total : 0,
                  minHeight: 6,
                  color: cor,
                  backgroundColor: tema.colorScheme.surfaceContainerHighest,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── 4. Contratos ─────────────────────────────────────────────────────

class _ContratosCard extends StatelessWidget {
  const _ContratosCard({required this.resumo});

  final ResumoContratos resumo;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Card(
      key: const Key('dashboard-contratos'),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                _KpiContrato(rotulo: 'Total', valor: resumo.total),
                _KpiContrato(
                  rotulo: 'Vigentes',
                  valor: resumo.vigentes,
                  situacao: SituacaoVigencia.vigente,
                ),
                _KpiContrato(
                  rotulo: 'Vencendo',
                  valor: resumo.venceEmBreve,
                  situacao: SituacaoVigencia.venceEmBreve,
                ),
                _KpiContrato(
                  rotulo: 'Vencidos',
                  valor: resumo.vencidos,
                  situacao: SituacaoVigencia.vencido,
                ),
              ],
            ),
            if (resumo.semVigencia > 0)
              Padding(
                padding: const EdgeInsets.fromLTRB(4, 4, 4, 0),
                child: Text(
                  '${Fmt.inteiro(resumo.semVigencia)} sem vigência informada',
                  style: tema.textTheme.bodySmall?.copyWith(
                    color: tema.colorScheme.onSurfaceVariant,
                  ),
                ),
              ),
            const Divider(height: 24),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 4),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Execução financeira dos contratos',
                    style: tema.textTheme.titleSmall?.copyWith(
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 8),
                  BarraPercentual(percentual: resumo.percentualExecutado),
                  const SizedBox(height: 8),
                  LinhaInfo(
                    rotulo: 'Valor contratado',
                    valor: Fmt.moeda(resumo.valorContratado),
                  ),
                  LinhaInfo(
                    rotulo: 'Valor medido',
                    valor: Fmt.moeda(resumo.valorMedido),
                  ),
                  LinhaInfo(rotulo: 'Saldo', valor: Fmt.moeda(resumo.saldo)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// Indicador tocável: abre Contratos filtrado por [situacao]
/// (ou sem filtro, quando nulo).
class _KpiContrato extends StatelessWidget {
  const _KpiContrato({
    required this.rotulo,
    required this.valor,
    this.situacao,
  });

  final String rotulo;
  final int valor;
  final SituacaoVigencia? situacao;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    // Cor só quando há algo a observar; vencidos em vermelho, vencendo em
    // tom de atenção. Zero fica neutro.
    final cor = switch (situacao) {
      SituacaoVigencia.vencido when valor > 0 => tema.colorScheme.error,
      SituacaoVigencia.venceEmBreve when valor > 0 => tema.colorScheme.tertiary,
      _ => tema.colorScheme.onSurface,
    };
    return Expanded(
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () => _abrirContratos(context, situacao),
        child: ConstrainedBox(
          constraints: const BoxConstraints(minHeight: 72),
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 2),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  Fmt.inteiro(valor),
                  style: tema.textTheme.headlineSmall?.copyWith(
                    fontWeight: FontWeight.w700,
                    color: cor,
                  ),
                ),
                Text(
                  rotulo,
                  textAlign: TextAlign.center,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: tema.textTheme.bodyMedium?.copyWith(
                    color: tema.colorScheme.onSurfaceVariant,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

// ── 5. Pontos de atenção ─────────────────────────────────────────────

enum _Gravidade { erro, atencao }

class _PontosDeAtencao extends StatelessWidget {
  const _PontosDeAtencao({required this.alertas});

  final Alertas alertas;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Card(
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 4),
        child: Column(
          children: [
            if (alertas.total == 0)
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
                child: Row(
                  children: [
                    Icon(
                      Icons.check_circle_outline,
                      color: tema.colorScheme.primary,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'Nenhum ponto de atenção no momento.',
                        style: tema.textTheme.bodyLarge,
                      ),
                    ),
                  ],
                ),
              ),
            _LinhaAtencao(
              rotulo: 'Contratos vencidos',
              valor: alertas.contratosVencidos,
              icone: Icons.event_busy_outlined,
              gravidade: _Gravidade.erro,
              situacaoContratos: SituacaoVigencia.vencido,
            ),
            const Divider(height: 1, indent: 64),
            _LinhaAtencao(
              rotulo: 'Contratos vencendo em 30 dias',
              valor: alertas.contratosVencendo,
              icone: Icons.schedule_outlined,
              gravidade: _Gravidade.atencao,
              situacaoContratos: SituacaoVigencia.venceEmBreve,
            ),
            const Divider(height: 1, indent: 64),
            _LinhaAtencao(
              rotulo: 'Obras sem medição há 60 dias',
              detalhe: 'Obras em execução',
              valor: alertas.obrasSemMedicao,
              icone: Icons.pending_actions_outlined,
              gravidade: _Gravidade.atencao,
            ),
          ],
        ),
      ),
    );
  }
}

class _LinhaAtencao extends StatelessWidget {
  const _LinhaAtencao({
    required this.rotulo,
    required this.valor,
    required this.icone,
    required this.gravidade,
    this.detalhe,
    this.situacaoContratos,
  });

  final String rotulo;
  final String? detalhe;
  final int valor;
  final IconData icone;
  final _Gravidade gravidade;

  /// Se informado (e houver ocorrências), o toque abre a lista de contratos
  /// já filtrada por essa situação.
  final SituacaoVigencia? situacaoContratos;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    final cores = tema.colorScheme;
    final ativo = valor > 0;
    final (fundo, frente) = !ativo
        ? (cores.surfaceContainerHighest, cores.onSurfaceVariant)
        : gravidade == _Gravidade.erro
        ? (cores.errorContainer, cores.onErrorContainer)
        : (cores.tertiaryContainer, cores.onTertiaryContainer);
    final tocavel = ativo && situacaoContratos != null;

    return InkWell(
      onTap: tocavel ? () => _abrirContratos(context, situacaoContratos) : null,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: fundo,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icone, size: 22, color: frente),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    rotulo,
                    style: tema.textTheme.bodyLarge?.copyWith(
                      fontWeight: ativo ? FontWeight.w600 : null,
                    ),
                  ),
                  Text(
                    ativo ? (detalhe ?? 'Requer atenção') : 'Nenhum',
                    style: tema.textTheme.bodySmall?.copyWith(
                      color: cores.onSurfaceVariant,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 8),
            Text(
              Fmt.inteiro(valor),
              style: tema.textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.w700,
                color: ativo
                    ? (gravidade == _Gravidade.erro
                          ? cores.error
                          : cores.onSurface)
                    : cores.onSurfaceVariant,
              ),
            ),
            SizedBox(
              width: 28,
              child: tocavel
                  ? Icon(Icons.chevron_right, color: cores.outline)
                  : null,
            ),
          ],
        ),
      ),
    );
  }
}
