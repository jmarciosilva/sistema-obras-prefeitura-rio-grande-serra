import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'core/auth/auth_state.dart';
import 'core/config/app_config.dart';
import 'features/auth/presentation/login_page.dart';
import 'features/auth/presentation/splash_page.dart';
import 'features/home/home_page.dart';

void main() {
  runApp(const ProviderScope(child: ObrasApp()));
}

final _navigatorKey = GlobalKey<NavigatorState>();

class ObrasApp extends ConsumerWidget {
  const ObrasApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final auth = ref.watch(authProvider);

    // Ao perder a sessão (logout ou 401), fecha telas empilhadas
    // (ex.: detalhe da obra) para o login aparecer por cima de tudo.
    ref.listen(authProvider, (anterior, atual) {
      if (atual is Unauthenticated) {
        _navigatorKey.currentState?.popUntil((r) => r.isFirst);
      }
    });

    return MaterialApp(
      title: AppConfig.appName,
      navigatorKey: _navigatorKey,
      debugShowCheckedModeBanner: false,
      theme: _tema(),
      home: switch (auth) {
        AuthChecking() || AuthCheckFailed() => const SplashPage(),
        Unauthenticated(:final message) => LoginPage(aviso: message),
        Authenticated() => const HomePage(),
      },
    );
  }

  ThemeData _tema() {
    // Azul institucional sóbrio
    const primaria = Color(0xFF0B4F8A);
    final esquema = ColorScheme.fromSeed(
      seedColor: primaria,
      primary: primaria,
    );
    return ThemeData(
      colorScheme: esquema,
      useMaterial3: true,
      scaffoldBackgroundColor: const Color(0xFFF4F6F9),
      appBarTheme: AppBarTheme(
        backgroundColor: esquema.primary,
        foregroundColor: esquema.onPrimary,
      ),
      cardTheme: const CardThemeData(
        elevation: 0.5,
        margin: EdgeInsets.symmetric(vertical: 6),
      ),
    );
  }
}
