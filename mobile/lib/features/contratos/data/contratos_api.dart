import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client.dart';
import '../../../core/providers.dart';
import '../models/contrato.dart';

class ContratosApi {
  ContratosApi(this._client);

  final ApiClient _client;

  static const porPagina = 15;

  Future<ContratosPagina> listar({
    int page = 1,
    String? search,
    SituacaoVigencia? situacao,
  }) async {
    final json = await _client.get(
      '/contratos',
      query: {
        'page': page,
        'per_page': porPagina,
        if (search != null && search.trim().isNotEmpty) 'search': search.trim(),
        'situacao': ?situacao?.api,
      },
    );
    return ContratosPagina.fromJson(json);
  }

  Future<ContratoDetalhe> detalhe(int id) async =>
      ContratoDetalhe.fromJson(await _client.get('/contratos/$id'));
}

final contratosApiProvider = Provider<ContratosApi>(
  (ref) => ContratosApi(ref.watch(apiClientProvider)),
);

final contratoDetalheProvider = FutureProvider.autoDispose
    .family<ContratoDetalhe, int>(
      (ref, id) => ref.watch(contratosApiProvider).detalhe(id),
    );
