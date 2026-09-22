import 'dart:convert';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../../features/auth/models/user.dart';

/// Guarda no armazenamento seguro do sistema (Keystore no Android,
/// Keychain no iOS) APENAS:
/// - o token da API e sua validade;
/// - os dados mínimos do último usuário autenticado (id, nome, e-mail,
///   perfil), para permitir a sessão offline.
/// Senha e credenciais nunca são salvas.
class SessionStorage {
  SessionStorage([FlutterSecureStorage? storage])
    : _storage = storage ?? const FlutterSecureStorage();

  final FlutterSecureStorage _storage;

  static const _kToken = 'api_token';
  static const _kExpiresAt = 'api_token_expires_at';
  static const _kUsuario = 'usuario';

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

  Future<User?> readUser() async {
    final value = await _storage.read(key: _kUsuario);
    if (value == null) return null;
    try {
      final json = jsonDecode(value);
      return json is Map<String, dynamic> ? User.fromJson(json) : null;
    } on FormatException {
      return null;
    }
  }

  Future<void> saveUser(User user) => _storage.write(
    key: _kUsuario,
    value: jsonEncode({
      'id': user.id,
      'name': user.name,
      'email': user.email,
      'perfil': user.perfil,
    }),
  );

  /// Encerra a sessão local: token, validade e usuário salvo.
  Future<void> clear() async {
    await _storage.delete(key: _kToken);
    await _storage.delete(key: _kExpiresAt);
    await _storage.delete(key: _kUsuario);
  }
}
