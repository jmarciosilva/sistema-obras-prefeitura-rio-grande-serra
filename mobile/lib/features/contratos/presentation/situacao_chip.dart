import 'package:flutter/material.dart';

import '../models/contrato.dart';

/// Selo da situação da vigência, com cores do Theme atual.
class SituacaoChip extends StatelessWidget {
  const SituacaoChip({super.key, required this.situacao});

  final SituacaoVigencia situacao;

  @override
  Widget build(BuildContext context) {
    final cores = Theme.of(context).colorScheme;
    final (fundo, texto) = switch (situacao) {
      SituacaoVigencia.vencido => (
        cores.errorContainer,
        cores.onErrorContainer,
      ),
      SituacaoVigencia.venceEmBreve => (
        cores.tertiaryContainer,
        cores.onTertiaryContainer,
      ),
      SituacaoVigencia.vigente => (
        cores.primaryContainer,
        cores.onPrimaryContainer,
      ),
      SituacaoVigencia.semVigencia => (
        cores.surfaceContainerHighest,
        cores.onSurfaceVariant,
      ),
    };

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: fundo,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        situacao.label,
        style: Theme.of(context).textTheme.labelMedium?.copyWith(
          color: texto,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }
}
