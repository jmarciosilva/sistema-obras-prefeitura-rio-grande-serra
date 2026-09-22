import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/cache/consulta_offline.dart';
import '../../core/cache/sync_state.dart';
import '../utils/formatters.dart';

/// Aviso discreto exibido quando a API não está acessível e a tela mostra
/// dados locais. Nunca esconde de quando são os dados.
class AvisoOffline extends ConsumerWidget {
  const AvisoOffline({super.key, required this.atualizadoEm});

  /// Quando os dados exibidos nesta tela vieram da API.
  final DateTime? atualizadoEm;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final sync = ref.watch(syncProvider);
    if (!sync.offline) return const SizedBox.shrink();

    final tema = Theme.of(context);
    final cor = tema.colorScheme.onSurfaceVariant;
    final quando = atualizadoEm;

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.fromLTRB(12, 10, 12, 10),
      decoration: BoxDecoration(
        color: tema.colorScheme.surfaceContainerHighest,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          Icon(Icons.cloud_off_outlined, size: 22, color: cor),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Modo offline',
                  style: tema.textTheme.titleSmall?.copyWith(
                    fontWeight: FontWeight.w700,
                  ),
                ),
                Text(
                  quando == null
                      ? 'Exibindo dados salvos no aparelho.'
                      : 'Dados atualizados em ${Fmt.dataHoraExtenso(quando)} '
                            '(${Fmt.relativo(quando)})',
                  style: tema.textTheme.bodySmall?.copyWith(color: cor),
                ),
              ],
            ),
          ),
          if (sync.emAndamento > 0) ...[
            const SizedBox(width: 8),
            const SizedBox(
              width: 16,
              height: 16,
              child: CircularProgressIndicator(strokeWidth: 2),
            ),
          ],
        ],
      ),
    );
  }
}

/// Detalhe nunca sincronizado, exibido a partir do resumo da lista.
class AvisoDetalheParcial extends StatelessWidget {
  const AvisoDetalheParcial({super.key});

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    final cor = tema.colorScheme.onSurfaceVariant;
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        border: Border.all(color: tema.colorScheme.outlineVariant),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          Icon(Icons.info_outline, size: 22, color: cor),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              '${SemDadosOffline.mensagem} Exibindo o resumo disponível.',
              style: tema.textTheme.bodyMedium?.copyWith(color: cor),
            ),
          ),
        ],
      ),
    );
  }
}

/// Pull-to-refresh sem conexão: mantém os dados e avisa discretamente.
void avisarSemConexao(BuildContext context) {
  ScaffoldMessenger.maybeOf(context)
    ?..hideCurrentSnackBar()
    ..showSnackBar(
      const SnackBar(
        content: Text('Sem conexão. Exibindo última atualização disponível.'),
        behavior: SnackBarBehavior.floating,
      ),
    );
}
