import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:obras_rgs/core/cache/consulta_offline.dart';
import 'package:obras_rgs/core/cache/sync_state.dart';
import 'package:obras_rgs/features/contratos/data/contratos_api.dart';
import 'package:obras_rgs/features/contratos/models/contrato.dart';
import 'package:obras_rgs/features/contratos/presentation/contratos_page.dart';
import 'package:obras_rgs/features/dashboard/data/dashboard_api.dart';
import 'package:obras_rgs/features/dashboard/models/dashboard.dart';
import 'package:obras_rgs/features/dashboard/presentation/dashboard_page.dart';
import 'package:obras_rgs/features/obras/data/obras_api.dart';
import 'package:obras_rgs/features/obras/models/obra.dart' hide ObrasPage;
import 'package:obras_rgs/features/obras/presentation/obras_page.dart';

// Providers com dados fixos: estes testes olham só a interface
// (o cache/SWR é testado em offline_test.dart).

final _quando = DateTime(2026, 9, 22, 8, 42);

class _DashboardFixo extends DashboardNotifier {
  _DashboardFixo(this.dashboard);
  final Dashboard dashboard;

  @override
  Future<Dados<Dashboard>> build() async =>
      Dados(dashboard, atualizadoEm: _quando);

  @override
  Future<bool> atualizar() async => true;
}

class _ObrasFixas extends ObrasNotifier {
  _ObrasFixas(this.obras);
  final List<Obra> obras;

  @override
  Future<Dados<List<Obra>>> build() async =>
      Dados(obras, atualizadoEm: _quando);
}

class _ContratosFixos extends ContratosNotifier {
  _ContratosFixos(this.contratos);
  final List<Contrato> contratos;

  @override
  Future<Dados<List<Contrato>>> build() async =>
      Dados(contratos, atualizadoEm: _quando);
}

class _SyncOffline extends SyncNotifier {
  @override
  SyncState build() => const SyncState(offline: true);
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

final _obras = [
  Obra.fromJson({
    'id': 1,
    'descricao': 'REFORMA DA ESCOLA MUNICIPAL',
    'status': {'id': 3, 'nome': 'Em Execução', 'cor': '#0d6efd'},
  }),
  Obra.fromJson({
    'id': 2,
    'descricao': 'Pavimentação da Rua das Flores',
    'status': {'id': 7, 'nome': 'Conclusão Circunstanciada', 'cor': '#d63384'},
  }),
];

final _contratos = [
  Contrato.fromJson({
    'id': 1,
    'numero_contrato_ano': '012/2026',
    'empresa': {'id': 1, 'razao_social': 'VZO ENGENHARIA LTDA'},
    'situacao_vigencia': 'vigente',
  }),
  Contrato.fromJson({
    'id': 2,
    'numero_contrato_ano': '032/2024',
    'empresa': {
      'id': 2,
      'razao_social': 'LMM Engenharia e Serviços',
      'nome_fantasia': 'Construções Ávila',
    },
    'situacao_vigencia': 'vencido',
  }),
];

List<Override> _overrides({
  Dashboard? dashboard,
  List<Obra>? obras,
  List<Contrato>? contratos,
  bool offline = false,
}) => [
  dashboardProvider.overrideWith(
    () => _DashboardFixo(dashboard ?? _dashboard()),
  ),
  obrasProvider.overrideWith(() => _ObrasFixas(obras ?? _obras)),
  contratosProvider.overrideWith(
    () => _ContratosFixos(contratos ?? _contratos),
  ),
  if (offline) syncProvider.overrideWith(_SyncOffline.new),
];

void main() {
  Future<void> abrir(
    WidgetTester tester, {
    Widget tela = const DashboardPage(),
    Dashboard? dashboard,
    bool offline = false,
  }) async {
    await tester.pumpWidget(
      ProviderScope(
        overrides: _overrides(dashboard: dashboard, offline: offline),
        child: MaterialApp(home: Scaffold(body: tela)),
      ),
    );
    await tester.pumpAndSettle();
  }

  Finder kpiContrato(String rotulo) => find.descendant(
    of: find.byKey(const Key('dashboard-contratos')),
    matching: find.text(rotulo),
  );

  bool chipSelecionado(WidgetTester tester, String rotulo) => tester
      .widget<ChoiceChip>(find.widgetWithText(ChoiceChip, rotulo))
      .selected;

  Future<void> voltar(WidgetTester tester) async {
    await tester.pageBack();
    await tester.pumpAndSettle();
  }

  testWidgets('mostra as seções do painel na ordem executiva', (tester) async {
    await abrir(tester);

    expect(find.text('Painel Executivo'), findsOneWidget);
    expect(find.text('RESUMO GERAL'), findsOneWidget);
    expect(find.text('Total de contratos'), findsOneWidget);
    expect(find.text('Modo offline'), findsNothing);

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

  testWidgets('offline: aviso discreto com o horário dos dados', (
    tester,
  ) async {
    await abrir(tester, offline: true);

    expect(find.text('Modo offline'), findsOneWidget);
    expect(
      find.textContaining('Dados atualizados em 22/09/2026 às 08:42'),
      findsOneWidget,
    );
    // O painel continua completo
    expect(find.text('Total de contratos'), findsOneWidget);
  });

  testWidgets('KPIs de contratos abrem a lista com o filtro correspondente', (
    tester,
  ) async {
    await abrir(tester);
    await tester.scrollUntilVisible(kpiContrato('Vencidos'), 300);

    const chipEsperado = {
      'Total': 'Todos',
      'Vigentes': 'Vigentes',
      'Vencendo': 'Vencendo',
      'Vencidos': 'Vencidos',
    };
    for (final MapEntry(key: kpi, value: chip) in chipEsperado.entries) {
      await tester.tap(kpiContrato(kpi));
      await tester.pumpAndSettle();
      expect(find.byType(ContratosPage), findsOneWidget, reason: kpi);
      expect(chipSelecionado(tester, chip), isTrue, reason: kpi);
      await voltar(tester);
    }

    // Filtro local: "Vencidos" mostra só o contrato vencido
    await tester.tap(kpiContrato('Vencidos'));
    await tester.pumpAndSettle();
    expect(find.text('Contrato 032/2024'), findsOneWidget);
    expect(find.text('Contrato 012/2026'), findsNothing);
  });

  testWidgets('KPI "Obras em execução" abre Obras filtrada pelo status', (
    tester,
  ) async {
    await abrir(tester);

    await tester.tap(find.text('Obras em execução'));
    await tester.pumpAndSettle();
    expect(chipSelecionado(tester, 'Em Execução (10)'), isTrue);
    expect(find.text('REFORMA DA ESCOLA MUNICIPAL'), findsOneWidget);
    expect(find.text('Pavimentação da Rua das Flores'), findsNothing);
    await voltar(tester);

    // "Conclu" casa com dois status → sem filtro equivalente, não navega
    await tester.tap(find.text('Obras concluídas'));
    await tester.pumpAndSettle();
    expect(find.byType(ObrasPage), findsNothing);
  });

  testWidgets('sem pendências: pontos de atenção neutros', (tester) async {
    await abrir(
      tester,
      dashboard: _dashboard(
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
    expect(find.byType(ContratosPage), findsNothing);
  });

  group('busca e filtro locais (funcionam offline)', () {
    testWidgets('obras: busca sem acento/maiúsculas e filtro de status', (
      tester,
    ) async {
      await abrir(tester, tela: const ObrasPage(), offline: true);
      expect(find.text('Modo offline'), findsOneWidget);
      expect(find.text('2 obras'), findsOneWidget);

      await tester.enterText(find.byType(TextField), 'pavimentacao');
      await tester.pump();
      expect(find.text('Pavimentação da Rua das Flores'), findsOneWidget);
      expect(find.text('REFORMA DA ESCOLA MUNICIPAL'), findsNothing);

      await tester.enterText(find.byType(TextField), '');
      await tester.tap(find.widgetWithText(ChoiceChip, 'Em Execução (10)'));
      await tester.pump();
      expect(find.text('1 obra'), findsOneWidget);
      expect(find.text('REFORMA DA ESCOLA MUNICIPAL'), findsOneWidget);
    });

    testWidgets('contratos: busca por número, empresa e nome fantasia', (
      tester,
    ) async {
      await abrir(tester, tela: const ContratosPage(), offline: true);

      for (final (busca, esperado) in [
        ('012/2026', 'Contrato 012/2026'),
        ('vzo', 'Contrato 012/2026'),
        ('avila', 'Contrato 032/2024'), // nome fantasia, sem acento
      ]) {
        await tester.enterText(find.byType(TextField), busca);
        await tester.pump();
        expect(find.text('1 contrato'), findsOneWidget, reason: busca);
        expect(find.text(esperado), findsOneWidget, reason: busca);
      }

      await tester.enterText(find.byType(TextField), 'nada disso');
      await tester.pump();
      expect(find.text('Nenhum contrato encontrado.'), findsOneWidget);
    });
  });

  group('responsividade (sem overflow)', () {
    const textoLongo =
        'CT Nº 45/2024 - CONSTRUÇÃO DO TERMINAL RODOVIÁRIO MUNICIPAL NO '
        'MUNICÍPIO DE RIO GRANDE DA SERRA - ETAPA 2';

    final obraLonga = Obra.fromJson({
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
    });
    final contratoLongo = Contrato.fromJson({
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
    });

    Future<void> montar(
      WidgetTester tester,
      Widget tela, {
      required double largura,
      required double escala,
    }) async {
      tester.view.physicalSize = Size(largura, 800);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.reset);

      await tester.pumpWidget(
        ProviderScope(
          // Offline: inclui o aviso na medição de espaço
          overrides: _overrides(
            obras: [obraLonga],
            contratos: [contratoLongo],
            offline: true,
          ),
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
