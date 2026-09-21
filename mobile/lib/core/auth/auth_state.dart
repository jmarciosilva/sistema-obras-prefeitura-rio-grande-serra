import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../features/auth/models/user.dart';
import '../api/api_exception.dart';
import '../providers.dart';

/// Estado da sessão. O app decide a tela raiz a partir dele.
sealed class AuthState {
  const AuthState();
}

/// Abrindo o app / validando o token salvo (splash).
class AuthChecking extends AuthState {
  const AuthChecking();
}

class Authenticated extends AuthState {
  const Authenticated(this.user);
  final User user;
}

/// Sem sessão. [message] explica o motivo (ex.: sessão expirada).
class Unauthenticated extends AuthState {
  const Unauthenticated({this.message});
  final String? message;
}

/// Há token, mas não foi possível validá-lo (ex.: sem conexão).
/// O token é mantido; o usuário pode tentar novamente.
class AuthCheckFailed extends AuthState {
  const AuthCheckFailed(this.message);
  final String message;
}

final authProvider = NotifierProvider<AuthNotifier, AuthState>(
  AuthNotifier.new,
);

class AuthNotifier extends Notifier<AuthState> {
  static const sessaoExpirada = 'Sua sessão expirou. Faça login novamente.';

  @override
  AuthState build() {
    Future.microtask(restore);
    return const AuthChecking();
  }

  /// Abertura do app: não confia só no token local, valida com GET /me.
  Future<void> restore() async {
    state = const AuthChecking();
    final storage = ref.read(sessionStorageProvider);

    final token = await storage.readToken();
    if (token == null || token.isEmpty) {
      state = const Unauthenticated();
      return;
    }

    final expiresAt = await storage.readExpiresAt();
    if (expiresAt != null && expiresAt.isBefore(DateTime.now())) {
      await storage.clear();
      state = const Unauthenticated(message: sessaoExpirada);
      return;
    }

    try {
      state = Authenticated(await ref.read(authApiProvider).me());
    } on ApiException catch (e) {
      if (e.type == ApiErrorType.unauthorized) {
        await storage.clear();
        state = const Unauthenticated(message: sessaoExpirada);
      } else {
        state = AuthCheckFailed(e.message);
      }
    }
  }

  /// Lança [ApiException] em caso de falha (a tela de login trata).
  Future<void> login(String email, String password) async {
    final resposta = await ref.read(authApiProvider).login(email, password);
    await ref
        .read(sessionStorageProvider)
        .saveToken(resposta.token, expiresAt: resposta.expiresAt);
    state = Authenticated(resposta.user);
  }

  /// Revoga o token no servidor; mesmo sem conexão, limpa a sessão local.
  Future<void> logout() async {
    try {
      await ref.read(authApiProvider).logout();
    } catch (_) {
      // Sem conexão ou token já inválido: segue com a limpeza local.
    }
    await ref.read(sessionStorageProvider).clear();
    state = const Unauthenticated();
  }

  /// Chamado pelo ApiClient ao receber 401 (token já apagado).
  void sessionExpired() {
    if (state is Authenticated || state is AuthChecking) {
      state = const Unauthenticated(message: sessaoExpirada);
    }
  }
}
