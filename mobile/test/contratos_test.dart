import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:obras_rgs/features/contratos/models/contrato.dart';
import 'package:obras_rgs/features/contratos/presentation/situacao_chip.dart';
import 'package:obras_rgs/shared/widgets/componentes.dart';

/// Fixture no formato real de GET /api/v1/contratos/{id} (MySQL local).
Map<String, dynamic> _detalheJson() => {
  'id': 1,
  'numero_contrato_ano': '33/2023',
  'processo_licitacao': '1864/2022',
  'obra': {
    'id': 41,
    'descricao': 'CT Nº 33/2023 - PAVIMENTAÇÃO - RUAS ACLIMAÇÃO E BELA VISTA',
    'endereco': null,
    'status': {'id': 4, 'nome': 'Paralisada', 'cor': '#dc3545'},
  },
  'empresa': {
    'id': 15,
    'razao_social': 'VIAPRECISA TERRAPLENAGEM E PAVIMENTAÇÃO LTDA',
    'nome_fantasia': 'VIA PRECISA',
  },
  'valor_contrato': 743798.67,
  'valor_medido': 237851.24,
  'saldo': 505947.43,
  'percentual_executado': 31.98,
  'data_assinatura': '2023-08-04',
  'ordem_inicio': '2023-09-04',
  'vigencia_contrato': '2026-02-20',
  'situacao_vigencia': 'vencido',
  'ultimas_medicoes': [
    {
      'id': 3,
      'data_medicao': '2024-03-01',
      'valor_medido': 120000.5,
      'percentual_executado': 31.98,
    },
    {
      'id': 2,
      'data_medicao': '2024-01-15',
      'valor_medido': 117850.74,
      'percentual_executado': null,
    },
  ],
};

void main() {
  group('Contrato (listagem)', () {
    test('lê o item da listagem com obra e empresa resumidas', () {
      final c = Contrato.fromJson({
        'id': 7,
        'numero_contrato_ano': '012/2026',
        'processo_licitacao': '500/2025',
        'obra': {'id': 41, 'descricao': 'Pavimentação Rua ABC'},
        'empresa': {
          'id': 3,
          'razao_social': 'Construtora XYZ LTDA',
          'nome_fantasia': null,
        },
        'valor_contrato': 2500000,
        'valor_medido': 1450000,
        'saldo': 1050000,
        'percentual_executado': 58,
        'data_assinatura': null,
        'ordem_inicio': null,
        'vigencia_contrato': '2026-12-31',
        'situacao_vigencia': 'vigente',
      });

      expect(c.titulo, 'Contrato 012/2026');
      expect(c.obra?.descricao, 'Pavimentação Rua ABC');
      expect(c.obra?.status, isNull);
      expect(c.empresa?.nomeExibicao, 'Construtora XYZ LTDA');
      expect(c.valorContrato, 2500000.0);
      expect(c.percentualExecutado, 58.0);
      expect(c.vigenciaContrato, DateTime(2026, 12, 31));
      expect(c.situacao, SituacaoVigencia.vigente);
    });

    test('valores nulos/ausentes viram zero e textos ausentes não quebram', () {
      final c = Contrato.fromJson({
        'id': 8,
        'numero_contrato_ano': null,
        'obra': null,
        'empresa': null,
        'valor_contrato': null,
        'situacao_vigencia': 'sem_vigencia',
      });

      expect(c.titulo, 'Contrato sem número');
      expect(c.obra, isNull);
      expect(c.empresa, isNull);
      expect(c.valorContrato, 0.0);
      expect(c.valorMedido, 0.0);
      expect(c.saldo, 0.0);
      expect(c.percentualExecutado, 0.0);
      expect(c.vigenciaContrato, isNull);
      expect(c.situacao, SituacaoVigencia.semVigencia);
    });
  });

  group('SituacaoVigencia', () {
    test('mapeia os valores da API e usa sem_vigencia como padrão', () {
      expect(SituacaoVigencia.fromApi('vencido'), SituacaoVigencia.vencido);
      expect(
        SituacaoVigencia.fromApi('vence_em_breve'),
        SituacaoVigencia.venceEmBreve,
      );
      expect(SituacaoVigencia.fromApi('vigente'), SituacaoVigencia.vigente);
      expect(SituacaoVigencia.fromApi('xyz'), SituacaoVigencia.semVigencia);
      expect(SituacaoVigencia.fromApi(null), SituacaoVigencia.semVigencia);
    });

    test('valores enviados no filtro batem com os aceitos pela API', () {
      expect(SituacaoVigencia.values.map((s) => s.api), [
        'vigente',
        'vence_em_breve',
        'vencido',
        'sem_vigencia',
      ]);
      expect(SituacaoVigencia.vencido.requerAtencao, isTrue);
      expect(SituacaoVigencia.venceEmBreve.requerAtencao, isTrue);
      expect(SituacaoVigencia.vigente.requerAtencao, isFalse);
    });
  });

  group('ContratosPagina (paginação)', () {
    test('lê data + meta do paginador do Laravel', () {
      final p = ContratosPagina.fromJson({
        'data': [
          {'id': 1, 'situacao_vigencia': 'vencido'},
          {'id': 2, 'situacao_vigencia': 'vigente'},
        ],
        'links': {'next': '...'},
        'meta': {'current_page': 1, 'last_page': 2, 'total': 15},
      });

      expect(p.contratos, hasLength(2));
      expect(p.total, 15);
      expect(p.hasMore, isTrue);
    });

    test('última página não tem mais itens', () {
      final p = ContratosPagina.fromJson({
        'data': [],
        'meta': {'current_page': 1, 'last_page': 1, 'total': 0},
      });
      expect(p.hasMore, isFalse);
      expect(p.contratos, isEmpty);
    });
  });

  group('ContratoDetalhe', () {
    test('lê obra completa, empresa, vigência e últimas medições', () {
      final d = ContratoDetalhe.fromJson(_detalheJson());
      final c = d.contrato;

      expect(c.obra?.status?.nome, 'Paralisada');
      expect(c.obra?.endereco, isNull);
      expect(c.empresa?.razaoSocial, startsWith('VIAPRECISA'));
      expect(c.empresa?.nomeExibicao, 'VIA PRECISA');
      expect(c.dataAssinatura, DateTime(2023, 8, 4));
      expect(c.situacao, SituacaoVigencia.vencido);
      expect(c.saldo, 505947.43);
      expect(d.ultimasMedicoes, hasLength(2));
      expect(d.ultimasMedicoes.first.dataMedicao, DateTime(2024, 3, 1));
      expect(d.ultimasMedicoes.first.valorMedido, 120000.5);
      expect(d.ultimasMedicoes.last.percentualExecutado, isNull);
    });

    test('sem medições', () {
      final json = _detalheJson()..['ultimas_medicoes'] = [];
      expect(ContratoDetalhe.fromJson(json).ultimasMedicoes, isEmpty);
    });
  });

  group('UI', () {
    Future<void> montar(WidgetTester tester, Widget child) =>
        tester.pumpWidget(MaterialApp(home: Scaffold(body: child)));

    testWidgets('percentual acima de 100 mostra valor real e não quebra', (
      tester,
    ) async {
      await montar(tester, const BarraPercentual(percentual: 132.5));

      expect(find.text('132,5%'), findsOneWidget);
      final barra = tester.widget<LinearProgressIndicator>(
        find.byType(LinearProgressIndicator),
      );
      expect(barra.value, 1.0); // limitada a 100%
      expect(tester.takeException(), isNull);
    });

    testWidgets('chip mostra o rótulo de cada situação', (tester) async {
      for (final s in SituacaoVigencia.values) {
        await montar(tester, SituacaoChip(situacao: s));
        expect(find.text(s.label), findsOneWidget);
      }
    });
  });
}
