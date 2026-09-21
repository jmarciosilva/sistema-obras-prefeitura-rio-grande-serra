import '../../../shared/utils/json.dart';

/// { "id": 3, "nome": "Em Execução", "cor": "#0d6efd" }
class StatusObra {
  const StatusObra({required this.id, required this.nome, this.cor});

  final int id;
  final String nome;
  final String? cor;

  static StatusObra? fromJson(Map<String, dynamic>? json) => json == null
      ? null
      : StatusObra(
          id: asInt(json['id']),
          nome: asStringOrNull(json['nome']) ?? '—',
          cor: asStringOrNull(json['cor']),
        );
}

/// Item de `GET /obras` (ObraResource no Laravel).
class Obra {
  const Obra({
    required this.id,
    required this.descricao,
    this.endereco,
    this.status,
    required this.valorContratado,
    required this.valorMedido,
    required this.saldo,
    required this.percentualExecutado,
  });

  final int id;
  final String descricao;
  final String? endereco;
  final StatusObra? status;
  final double valorContratado;
  final double valorMedido;
  final double saldo;
  final double percentualExecutado;

  factory Obra.fromJson(Map<String, dynamic> json) => Obra(
    id: asInt(json['id']),
    descricao: asStringOrNull(json['descricao']) ?? 'Obra sem descrição',
    endereco: asStringOrNull(json['endereco']),
    status: StatusObra.fromJson(asMap(json['status'])),
    valorContratado: asDouble(json['valor_contratado']),
    valorMedido: asDouble(json['valor_medido']),
    saldo: asDouble(json['saldo']),
    percentualExecutado: asDouble(json['percentual_executado']),
  );
}

/// Página de `GET /obras` (paginação padrão do Laravel):
/// { "data": [...], "links": {...},
///   "meta": { "current_page": 1, "last_page": 3, "per_page": 15, "total": 23, ... } }
class ObrasPage {
  const ObrasPage({
    required this.obras,
    required this.currentPage,
    required this.lastPage,
    required this.total,
  });

  final List<Obra> obras;
  final int currentPage;
  final int lastPage;
  final int total;

  bool get hasMore => currentPage < lastPage;

  factory ObrasPage.fromJson(Map<String, dynamic> json) {
    final meta = asMap(json['meta']) ?? const {};
    return ObrasPage(
      obras: asMapList(json['data']).map(Obra.fromJson).toList(),
      currentPage: asInt(meta['current_page']),
      lastPage: asInt(meta['last_page']),
      total: asInt(meta['total']),
    );
  }
}
