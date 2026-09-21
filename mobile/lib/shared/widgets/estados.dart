import 'package:flutter/material.dart';

import '../../core/api/api_exception.dart';

/// Mensagem amigável para qualquer erro (nunca mostra detalhes técnicos).
String mensagemDeErro(Object erro) =>
    erro is ApiException ? erro.message : 'Erro ao carregar dados.';

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
    final semConexao =
        erro is ApiException &&
        (erro as ApiException).type == ApiErrorType.network;

    return _Centro(
      icone: semConexao ? Icons.wifi_off_rounded : Icons.error_outline_rounded,
      titulo: semConexao ? 'Sem conexão' : 'Erro ao carregar dados',
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
  });

  final String mensagem;
  final IconData icone;

  @override
  Widget build(BuildContext context) => _Centro(icone: icone, titulo: mensagem);
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
            Icon(icone, size: 56, color: tema.colorScheme.outline),
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
                style: tema.textTheme.bodyMedium,
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
