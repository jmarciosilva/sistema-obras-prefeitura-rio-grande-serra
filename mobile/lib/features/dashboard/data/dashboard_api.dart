import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client.dart';
import '../../../core/cache/consulta_offline.dart';
import '../../../core/providers.dart';
import '../models/dashboard.dart';

class DashboardApi {
  DashboardApi(this._client);

  final ApiClient _client;

  /// JSON original (é ele que vai para o cache).
  Future<Map<String, dynamic>> carregar() => _client.get('/dashboard');
}

final dashboardApiProvider = Provider<DashboardApi>(
  (ref) => DashboardApi(ref.watch(apiClientProvider)),
);

/// Cache primeiro, revalida com a API em segundo plano.
/// Recarrega ao trocar de usuário. Também fornece a lista de status para o
/// filtro da tela de Obras.
class DashboardNotifier extends ConsultaNotifier<Dashboard> {
  @override
  Swr<Dashboard> swr(ConsultaOffline consulta) => Swr(
    consulta: consulta,
    chave: 'dashboard',
    api: ref.read(dashboardApiProvider).carregar,
    converter: Dashboard.fromJson,
  );
}

final dashboardProvider =
    AsyncNotifierProvider<DashboardNotifier, Dados<Dashboard>>(
      DashboardNotifier.new,
    );
