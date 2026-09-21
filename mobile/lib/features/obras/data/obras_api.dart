import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client.dart';
import '../../../core/providers.dart';
import '../models/obra.dart';
import '../models/obra_detalhe.dart';

class ObrasApi {
  ObrasApi(this._client);

  final ApiClient _client;

  static const porPagina = 15;

  Future<ObrasPage> listar({
    int page = 1,
    String? search,
    int? statusId,
  }) async {
    final json = await _client.get(
      '/obras',
      query: {
        'page': page,
        'per_page': porPagina,
        if (search != null && search.trim().isNotEmpty) 'search': search.trim(),
        'status': ?statusId,
      },
    );
    return ObrasPage.fromJson(json);
  }

  Future<ObraDetalhe> detalhe(int id) async =>
      ObraDetalhe.fromJson(await _client.get('/obras/$id'));
}

final obrasApiProvider = Provider<ObrasApi>(
  (ref) => ObrasApi(ref.watch(apiClientProvider)),
);

final obraDetalheProvider = FutureProvider.autoDispose.family<ObraDetalhe, int>(
  (ref, id) => ref.watch(obrasApiProvider).detalhe(id),
);
