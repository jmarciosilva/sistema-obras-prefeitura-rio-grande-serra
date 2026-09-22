import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client.dart';
import '../../../core/cache/consulta_offline.dart';
import '../../../core/providers.dart';
import '../../../shared/utils/json.dart';
import '../models/contrato.dart';

class ContratosApi {
  ContratosApi(this._client);

  final ApiClient _client;

  /// Máximo aceito pela API (`per_page` max:50).
  static const porPagina = 50;

  /// Limite de segurança contra laço infinito em resposta inesperada.
  static const _maxPaginas = 200;

  /// Lista COMPLETA de contratos (todas as páginas), `{ "data": [...] }`.
  /// Busca e filtro por situação são aplicados no aparelho.
  Future<Map<String, dynamic>> listarTodos() async {
    final itens = <Map<String, dynamic>>[];
    var pagina = 1;
    var ultima = 1;
    do {
      final json = await _client.get(
        '/contratos',
        query: {'page': pagina, 'per_page': porPagina},
      );
      itens.addAll(asMapList(json['data']));
      ultima = asInt(asMap(json['meta'])?['last_page']);
      pagina++;
    } while (pagina <= ultima && pagina <= _maxPaginas);
    return {'data': itens};
  }

  Future<Map<String, dynamic>> detalhe(int id) => _client.get('/contratos/$id');
}

final contratosApiProvider = Provider<ContratosApi>(
  (ref) => ContratosApi(ref.watch(apiClientProvider)),
);

List<Contrato> _contratosDoJson(Map<String, dynamic> json) =>
    asMapList(json['data']).map(Contrato.fromJson).toList();

/// Todos os contratos (cache primeiro, sincroniza em segundo plano).
class ContratosNotifier extends ConsultaNotifier<List<Contrato>> {
  @override
  Swr<List<Contrato>> swr(ConsultaOffline consulta) => Swr(
    consulta: consulta,
    chave: 'contratos',
    api: ref.read(contratosApiProvider).listarTodos,
    converter: _contratosDoJson,
  );
}

final contratosProvider =
    AsyncNotifierProvider<ContratosNotifier, Dados<List<Contrato>>>(
      ContratosNotifier.new,
    );

/// Detalhe: salvo ao ser visitado. Offline e nunca visitado → resumo com os
/// dados da lista (empresa, valores e vigência já vêm nela).
class ContratoDetalheNotifier extends ConsultaDetalheNotifier<ContratoDetalhe> {
  @override
  Swr<ContratoDetalhe> swr(ConsultaOffline consulta, int id) => Swr(
    consulta: consulta,
    chave: 'contrato:$id',
    api: () => ref.read(contratosApiProvider).detalhe(id),
    converter: ContratoDetalhe.fromJson,
    alternativaOffline: () async {
      final lista = await consulta.lerCache('contratos');
      final item = asMapList(
        lista?.payload['data'],
      ).where((c) => asInt(c['id']) == id).firstOrNull;
      if (lista == null || item == null) return null;
      return Dados(
        ContratoDetalhe(
          contrato: Contrato.fromJson(item),
          ultimasMedicoes: const [],
        ),
        atualizadoEm: lista.atualizadoEm,
        doCache: true,
        parcial: true,
      );
    },
  );
}

final contratoDetalheProvider = AsyncNotifierProvider.autoDispose
    .family<ContratoDetalheNotifier, Dados<ContratoDetalhe>, int>(
      ContratoDetalheNotifier.new,
    );
