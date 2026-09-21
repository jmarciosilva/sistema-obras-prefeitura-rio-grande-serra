import 'package:flutter/foundation.dart';

/// Configuração central do app.
///
/// A URL da API vem de `--dart-define=API_BASE_URL=...`. Sem ela, usa o
/// servidor local de desenvolvimento (`php artisan serve`):
///  - Android Emulator: http://10.0.2.2:8000 (localhost do Windows visto
///    de dentro do emulador)
///  - demais plataformas: http://localhost:8000
class AppConfig {
  AppConfig._();

  static const String appName = 'Obras RGS';

  static const String _apiBaseUrlDefine = String.fromEnvironment(
    'API_BASE_URL',
  );

  /// URL do servidor, sem barra final. Ex.: http://10.0.2.2:8000
  static String get apiBaseUrl {
    final url = _apiBaseUrlDefine.isNotEmpty
        ? _apiBaseUrlDefine
        : (!kIsWeb && defaultTargetPlatform == TargetPlatform.android
              ? 'http://10.0.2.2:8000'
              : 'http://localhost:8000');
    return url.replaceAll(RegExp(r'/+$'), '');
  }

  /// Raiz da API versionada. Ex.: http://10.0.2.2:8000/api/v1
  static String get apiUrl => '$apiBaseUrl/api/v1';

  static const Duration connectTimeout = Duration(seconds: 10);
  static const Duration receiveTimeout = Duration(seconds: 20);

  /// Identifica o aparelho no nome do token (visível só no servidor).
  static const String deviceName = 'app-mobile';
}
