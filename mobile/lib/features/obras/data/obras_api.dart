import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client.dart';
import '../../../core/cache/consulta_offline.dart';
import '../../../core/providers.dart';
import '../../../shared/utils/json.dart';
import '../models/obra.dart';
import '../models/obra_detalhe.dart';

class ObrasApi {
  ObrasApi(this._client);

  final ApiClient _client;

  /// Máximo aceito pela API (`per_page` max:50).
  static const porPagina = 50;

  /// Limite de segurança contra laço infinito em resposta inesperada.
  static const _maxPaginas = 200;

  /// Lista COMPLETA de obras (percorre todas as páginas), no formato
  /// `{ "data": [...] }`. Busca e filtro são aplicados no aparelho, o que
  /// funciona igual online e offline.
  Future<Map<String, dynamic>> listarTodas() async {
    final itens = <Map<String, dynamic>>[];
    var pagina = 1;
    var ultima = 1;
    do {
      final json = await _client.get(
        '/obras',
        query: {'page': pagina, 'per_page': porPagina},
      );
      itens.addAll(asMapList(json['data']));
      ultima = asInt(asMap(json['meta'])?['last_page']);
      pagina++;
    } while (pagina <= ultima && pagina <= _maxPaginas);
    return {'data': itens};
  }

  Future<Map<String, dynamic>> detalhe(int id) => _client.get('/obras/$id');
}

final obrasApiProvider = Provider<ObrasApi>(
  (ref) => ObrasApi(ref.watch(apiClientProvider)),
);

List<Obra> _obrasDoJson(Map<String, dynamic> json) =>
    asMapList(json['data']).map(Obra.fromJson).toList();

/// Todas as obras (cache primeiro, sincroniza em segundo plano).
class ObrasNotifier extends ConsultaNotifier<List<Obra>> {
  @override
  Swr<List<Obra>> swr(ConsultaOffline consulta) => Swr(
    consulta: consulta,
    chave: 'obras',
    api: ref.read(obrasApiProvider).listarTodas,
    converter: _obrasDoJson,
  );
}

final obrasProvider = AsyncNotifierProvider<ObrasNotifier, Dados<List<Obra>>>(
  ObrasNotifier.new,
);

/// Detalhe: salvo ao ser visitado. Offline e nunca visitado → resumo com os
/// dados da lista (quando houver).
class ObraDetalheNotifier extends ConsultaDetalheNotifier<ObraDetalhe> {
  @override
  Swr<ObraDetalhe> swr(ConsultaOffline consulta, int id) => Swr(
    consulta: consulta,
    chave: 'obra:$id',
    api: () => ref.read(obrasApiProvider).detalhe(id),
    converter: ObraDetalhe.fromJson,
    alternativaOffline: () async {
      final lista = await consulta.lerCache('obras');
      final item = asMapList(
        lista?.payload['data'],
      ).where((o) => asInt(o['id']) == id).firstOrNull;
      if (lista == null || item == null) return null;
      return Dados(
        ObraDetalhe(
          obra: Obra.fromJson(item),
          contratos: const [],
          ultimasMedicoes: const [],
        ),
        atualizadoEm: lista.atualizadoEm,
        doCache: true,
        parcial: true,
      );
    },
  );
}

final obraDetalheProvider = AsyncNotifierProvider.autoDispose
    .family<ObraDetalheNotifier, Dados<ObraDetalhe>, int>(
      ObraDetalheNotifier.new,
    );
