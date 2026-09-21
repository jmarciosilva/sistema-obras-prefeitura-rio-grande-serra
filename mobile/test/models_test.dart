import 'package:flutter_test/flutter_test.dart';
import 'package:obras_rgs/features/auth/models/login_response.dart';
import 'package:obras_rgs/features/dashboard/models/dashboard.dart';
import 'package:obras_rgs/features/obras/models/obra.dart';
import 'package:obras_rgs/features/obras/models/obra_detalhe.dart';

/// Fixtures copiadas de respostas reais da API local (/api/v1).
void main() {
  group('LoginResponse', () {
    test('lê token, validade e usuário', () {
      final r = LoginResponse.fromJson({
        'token': '3|Cjdqee',
        'token_type': 'Bearer',
        'expires_at': '2026-10-21T20:18:07+00:00',
        'user': {
          'id': 22,
          'name': 'Smoke API',
          'email': 'smoke-api@local.test',
          'perfil': 'secretario',
        },
      });

      expect(r.token, '3|Cjdqee');
      expect(r.expiresAt, DateTime.utc(2026, 10, 21, 20, 18, 7));
      expect(r.user.id, 22);
      expect(r.user.perfil, 'secretario');
      expect(r.user.perfilLabel, 'Secretário de Obras');
    });

    test('resposta sem token é rejeitada', () {
      expect(
        () => LoginResponse.fromJson({'user': <String, dynamic>{}}),
        throwsFormatException,
      );
    });
  });

  group('Dashboard', () {
    test('lê o contrato real de GET /dashboard', () {
      final d = Dashboard.fromJson({
        'obras': {
          'total': 23,
          'em_execucao': 10,
          'concluidas': 1,
          'valor_contratado': 54187185.56,
          'valor_medido': 10376920.8,
          'saldo': 43810264.76,
          'percentual_executado': 19.15,
        },
        'status': [
          {'id': 1, 'nome': 'Em Planejamento', 'cor': '#6c757d', 'total': 5},
          {'id': 3, 'nome': 'Em Execução', 'cor': '#0d6efd', 'total': 10},
        ],
        'alertas': {
          'contratos_vencidos': 9,
          'contratos_vencendo': 0,
          'obras_sem_medicao': 7,
        },
        'atualizado_em': '2026-09-21T20:18:08+00:00',
      });

      expect(d.obras.total, 23);
      expect(d.obras.emExecucao, 10);
      expect(d.obras.valorContratado, 54187185.56);
      expect(d.obras.percentualExecutado, 19.15);
      expect(d.status, hasLength(2));
      expect(d.status[1].nome, 'Em Execução');
      expect(d.status[1].total, 10);
      expect(d.alertas.contratosVencidos, 9);
      expect(d.alertas.total, 16);
      expect(d.atualizadoEm, isNotNull);
    });

    test('valores inteiros, nulos ou ausentes viram zero (nunca null)', () {
      final d = Dashboard.fromJson({
        'obras': {'total': 0, 'valor_contratado': 0, 'valor_medido': null},
        'status': [],
      });

      expect(d.obras.valorContratado, 0.0);
      expect(d.obras.valorMedido, 0.0);
      expect(d.obras.percentualExecutado, 0.0);
      expect(d.status, isEmpty);
      expect(d.alertas.total, 0);
      expect(d.atualizadoEm, isNull);
    });
  });

  group('ObrasPage (paginação)', () {
    test('lê data + meta do paginador do Laravel', () {
      final p = ObrasPage.fromJson({
        'data': [
          {
            'id': 110,
            'descricao': 'Pavimentação Rua Daniela',
            'endereco': 'Jardim Guiomar',
            'status': {'id': 1, 'nome': 'Em Planejamento', 'cor': '#6c757d'},
            'valor_contratado': 0,
            'valor_medido': 0,
            'saldo': 0,
            'percentual_executado': 0,
          },
          {
            'id': 37,
            'descricao': 'Reforma do Centro Médico',
            'endereco': null,
            'status': null,
            'valor_contratado': 814885,
            'valor_medido': 802603.21,
            'saldo': 12281.79,
            'percentual_executado': 98.49,
          },
        ],
        'links': {'first': '...', 'last': '...', 'prev': null, 'next': '...'},
        'meta': {
          'current_page': 1,
          'from': 1,
          'last_page': 2,
          'per_page': 15,
          'to': 15,
          'total': 23,
        },
      });

      expect(p.obras, hasLength(2));
      expect(p.currentPage, 1);
      expect(p.lastPage, 2);
      expect(p.total, 23);
      expect(p.hasMore, isTrue);
      expect(p.obras[0].status?.nome, 'Em Planejamento');
      expect(p.obras[1].status, isNull);
      expect(p.obras[1].endereco, isNull);
      expect(p.obras[1].valorContratado, 814885.0);
      expect(p.obras[1].percentualExecutado, 98.49);
    });

    test('última página não tem mais itens', () {
      final p = ObrasPage.fromJson({
        'data': [],
        'meta': {'current_page': 2, 'last_page': 2, 'total': 23},
      });
      expect(p.hasMore, isFalse);
      expect(p.obras, isEmpty);
    });
  });

  group('ObraDetalhe', () {
    test('lê contratos, empresa e últimas medições', () {
      final d = ObraDetalhe.fromJson({
        'id': 37,
        'descricao': 'Reforma do Centro Médico',
        'endereco': 'Rua Venâncio Orsini, 162 - Centro',
        'status': {
          'id': 7,
          'nome': 'Conclusão Circunstanciada',
          'cor': '#d63384',
        },
        'valor_contratado': 814885,
        'valor_medido': 802603.21,
        'saldo': 12281.79,
        'percentual_executado': 98.49,
        'processo_execucao': '1602/2024',
        'contratos': [
          {
            'id': 100,
            'numero_contrato': '32/2024',
            'processo_licitacao': '531/2024',
            'empresa': {'id': 8, 'nome': 'LMM Engenharia', 'cnpj': '00.000'},
            'data_assinatura': '2024-08-20',
            'ordem_inicio': null,
            'vigencia_contrato': '2025-08-15',
            'vencido': true,
            'valor_contrato': 814885,
            'valor_medido': 802603.21,
          },
        ],
        'ultimas_medicoes': [
          {
            'id': 10,
            'contrato_id': 100,
            'numero_contrato': '32/2024',
            'data_medicao': '2025-09-23',
            'valor_medido': 15130.77,
            'percentual_executado': null,
          },
        ],
      });

      expect(d.obra.id, 37);
      expect(d.processoExecucao, '1602/2024');
      final c = d.contratos.single;
      expect(c.empresa?.nome, 'LMM Engenharia');
      expect(c.vencido, isTrue);
      expect(c.ordemInicio, isNull);
      expect(c.vigenciaContrato, DateTime(2025, 8, 15));
      final m = d.ultimasMedicoes.single;
      expect(m.dataMedicao, DateTime(2025, 9, 23));
      expect(m.valorMedido, 15130.77);
      expect(m.percentualExecutado, isNull);
    });

    test('obra sem contratos nem medições', () {
      final d = ObraDetalhe.fromJson({
        'id': 110,
        'descricao': 'Obra nova',
        'contratos': [],
        'ultimas_medicoes': [],
      });
      expect(d.contratos, isEmpty);
      expect(d.ultimasMedicoes, isEmpty);
      expect(d.obra.saldo, 0.0);
    });
  });
}
