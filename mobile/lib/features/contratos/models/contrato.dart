import '../../../shared/utils/json.dart';
import '../../obras/models/obra.dart';
import '../../obras/models/obra_detalhe.dart';

/// Situação da vigência calculada pela API (`situacao_vigencia`).
enum SituacaoVigencia {
  vigente('vigente', 'Vigente'),
  venceEmBreve('vence_em_breve', 'Vence em breve'),
  vencido('vencido', 'Vencido'),
  semVigencia('sem_vigencia', 'Sem vigência');

  const SituacaoVigencia(this.api, this.label);

  /// Valor aceito pela API em `?situacao=`.
  final String api;
  final String label;

  bool get requerAtencao => this == vencido || this == venceEmBreve;

  static SituacaoVigencia fromApi(Object? valor) => values.firstWhere(
    (s) => s.api == valor,
    orElse: () => SituacaoVigencia.semVigencia,
  );
}

/// { "id": 15, "razao_social": "...", "nome_fantasia": "..." }
class EmpresaContratada {
  const EmpresaContratada({
    required this.id,
    required this.razaoSocial,
    this.nomeFantasia,
  });

  final int id;
  final String razaoSocial;
  final String? nomeFantasia;

  /// Mesmo critério de Empresa::nomeExibicao() no Laravel.
  String get nomeExibicao => nomeFantasia ?? razaoSocial;

  static EmpresaContratada? fromJson(Map<String, dynamic>? json) => json == null
      ? null
      : EmpresaContratada(
          id: asInt(json['id']),
          razaoSocial: asStringOrNull(json['razao_social']) ?? '—',
          nomeFantasia: asStringOrNull(json['nome_fantasia']),
        );
}

/// Obra vinculada. Na listagem vem só id/descricao; no detalhe também
/// endereco e status.
class ObraVinculada {
  const ObraVinculada({
    required this.id,
    required this.descricao,
    this.endereco,
    this.status,
  });

  final int id;
  final String descricao;
  final String? endereco;
  final StatusObra? status;

  static ObraVinculada? fromJson(Map<String, dynamic>? json) => json == null
      ? null
      : ObraVinculada(
          id: asInt(json['id']),
          descricao: asStringOrNull(json['descricao']) ?? 'Obra sem descrição',
          endereco: asStringOrNull(json['endereco']),
          status: StatusObra.fromJson(asMap(json['status'])),
        );
}

/// Item de `GET /contratos` (ContratoResource no Laravel).
class Contrato {
  const Contrato({
    required this.id,
    this.numeroContratoAno,
    this.processoLicitacao,
    this.obra,
    this.empresa,
    required this.valorContrato,
    required this.valorMedido,
    required this.saldo,
    required this.percentualExecutado,
    this.dataAssinatura,
    this.ordemInicio,
    this.vigenciaContrato,
    required this.situacao,
  });

  final int id;
  final String? numeroContratoAno;
  final String? processoLicitacao;
  final ObraVinculada? obra;
  final EmpresaContratada? empresa;
  final double valorContrato;
  final double valorMedido;
  final double saldo;
  final double percentualExecutado;
  final DateTime? dataAssinatura;
  final DateTime? ordemInicio;
  final DateTime? vigenciaContrato;
  final SituacaoVigencia situacao;

  String get titulo => numeroContratoAno != null
      ? 'Contrato $numeroContratoAno'
      : 'Contrato sem número';

  factory Contrato.fromJson(Map<String, dynamic> json) => Contrato(
    id: asInt(json['id']),
    numeroContratoAno: asStringOrNull(json['numero_contrato_ano']),
    processoLicitacao: asStringOrNull(json['processo_licitacao']),
    obra: ObraVinculada.fromJson(asMap(json['obra'])),
    empresa: EmpresaContratada.fromJson(asMap(json['empresa'])),
    valorContrato: asDouble(json['valor_contrato']),
    valorMedido: asDouble(json['valor_medido']),
    saldo: asDouble(json['saldo']),
    percentualExecutado: asDouble(json['percentual_executado']),
    dataAssinatura: asDate(json['data_assinatura']),
    ordemInicio: asDate(json['ordem_inicio']),
    vigenciaContrato: asDate(json['vigencia_contrato']),
    situacao: SituacaoVigencia.fromApi(json['situacao_vigencia']),
  );
}

/// Página de `GET /contratos` (paginação padrão do Laravel: data + meta).
class ContratosPagina {
  const ContratosPagina({
    required this.contratos,
    required this.currentPage,
    required this.lastPage,
    required this.total,
  });

  final List<Contrato> contratos;
  final int currentPage;
  final int lastPage;
  final int total;

  bool get hasMore => currentPage < lastPage;

  factory ContratosPagina.fromJson(Map<String, dynamic> json) {
    final meta = asMap(json['meta']) ?? const {};
    return ContratosPagina(
      contratos: asMapList(json['data']).map(Contrato.fromJson).toList(),
      currentPage: asInt(meta['current_page']),
      lastPage: asInt(meta['last_page']),
      total: asInt(meta['total']),
    );
  }
}

/// `GET /contratos/{id}`: campos da listagem + obra completa + últimas medições.
class ContratoDetalhe {
  const ContratoDetalhe({
    required this.contrato,
    required this.ultimasMedicoes,
  });

  final Contrato contrato;

  /// Reaproveita o model de medição da obra (id, data_medicao, valor_medido).
  final List<Medicao> ultimasMedicoes;

  factory ContratoDetalhe.fromJson(Map<String, dynamic> json) =>
      ContratoDetalhe(
        contrato: Contrato.fromJson(json),
        ultimasMedicoes: asMapList(
          json['ultimas_medicoes'],
        ).map(Medicao.fromJson).toList(),
      );
}
