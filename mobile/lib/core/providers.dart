import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../features/auth/data/auth_api.dart';
import 'api/api_client.dart';
import 'auth/auth_state.dart';
import 'storage/secure_storage.dart';

/// Providers de infraestrutura. Os de tela ficam em cada feature.

final sessionStorageProvider = Provider<SessionStorage>(
  (ref) => SessionStorage(),
);

final apiClientProvider = Provider<ApiClient>(
  (ref) => ApiClient(
    storage: ref.watch(sessionStorageProvider),
    // 401 em rota protegida → sessão encerrada → tela de login
    onUnauthorized: () => ref.read(authProvider.notifier).sessionExpired(),
  ),
);

final authApiProvider = Provider<AuthApi>(
  (ref) => AuthApi(ref.watch(apiClientProvider)),
);
