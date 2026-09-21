import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client.dart';
import '../../../core/auth/auth_state.dart';
import '../../../core/providers.dart';
import '../models/dashboard.dart';

class DashboardApi {
  DashboardApi(this._client);

  final ApiClient _client;

  Future<Dashboard> carregar() async =>
      Dashboard.fromJson(await _client.get('/dashboard'));
}

/// Recarrega ao trocar de usuário e com `ref.invalidate(dashboardProvider)`.
/// Também fornece a lista de status para o filtro da tela de Obras.
final dashboardProvider = FutureProvider<Dashboard>((ref) {
  final userId = ref.watch(
    authProvider.select((s) => s is Authenticated ? s.user.id : null),
  );
  if (userId == null) {
    return Future.error(StateError('Sem sessão'));
  }
  return DashboardApi(ref.watch(apiClientProvider)).carregar();
});
