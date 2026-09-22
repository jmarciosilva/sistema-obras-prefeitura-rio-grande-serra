import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../features/auth/models/user.dart';
import '../api/api_exception.dart';
import '../cache/cache_repository.dart';
import '../cache/sync_state.dart';
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

/// Há token, mas não foi possível validá-lo (ex.: sem conexão) e ainda não
/// existe usuário salvo para abrir em modo offline. O token é mantido; o
/// usuário pode tentar novamente.
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

  /// Abertura do app:
  /// - sem token → login;
  /// - token vencido localmente → limpa tudo → login (nunca abre offline);
  /// - valida com GET /me:
  ///   - 200 → atualiza o usuário salvo e entra (online);
  ///   - 401 → limpa tudo → login (offline nunca ignora um 401);
  ///   - sem conexão/servidor fora + usuário salvo → entra em modo offline;
  ///   - sem conexão e sem usuário salvo → tela de "Tentar novamente".
  Future<void> restore() async {
    state = const AuthChecking();
    final storage = ref.read(sessionStorageProvider);

    final token = await storage.readToken();
    if (token == null || token.isEmpty) {
      await _encerrarSessaoLocal(await storage.readUser());
      state = const Unauthenticated();
      return;
    }

    final salvo = await storage.readUser();
    final expiresAt = await storage.readExpiresAt();
    if (expiresAt != null && expiresAt.isBefore(DateTime.now())) {
      await _encerrarSessaoLocal(salvo);
      state = const Unauthenticated(message: sessaoExpirada);
      return;
    }

    final sync = ref.read(syncProvider.notifier);
    try {
      final user = await ref.read(authApiProvider).me();
      await storage.saveUser(user);
      sync.sucesso();
      state = Authenticated(user);
    } on ApiException catch (e) {
      if (e.type == ApiErrorType.unauthorized) {
        await _encerrarSessaoLocal(salvo);
        state = const Unauthenticated(message: sessaoExpirada);
      } else if (e.servidorInacessivel && salvo != null) {
        sync.marcarOffline();
        state = Authenticated(salvo);
      } else {
        state = AuthCheckFailed(e.message);
      }
    }
  }

  /// Lança [ApiException] em caso de falha (a tela de login trata).
  /// Login novo sempre exige conexão.
  Future<void> login(String email, String password) async {
    final resposta = await ref.read(authApiProvider).login(email, password);
    final storage = ref.read(sessionStorageProvider);
    await storage.saveToken(resposta.token, expiresAt: resposta.expiresAt);
    await storage.saveUser(resposta.user);
    // Nenhum dado de outro usuário pode permanecer no aparelho
    await ref
        .read(cacheRepositoryProvider)
        .limparOutrosUsuarios(resposta.user.id);
    ref.read(syncProvider.notifier).sucesso();
    state = Authenticated(resposta.user);
  }

  /// Revoga o token no servidor; mesmo sem conexão, limpa a sessão local
  /// (token, usuário salvo, cache e estado de sincronização).
  Future<void> logout() async {
    final atual = state;
    try {
      await ref.read(authApiProvider).logout();
    } catch (_) {
      // Sem conexão ou token já inválido: segue com a limpeza local.
    }
    await _encerrarSessaoLocal(
      atual is Authenticated
          ? atual.user
          : await ref.read(sessionStorageProvider).readUser(),
    );
    state = const Unauthenticated();
  }

  /// Chamado pelo ApiClient ao receber 401 (token já apagado).
  void sessionExpired() {
    final atual = state;
    if (atual is Authenticated || atual is AuthChecking) {
      state = const Unauthenticated(message: sessaoExpirada);
      _encerrarSessaoLocal(atual is Authenticated ? atual.user : null);
    }
  }

  /// Nenhuma informação protegida pode sobrar após o fim da sessão.
  Future<void> _encerrarSessaoLocal(User? usuario) async {
    await ref.read(sessionStorageProvider).clear();
    final cache = ref.read(cacheRepositoryProvider);
    if (usuario != null) {
      await cache.limparUsuario(usuario.id);
    } else {
      await cache.limparTudo();
    }
    ref.read(syncProvider.notifier).reset();
  }
}
