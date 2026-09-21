import 'package:flutter/material.dart';

import '../utils/formatters.dart';

/// Cabeçalho institucional usado nas telas principais.
class InstitucionalAppBar extends StatelessWidget
    implements PreferredSizeWidget {
  const InstitucionalAppBar({super.key, this.actions});

  final List<Widget>? actions;

  @override
  Size get preferredSize => const Size.fromHeight(kToolbarHeight + 4);

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return AppBar(
      toolbarHeight: kToolbarHeight + 4,
      actions: actions,
      title: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Prefeitura de Rio Grande da Serra',
            style: tema.textTheme.titleMedium?.copyWith(
              color: tema.colorScheme.onPrimary,
              fontWeight: FontWeight.w600,
            ),
          ),
          Text(
            'Secretaria de Obras',
            style: tema.textTheme.bodySmall?.copyWith(
              color: tema.colorScheme.onPrimary.withValues(alpha: 0.85),
            ),
          ),
        ],
      ),
    );
  }
}

/// Barra de progresso do percentual executado.
/// O texto mostra o valor real; a barra é limitada a 0–100%.
class BarraPercentual extends StatelessWidget {
  const BarraPercentual({
    super.key,
    required this.percentual,
    this.altura = 10,
    this.mostrarTexto = true,
  });

  final double percentual;
  final double altura;
  final bool mostrarTexto;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    final valor = (percentual.isFinite ? percentual : 0).clamp(0, 100) / 100;

    final barra = ClipRRect(
      borderRadius: BorderRadius.circular(altura),
      child: LinearProgressIndicator(
        value: valor.toDouble(),
        minHeight: altura,
        backgroundColor: tema.colorScheme.surfaceContainerHighest,
      ),
    );

    if (!mostrarTexto) return barra;

    return Row(
      children: [
        Expanded(child: barra),
        const SizedBox(width: 12),
        SizedBox(
          width: 56,
          child: Text(
            Fmt.percentual(percentual.isFinite ? percentual : 0),
            textAlign: TextAlign.right,
            style: tema.textTheme.labelLarge,
          ),
        ),
      ],
    );
  }
}

/// Selo colorido do status da obra (cor vem da API).
class StatusChip extends StatelessWidget {
  const StatusChip({super.key, required this.nome, this.cor});

  final String nome;
  final String? cor;

  @override
  Widget build(BuildContext context) {
    final c = Fmt.cor(cor);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: c.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: c.withValues(alpha: 0.5)),
      ),
      child: Text(
        nome,
        style: Theme.of(context).textTheme.labelMedium?.copyWith(
          color: c,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }
}

/// Card com título de seção.
class SecaoCard extends StatelessWidget {
  const SecaoCard({
    super.key,
    required this.titulo,
    required this.child,
    this.icone,
  });

  final String titulo;
  final Widget child;
  final IconData? icone;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                if (icone != null) ...[
                  Icon(icone, size: 20, color: tema.colorScheme.primary),
                  const SizedBox(width: 8),
                ],
                Expanded(
                  child: Text(titulo, style: tema.textTheme.titleMedium),
                ),
              ],
            ),
            const SizedBox(height: 12),
            child,
          ],
        ),
      ),
    );
  }
}

/// Linha "rótulo ........ valor".
class LinhaInfo extends StatelessWidget {
  const LinhaInfo({
    super.key,
    required this.rotulo,
    required this.valor,
    this.destaque = false,
  });

  final String rotulo;
  final String valor;
  final bool destaque;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            flex: 4,
            child: Text(
              rotulo,
              style: tema.textTheme.bodyMedium?.copyWith(
                color: tema.colorScheme.onSurfaceVariant,
              ),
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            flex: 6,
            child: Text(
              valor,
              textAlign: TextAlign.right,
              style:
                  (destaque
                          ? tema.textTheme.titleSmall
                          : tema.textTheme.bodyMedium)
                      ?.copyWith(fontWeight: destaque ? FontWeight.w700 : null),
            ),
          ),
        ],
      ),
    );
  }
}
