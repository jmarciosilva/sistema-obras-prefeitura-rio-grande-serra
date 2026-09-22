import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:obras_rgs/core/api/api_client.dart';
import 'package:obras_rgs/features/contratos/data/contratos_api.dart';
import 'package:obras_rgs/features/contratos/models/contrato.dart';
import 'package:obras_rgs/features/dashboard/data/dashboard_api.dart';
import 'package:obras_rgs/features/dashboard/models/dashboard.dart';
import 'package:obras_rgs/features/dashboard/presentation/dashboard_page.dart';

import 'fakes.dart';

/// Registra o filtro pedido pela tela de Contratos, sem rede.
class _ContratosApiEspiao extends ContratosApi {
  _ContratosApiEspiao() : super(ApiClient(storage: FakeSessionStorage()));

  final List<SituacaoVigencia?> filtros = [];

  @override
  Future<ContratosPagina> listar({
    int page = 1,
    String? search,
    SituacaoVigencia? situacao,
  }) async {
    filtros.add(situacao);
    return ContratosPagina.fromJson(const {'data': []});
  }
}

void main() {
  final dashboard = Dashboard.fromJson({
    'obras': {'total': 23},
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
    'status': [],
    'alertas': {'contratos_vencidos': 9, 'contratos_vencendo': 2},
  });

  Future<_ContratosApiEspiao> abrir(WidgetTester tester) async {
    final api = _ContratosApiEspiao();
    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          dashboardProvider.overrideWith((ref) async => dashboard),
          contratosApiProvider.overrideWithValue(api),
        ],
        child: const MaterialApp(home: Scaffold(body: DashboardPage())),
      ),
    );
    await tester.pumpAndSettle();
    return api;
  }

  Finder kpi(String rotulo) => find.descendant(
    of: find.widgetWithText(Card, 'Contratos'),
    matching: find.text(rotulo),
  );

  testWidgets('mostra a seção de Contratos com KPIs e financeiro', (
    tester,
  ) async {
    await abrir(tester);
    await tester.scrollUntilVisible(
      find.text('Execução financeira dos contratos'),
      200,
    );

    expect(find.text('Contratos'), findsOneWidget);
    expect(kpi('Vigentes'), findsOneWidget);
    expect(kpi('Vencendo'), findsOneWidget);
    expect(kpi('Vencidos'), findsOneWidget);
    expect(kpi('30'), findsOneWidget);
    expect(kpi('15'), findsOneWidget);
    expect(find.text('Execução financeira dos contratos'), findsOneWidget);
    expect(find.text('4 sem vigência informada'), findsOneWidget);
    // Alertas continuam presentes
    await tester.scrollUntilVisible(find.text('Contratos vencidos'), 200);
    expect(find.text('Contratos vencidos'), findsOneWidget);
  });

  testWidgets('tocar nos KPIs abre Contratos com o filtro correspondente', (
    tester,
  ) async {
    final api = await abrir(tester);
    await tester.scrollUntilVisible(kpi('Vencidos'), 200);

    final casos = {
      'Total': null,
      'Vigentes': SituacaoVigencia.vigente,
      'Vencendo': SituacaoVigencia.venceEmBreve,
      'Vencidos': SituacaoVigencia.vencido,
    };
    for (final MapEntry(key: rotulo, value: situacao) in casos.entries) {
      await tester.tap(kpi(rotulo));
      await tester.pumpAndSettle();
      expect(api.filtros.last, situacao, reason: rotulo);
      await tester.pageBack();
      await tester.pumpAndSettle();
    }
    expect(api.filtros, hasLength(4));
  });
}
