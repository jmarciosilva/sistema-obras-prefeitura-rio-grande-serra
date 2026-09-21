import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/auth/auth_state.dart';

/// Abertura do app: valida o token salvo com GET /me.
/// Se não der para validar (ex.: sem conexão), oferece tentar novamente.
class SplashPage extends ConsumerWidget {
  const SplashPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final auth = ref.watch(authProvider);
    final tema = Theme.of(context);
    final falha = auth is AuthCheckFailed ? auth : null;

    return Scaffold(
      body: SafeArea(
        child: Center(
          child: Padding(
            padding: const EdgeInsets.all(32),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  Icons.apartment_rounded,
                  size: 72,
                  color: tema.colorScheme.primary,
                ),
                const SizedBox(height: 16),
                Text('Obras RGS', style: tema.textTheme.headlineSmall),
                const SizedBox(height: 4),
                Text(
                  'Prefeitura de Rio Grande da Serra',
                  style: tema.textTheme.bodyMedium,
                ),
                const SizedBox(height: 32),
                if (falha == null)
                  const CircularProgressIndicator()
                else ...[
                  Text(
                    falha.message,
                    textAlign: TextAlign.center,
                    style: tema.textTheme.bodyMedium,
                  ),
                  const SizedBox(height: 16),
                  FilledButton.icon(
                    onPressed: () => ref.read(authProvider.notifier).restore(),
                    icon: const Icon(Icons.refresh),
                    label: const Text('Tentar novamente'),
                  ),
                  TextButton(
                    onPressed: () => ref.read(authProvider.notifier).logout(),
                    child: const Text('Entrar com outra conta'),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}
