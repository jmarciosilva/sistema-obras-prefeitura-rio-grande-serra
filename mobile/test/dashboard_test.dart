import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:obras_rgs/core/api/api_client.dart';
import 'package:obras_rgs/features/contratos/data/contratos_api.dart';
import 'package:obras_rgs/features/contratos/models/contrato.dart';
import 'package:obras_rgs/features/dashboard/data/dashboard_api.dart';
import 'package:obras_rgs/features/dashboard/models/dashboard.dart';
import 'package:obras_rgs/features/dashboard/presentation/dashboard_page.dart';
import 'package:obras_rgs/features/obras/data/obras_api.dart';
import 'package:obras_rgs/features/contratos/presentation/contratos_page.dart';
import 'package:obras_rgs/features/obras/models/obra.dart' as modelo;
import 'package:obras_rgs/features/obras/presentation/obras_page.dart';

import 'fakes.dart';

/// Registram o filtro pedido pelas listas, sem rede.
class _ContratosApiEspiao extends ContratosApi {
  _ContratosApiEspiao() : super(ApiClient(storage: FakeSessionStorage()));

  final List<SituacaoVigencia?> filtros = [];
  Map<String, dynamic> pagina = const {'data': []};

  @override
  Future<ContratosPagina> listar({
    int page = 1,
    String? search,
    SituacaoVigencia? situacao,
  }) async {
    filtros.add(situacao);
    return ContratosPagina.fromJson(pagina);
  }
}

class _ObrasApiEspiao extends ObrasApi {
  _ObrasApiEspiao() : super(ApiClient(storage: FakeSessionStorage()));

  final List<int?> filtros = [];
  Map<String, dynamic> pagina = const {'data': []};

  @override
  Future<modelo.ObrasPage> listar({
    int page = 1,
    String? search,
    int? statusId,
  }) async {
    filtros.add(statusId);
    return modelo.ObrasPage.fromJson(pagina);
  }
}

Dashboard _dashboard({Map<String, dynamic>? alertas}) => Dashboard.fromJson({
  'obras': {'total': 23, 'em_execucao': 10, 'concluidas': 1},
  'contratos': {
    'total': 30,
    'vigentes': 15,
    'vence_em_breve': 2,
    'vencidos': 9,
    'sem_vigencia': 4,
    'valor_contratado': 1000,
    'valor_medido': 250,
    'saldo': 750,
    'percentual_executado': 25,
  },
  'status': [
    {'id': 3, 'nome': 'Em Execução', 'cor': '#0d6efd', 'total': 10},
    {'id': 5, 'nome': 'Concluída', 'cor': '#198754', 'total': 0},
    {
      'id': 7,
      'nome': 'Conclusão Circunstanciada',
      'cor': '#d63384',
      'total': 1,
    },
  ],
  'alertas':
      alertas ??
      {
        'contratos_vencidos': 9,
        'contratos_vencendo': 2,
        'obras_sem_medicao': 7,
      },
});

void main() {
  late _ContratosApiEspiao contratosApi;
  late _ObrasApiEspiao obrasApi;

  Future<void> abrir(WidgetTester tester, Dashboard dashboard) async {
    contratosApi = _ContratosApiEspiao();
    obrasApi = _ObrasApiEspiao();
    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          dashboardProvider.overrideWith((ref) async => dashboard),
          contratosApiProvider.overrideWithValue(contratosApi),
          obrasApiProvider.overrideWithValue(obrasApi),
        ],
        child: const MaterialApp(home: Scaffold(body: DashboardPage())),
      ),
    );
    await tester.pumpAndSettle();
  }

  Finder kpiContrato(String rotulo) => find.descendant(
    of: find.byKey(const Key('dashboard-contratos')),
    matching: find.text(rotulo),
  );

  Future<void> voltar(WidgetTester tester) async {
    await tester.pageBack();
    await tester.pumpAndSettle();
  }

  testWidgets('mostra as seções do painel na ordem executiva', (tester) async {
    await abrir(tester, _dashboard());

    expect(find.text('Painel Executivo'), findsOneWidget);
    expect(find.text('RESUMO GERAL'), findsOneWidget);
    expect(find.text('Total de contratos'), findsOneWidget);

    // Rolagem só para baixo: se achar cada seção em sequência, a ordem está certa
    for (final secao in [
      'EXECUÇÃO FINANCEIRA',
      'OBRAS',
      'CONTRATOS',
      'PONTOS DE ATENÇÃO',
    ]) {
      await tester.scrollUntilVisible(find.text(secao), 200);
      expect(find.text(secao), findsOneWidget);
    }
    await tester.scrollUntilVisible(find.text('Contratos vencidos'), 200);
    expect(find.text('Execução financeira dos contratos'), findsOneWidget);
    expect(find.text('4 sem vigência informada'), findsOneWidget);
    expect(find.text('Obras sem medição há 60 dias'), findsOneWidget);
    expect(find.text('Nenhum ponto de atenção no momento.'), findsNothing);
  });

  testWidgets('KPIs de contratos abrem a lista com o filtro correspondente', (
    tester,
  ) async {
    await abrir(tester, _dashboard());
    await tester.scrollUntilVisible(kpiContrato('Vencidos'), 300);

    final casos = {
      'Total': null,
      'Vigentes': SituacaoVigencia.vigente,
      'Vencendo': SituacaoVigencia.venceEmBreve,
      'Vencidos': SituacaoVigencia.vencido,
    };
    for (final MapEntry(key: rotulo, value: situacao) in casos.entries) {
      await tester.tap(kpiContrato(rotulo));
      await tester.pumpAndSettle();
      expect(contratosApi.filtros.last, situacao, reason: rotulo);
      await voltar(tester);
    }
    expect(contratosApi.filtros, hasLength(4));
  });

  testWidgets('KPI "Obras em execução" abre Obras filtrada pelo status', (
    tester,
  ) async {
    await abrir(tester, _dashboard());

    await tester.tap(find.text('Obras em execução'));
    await tester.pumpAndSettle();
    expect(obrasApi.filtros.last, 3);
    await voltar(tester);

    // "Conclu" casa com dois status → sem filtro equivalente, não navega
    await tester.tap(find.text('Obras concluídas'));
    await tester.pumpAndSettle();
    expect(obrasApi.filtros, hasLength(1));
  });

  testWidgets('sem pendências: pontos de atenção neutros', (tester) async {
    await abrir(
      tester,
      _dashboard(
        alertas: {
          'contratos_vencidos': 0,
          'contratos_vencendo': 0,
          'obras_sem_medicao': 0,
        },
      ),
    );
    await tester.scrollUntilVisible(find.text('Contratos vencidos'), 300);

    expect(find.text('Nenhum ponto de atenção no momento.'), findsOneWidget);
    expect(find.text('Nenhum'), findsNWidgets(3));
    // Sem ocorrências, a linha não navega
    await tester.tap(find.text('Contratos vencidos'));
    await tester.pumpAndSettle();
    expect(contratosApi.filtros, isEmpty);
  });

  group('responsividade (sem overflow)', () {
    const textoLongo =
        'CT Nº 45/2024 - CONSTRUÇÃO DO TERMINAL RODOVIÁRIO MUNICIPAL NO '
        'MUNICÍPIO DE RIO GRANDE DA SERRA - ETAPA 2';

    Future<void> montar(
      WidgetTester tester,
      Widget tela, {
      required double largura,
      required double escala,
    }) async {
      tester.view.physicalSize = Size(largura, 800);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.reset);

      contratosApi = _ContratosApiEspiao()
        ..pagina = {
          'data': [
            {
              'id': 1,
              'numero_contrato_ano': '045/2024',
              'obra': {'id': 1, 'descricao': textoLongo},
              'empresa': {
                'id': 1,
                'razao_social': 'VITÓRIA SERVIÇOS OPERACIONAIS E CONSTRUÇÕES',
              },
              'valor_contrato': 13959514.99,
              'valor_medido': 11440109.47,
              'percentual_executado': 132.5,
              'vigencia_contrato': '2025-12-27',
              'situacao_vigencia': 'vence_em_breve',
            },
          ],
          'meta': {'current_page': 1, 'last_page': 1, 'total': 1},
        };
      obrasApi = _ObrasApiEspiao()
        ..pagina = {
          'data': [
            {
              'id': 1,
              'descricao': textoLongo,
              'endereco': 'Avenida Dom Pedro I, s/n — Centro',
              'status': {
                'id': 8,
                'nome': 'Encerramento contratual com aplicação de penalidade',
                'cor': '#6f42c1',
              },
              'valor_contratado': 13959514.99,
              'valor_medido': 11440109.47,
              'percentual_executado': 81.95,
            },
          ],
          'meta': {'current_page': 1, 'last_page': 1, 'total': 1},
        };

      await tester.pumpWidget(
        ProviderScope(
          overrides: [
            dashboardProvider.overrideWith((ref) async => _dashboard()),
            contratosApiProvider.overrideWithValue(contratosApi),
            obrasApiProvider.overrideWithValue(obrasApi),
          ],
          child: MaterialApp(
            home: MediaQuery(
              data: MediaQueryData(
                size: Size(largura, 800),
                textScaler: TextScaler.linear(escala),
              ),
              child: Scaffold(body: tela),
            ),
          ),
        ),
      );
      await tester.pumpAndSettle();
    }

    for (final largura in [360.0, 390.0, 412.0]) {
      for (final escala in [1.0, 1.3]) {
        testWidgets('largura $largura, fonte ${escala}x', (tester) async {
          await montar(
            tester,
            const DashboardPage(),
            largura: largura,
            escala: escala,
          );
          await tester.scrollUntilVisible(find.text('Contratos vencidos'), 300);
          await montar(
            tester,
            const ObrasPage(),
            largura: largura,
            escala: escala,
          );
          expect(find.text(textoLongo), findsOneWidget);
          await montar(
            tester,
            const ContratosPage(),
            largura: largura,
            escala: escala,
          );
          expect(find.text('Contrato 045/2024'), findsOneWidget);
          expect(tester.takeException(), isNull);
        });
      }
    }
  });
}
