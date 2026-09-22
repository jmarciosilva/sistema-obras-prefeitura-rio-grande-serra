import 'package:flutter_test/flutter_test.dart';
import 'package:obras_rgs/core/config/app_config.dart';

void main() {
  test('sem --dart-define, o app usa a API de produção (HTTPS)', () {
    expect(AppConfig.apiBaseUrl, 'https://prefeitura.jmfsystem.com');
    expect(AppConfig.apiUrl, 'https://prefeitura.jmfsystem.com/api/v1');
    expect(AppConfig.apiBaseUrl, startsWith('https://'));
  });
}
