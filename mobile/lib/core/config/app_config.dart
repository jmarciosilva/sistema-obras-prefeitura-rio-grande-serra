/// Configuração central do app.
///
/// A API padrão é a de PRODUÇÃO ([producao]). Para desenvolver contra o
/// servidor local (`php artisan serve`), use `--dart-define`:
///  - Android Emulator: `--dart-define=API_BASE_URL=http://10.0.2.2:8000`
///    (localhost do Windows visto de dentro do emulador)
///  - demais plataformas: `--dart-define=API_BASE_URL=http://localhost:8000`
///
/// HTTP sem TLS só funciona no build debug; release exige HTTPS.
class AppConfig {
  AppConfig._();

  static const String appName = 'Obras RGS';

  static const String _apiBaseUrlDefine = String.fromEnvironment(
    'API_BASE_URL',
  );

  /// Servidor de produção (Hostinger).
  static const String producao = 'https://prefeitura.jmfsystem.com';

  /// URL do servidor, sem barra final.
  static String get apiBaseUrl {
    final url = _apiBaseUrlDefine.isNotEmpty ? _apiBaseUrlDefine : producao;
    return url.replaceAll(RegExp(r'/+$'), '');
  }

  /// Raiz da API versionada. Ex.: https://prefeitura.jmfsystem.com/api/v1
  static String get apiUrl => '$apiBaseUrl/api/v1';

  static const Duration connectTimeout = Duration(seconds: 10);
  static const Duration receiveTimeout = Duration(seconds: 20);

  /// Identifica o aparelho no nome do token (visível só no servidor).
  static const String deviceName = 'app-mobile';
}
