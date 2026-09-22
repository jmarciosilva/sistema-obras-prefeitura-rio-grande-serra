import 'package:flutter/material.dart';

import '../models/contrato.dart';

/// Selo da situação da vigência, com cores do Theme atual.
/// Ícone + texto: a situação não depende só da cor.
class SituacaoChip extends StatelessWidget {
  const SituacaoChip({super.key, required this.situacao});

  final SituacaoVigencia situacao;

  @override
  Widget build(BuildContext context) {
    final cores = Theme.of(context).colorScheme;
    final (fundo, texto, icone) = switch (situacao) {
      SituacaoVigencia.vencido => (
        cores.errorContainer,
        cores.onErrorContainer,
        Icons.event_busy_outlined,
      ),
      SituacaoVigencia.venceEmBreve => (
        cores.tertiaryContainer,
        cores.onTertiaryContainer,
        Icons.schedule_outlined,
      ),
      SituacaoVigencia.vigente => (
        cores.primaryContainer,
        cores.onPrimaryContainer,
        Icons.check_circle_outline,
      ),
      SituacaoVigencia.semVigencia => (
        cores.surfaceContainerHighest,
        cores.onSurfaceVariant,
        Icons.remove_circle_outline,
      ),
    };

    return Container(
      padding: const EdgeInsets.fromLTRB(8, 4, 10, 4),
      decoration: BoxDecoration(
        color: fundo,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icone, size: 16, color: texto),
          const SizedBox(width: 4),
          Text(
            situacao.label,
            style: Theme.of(context).textTheme.labelMedium?.copyWith(
              color: texto,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}
