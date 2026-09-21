import '../../../shared/utils/json.dart';
import 'obra.dart';

/// Contrato de `GET /obras/{id}` (ObraDetalheResource no Laravel):
/// campos da listagem + processo_execucao, contratos e ultimas_medicoes.
class ObraDetalhe {
  const ObraDetalhe({
    required this.obra,
    this.processoExecucao,
    required this.contratos,
    required this.ultimasMedicoes,
  });

  final Obra obra;
  final String? processoExecucao;
  final List<Contrato> contratos;
  final List<Medicao> ultimasMedicoes;

  factory ObraDetalhe.fromJson(Map<String, dynamic> json) => ObraDetalhe(
    obra: Obra.fromJson(json),
    processoExecucao: asStringOrNull(json['processo_execucao']),
    contratos: asMapList(json['contratos']).map(Contrato.fromJson).toList(),
    ultimasMedicoes: asMapList(
      json['ultimas_medicoes'],
    ).map(Medicao.fromJson).toList(),
  );
}

class Empresa {
  const Empresa({required this.id, required this.nome, this.cnpj});

  final int id;
  final String nome;
  final String? cnpj;

  static Empresa? fromJson(Map<String, dynamic>? json) => json == null
      ? null
      : Empresa(
          id: asInt(json['id']),
          nome: asStringOrNull(json['nome']) ?? '—',
          cnpj: asStringOrNull(json['cnpj']),
        );
}

class Contrato {
  const Contrato({
    required this.id,
    this.numeroContrato,
    this.processoLicitacao,
    this.empresa,
    this.dataAssinatura,
    this.ordemInicio,
    this.vigenciaContrato,
    required this.vencido,
    required this.valorContrato,
    required this.valorMedido,
  });

  final int id;
  final String? numeroContrato;
  final String? processoLicitacao;
  final Empresa? empresa;
  final DateTime? dataAssinatura;
  final DateTime? ordemInicio;
  final DateTime? vigenciaContrato;
  final bool vencido;
  final double valorContrato;
  final double valorMedido;

  factory Contrato.fromJson(Map<String, dynamic> json) => Contrato(
    id: asInt(json['id']),
    numeroContrato: asStringOrNull(json['numero_contrato']),
    processoLicitacao: asStringOrNull(json['processo_licitacao']),
    empresa: Empresa.fromJson(asMap(json['empresa'])),
    dataAssinatura: asDate(json['data_assinatura']),
    ordemInicio: asDate(json['ordem_inicio']),
    vigenciaContrato: asDate(json['vigencia_contrato']),
    vencido: json['vencido'] == true,
    valorContrato: asDouble(json['valor_contrato']),
    valorMedido: asDouble(json['valor_medido']),
  );
}

class Medicao {
  const Medicao({
    required this.id,
    required this.contratoId,
    this.numeroContrato,
    this.dataMedicao,
    required this.valorMedido,
    this.percentualExecutado,
  });

  final int id;
  final int contratoId;
  final String? numeroContrato;
  final DateTime? dataMedicao;
  final double valorMedido;
  final double? percentualExecutado;

  factory Medicao.fromJson(Map<String, dynamic> json) => Medicao(
    id: asInt(json['id']),
    contratoId: asInt(json['contrato_id']),
    numeroContrato: asStringOrNull(json['numero_contrato']),
    dataMedicao: asDate(json['data_medicao']),
    valorMedido: asDouble(json['valor_medido']),
    percentualExecutado: asDoubleOrNull(json['percentual_executado']),
  );
}
