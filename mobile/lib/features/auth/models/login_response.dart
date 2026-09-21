import '../../../shared/utils/json.dart';
import 'user.dart';

/// Contrato de `POST /login` (200):
/// { "token": "3|abc", "token_type": "Bearer",
///   "expires_at": "2026-10-21T20:18:07+00:00", "user": {...} }
class LoginResponse {
  const LoginResponse({
    required this.token,
    required this.user,
    this.expiresAt,
  });

  final String token;
  final DateTime? expiresAt;
  final User user;

  factory LoginResponse.fromJson(Map<String, dynamic> json) {
    final token = asStringOrNull(json['token']);
    final user = asMap(json['user']);
    if (token == null || user == null) {
      throw const FormatException('Resposta de login incompleta.');
    }
    return LoginResponse(
      token: token,
      expiresAt: asDate(json['expires_at']),
      user: User.fromJson(user),
    );
  }
}
