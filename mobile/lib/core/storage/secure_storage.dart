import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Guarda APENAS o token da API e sua validade, no armazenamento seguro
/// do sistema (Keystore no Android, Keychain no iOS).
/// Senha e credenciais nunca são salvas.
class SessionStorage {
  SessionStorage([FlutterSecureStorage? storage])
    : _storage = storage ?? const FlutterSecureStorage();

  final FlutterSecureStorage _storage;

  static const _kToken = 'api_token';
  static const _kExpiresAt = 'api_token_expires_at';

  Future<String?> readToken() => _storage.read(key: _kToken);

  Future<DateTime?> readExpiresAt() async {
    final value = await _storage.read(key: _kExpiresAt);
    return value == null ? null : DateTime.tryParse(value);
  }

  Future<void> saveToken(String token, {DateTime? expiresAt}) async {
    await _storage.write(key: _kToken, value: token);
    if (expiresAt != null) {
      await _storage.write(
        key: _kExpiresAt,
        value: expiresAt.toUtc().toIso8601String(),
      );
    } else {
      await _storage.delete(key: _kExpiresAt);
    }
  }

  Future<void> clear() async {
    await _storage.delete(key: _kToken);
    await _storage.delete(key: _kExpiresAt);
  }
}
