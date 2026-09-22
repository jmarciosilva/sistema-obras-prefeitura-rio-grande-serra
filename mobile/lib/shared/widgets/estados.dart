import 'package:flutter/material.dart';

import '../../core/api/api_exception.dart';
import '../../core/cache/consulta_offline.dart';

/// Mensagem amigável para qualquer erro (nunca mostra detalhes técnicos).
String mensagemDeErro(Object erro) => switch (erro) {
  ApiException() => erro.message,
  SemDadosOffline() => SemDadosOffline.mensagem,
  _ => 'Erro ao carregar dados.',
};

class LoadingView extends StatelessWidget {
  const LoadingView({super.key, this.mensagem = 'Carregando...'});

  final String mensagem;

  @override
  Widget build(BuildContext context) => Center(
    child: Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        const CircularProgressIndicator(),
        const SizedBox(height: 16),
        Text(mensagem, style: Theme.of(context).textTheme.bodyMedium),
      ],
    ),
  );
}

class ErrorView extends StatelessWidget {
  const ErrorView({super.key, required this.erro, required this.onRetry});

  final Object erro;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    final semDados = erro is SemDadosOffline;
    final semConexao =
        semDados ||
        (erro is ApiException &&
            (erro as ApiException).type == ApiErrorType.network);

    return _Centro(
      icone: semDados
          ? Icons.cloud_off_outlined
          : (semConexao ? Icons.wifi_off_rounded : Icons.error_outline_rounded),
      titulo: semConexao
          ? 'Sem conexão'
          : 'Não foi possível carregar os dados.',
      mensagem: mensagemDeErro(erro),
      acao: FilledButton.icon(
        onPressed: onRetry,
        icon: const Icon(Icons.refresh),
        label: const Text('Tentar novamente'),
      ),
    );
  }
}

class EmptyView extends StatelessWidget {
  const EmptyView({
    super.key,
    required this.mensagem,
    this.icone = Icons.inbox_outlined,
    this.detalhe,
  });

  final String mensagem;
  final IconData icone;

  /// Dica secundária (ex.: "Tente outra busca ou filtro.").
  final String? detalhe;

  @override
  Widget build(BuildContext context) =>
      _Centro(icone: icone, titulo: mensagem, mensagem: detalhe);
}

class _Centro extends StatelessWidget {
  const _Centro({
    required this.icone,
    required this.titulo,
    this.mensagem,
    this.acao,
  });

  final IconData icone;
  final String titulo;
  final String? mensagem;
  final Widget? acao;

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: tema.colorScheme.surfaceContainerHighest,
                shape: BoxShape.circle,
              ),
              child: Icon(icone, size: 40, color: tema.colorScheme.outline),
            ),
            const SizedBox(height: 16),
            Text(
              titulo,
              style: tema.textTheme.titleMedium,
              textAlign: TextAlign.center,
            ),
            if (mensagem != null) ...[
              const SizedBox(height: 8),
              Text(
                mensagem!,
                style: tema.textTheme.bodyMedium?.copyWith(
                  color: tema.colorScheme.onSurfaceVariant,
                ),
                textAlign: TextAlign.center,
              ),
            ],
            if (acao != null) ...[const SizedBox(height: 24), acao!],
          ],
        ),
      ),
    );
  }
}
