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
      // Cards brancos com borda sutil: separação limpa sem sombras pesadas
      cardTheme: CardThemeData(
        elevation: 0,
        color: esquema.surfaceContainerLowest,
        surfaceTintColor: Colors.transparent,
        margin: const EdgeInsets.symmetric(vertical: 6),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: BorderSide(
            color: esquema.outlineVariant.withValues(alpha: 0.6),
          ),
        ),
      ),
      dividerTheme: DividerThemeData(
        color: esquema.outlineVariant.withValues(alpha: 0.6),
      ),
      navigationBarTheme: NavigationBarThemeData(
        backgroundColor: esquema.surfaceContainerLowest,
        indicatorColor: esquema.primaryContainer,
        surfaceTintColor: Colors.transparent,
        elevation: 3,
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: esquema.surfaceContainerLowest,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
      ),
      chipTheme: ChipThemeData(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      ),
    );
  }
}
