import '../../../shared/utils/json.dart';

/// Contrato de `GET /dashboard`:
/// { "obras": {...}, "status": [...], "alertas": {...},
///   "atualizado_em": "2026-09-21T20:18:08+00:00" }
class Dashboard {
  const Dashboard({
    required this.obras,
    required this.status,
    required this.alertas,
    this.atualizadoEm,
  });

  final ResumoObras obras;
  final List<StatusQuantidade> status;
  final Alertas alertas;
  final DateTime? atualizadoEm;

  factory Dashboard.fromJson(Map<String, dynamic> json) => Dashboard(
    obras: ResumoObras.fromJson(asMap(json['obras']) ?? const {}),
    status: asMapList(json['status']).map(StatusQuantidade.fromJson).toList(),
    alertas: Alertas.fromJson(asMap(json['alertas']) ?? const {}),
    atualizadoEm: asDate(json['atualizado_em']),
  );
}

class ResumoObras {
  const ResumoObras({
    required this.total,
    required this.emExecucao,
    required this.concluidas,
    required this.valorContratado,
    required this.valorMedido,
    required this.saldo,
    required this.percentualExecutado,
  });

  final int total;
  final int emExecucao;
  final int concluidas;
  final double valorContratado;
  final double valorMedido;
  final double saldo;
  final double percentualExecutado;

  factory ResumoObras.fromJson(Map<String, dynamic> json) => ResumoObras(
    total: asInt(json['total']),
    emExecucao: asInt(json['em_execucao']),
    concluidas: asInt(json['concluidas']),
    valorContratado: asDouble(json['valor_contratado']),
    valorMedido: asDouble(json['valor_medido']),
    saldo: asDouble(json['saldo']),
    percentualExecutado: asDouble(json['percentual_executado']),
  );
}

/// Item de "status": { "id": 3, "nome": "Em Execução", "cor": "#0d6efd", "total": 10 }
class StatusQuantidade {
  const StatusQuantidade({
    required this.id,
    required this.nome,
    required this.cor,
    required this.total,
  });

  final int id;
  final String nome;
  final String? cor;
  final int total;

  factory StatusQuantidade.fromJson(Map<String, dynamic> json) =>
      StatusQuantidade(
        id: asInt(json['id']),
        nome: asStringOrNull(json['nome']) ?? '—',
        cor: asStringOrNull(json['cor']),
        total: asInt(json['total']),
      );
}

class Alertas {
  const Alertas({
    required this.contratosVencidos,
    required this.contratosVencendo,
    required this.obrasSemMedicao,
  });

  final int contratosVencidos;
  final int contratosVencendo;
  final int obrasSemMedicao;

  int get total => contratosVencidos + contratosVencendo + obrasSemMedicao;

  factory Alertas.fromJson(Map<String, dynamic> json) => Alertas(
    contratosVencidos: asInt(json['contratos_vencidos']),
    contratosVencendo: asInt(json['contratos_vencendo']),
    obrasSemMedicao: asInt(json['obras_sem_medicao']),
  );
}
