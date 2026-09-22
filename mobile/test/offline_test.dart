import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:obras_rgs/core/api/api_client.dart';
import 'package:obras_rgs/core/cache/cache_database.dart';
import 'package:obras_rgs/core/cache/cache_repository.dart';
import 'package:obras_rgs/core/cache/consulta_offline.dart';
import 'package:obras_rgs/core/cache/sync_state.dart';
import 'package:obras_rgs/features/contratos/data/contratos_api.dart';
import 'package:obras_rgs/features/contratos/presentation/contrato_detalhe_page.dart';
import 'package:obras_rgs/features/dashboard/data/dashboard_api.dart';
import 'package:obras_rgs/features/obras/data/obras_api.dart';
import 'package:obras_rgs/features/obras/presentation/obra_detalhe_page.dart';
import 'package:obras_rgs/shared/widgets/aviso_offline.dart';
import 'package:sqflite_common_ffi/sqflite_ffi.dart';

import 'fakes.dart';

// ── APIs falsas: online, sem rede ou 401, conforme o teste ───────────

ApiClient _clienteFalso() => ApiClient(storage: FakeSessionStorage());

class _DashboardApiFalsa extends DashboardApi {
  _DashboardApiFalsa() : super(_clienteFalso());

  Map<String, dynamic> resposta = {
    'obras': {'total': 23},
    'contratos': {'total': 15},
    'atualizado_em': '2026-09-22T08:42:00-03:00',
  };
  Object? erro;
  int chamadas = 0;

  @override
  Future<Map<String, dynamic>> carregar() async {
    chamadas++;
    if (erro != null) throw erro!;
    return resposta;
  }
}

class _ObrasApiFalsa extends ObrasApi {
  _ObrasApiFalsa() : super(_clienteFalso());

  Map<String, dynamic> lista = {
    'data': [
      {'id': 1, 'descricao': 'Reforma da Escola', 'percentual_executado': 40},
      {'id': 2, 'descricao': 'Pavimentação', 'percentual_executado': 10},
    ],
  };
  Map<int, Map<String, dynamic>> detalhes = {
    1: {
      'id': 1,
      'descricao': 'Reforma da Escola',
      'contratos': [
        {'id': 7, 'numero_contrato': '001/2026'},
      ],
      'ultimas_medicoes': [],
    },
  };
  Object? erro;
  int chamadasDetalhe = 0;

  @override
  Future<Map<String, dynamic>> listarTodas() async {
    if (erro != null) throw erro!;
    return lista;
  }

  @override
  Future<Map<String, dynamic>> detalhe(int id) async {
    chamadasDetalhe++;
    if (erro != null) throw erro!;
    return detalhes[id]!;
  }
}

class _ContratosApiFalsa extends ContratosApi {
  _ContratosApiFalsa() : super(_clienteFalso());

  Map<String, dynamic> lista = {
    'data': [
      {
        'id': 7,
        'numero_contrato_ano': '001/2026',
        'empresa': {'id': 3, 'razao_social': 'Construtora X LTDA'},
        'valor_contrato': 1000,
        'situacao_vigencia': 'vigente',
      },
    ],
  };
  Object? erro;

  @override
  Future<Map<String, dynamic>> listarTodos() async {
    if (erro != null) throw erro!;
    return lista;
  }

  @override
  Future<Map<String, dynamic>> detalhe(int id) async {
    if (erro != null) throw erro!;
    return {
      ...lista['data'][0] as Map<String, dynamic>,
      'ultimas_medicoes': [
        {'id': 1, 'data_medicao': '2026-06-10', 'valor_medido': 400},
      ],
    };
  }
}

/// Responde `/obras` e `/contratos` paginados (3 páginas), como o Laravel.
class _PaginasAdapter implements HttpClientAdapter {
  final paginasPedidas = <Object?>[];
  final porPagina = <Object?>[];

  @override
  Future<ResponseBody> fetch(
    RequestOptions options,
    Stream<List<int>>? requestStream,
    Future<void>? cancelFuture,
  ) async {
    final pagina = options.queryParameters['page'] as int;
    paginasPedidas.add(pagina);
    porPagina.add(options.queryParameters['per_page']);
    return ResponseBody.fromString(
      '{"data":[{"id":${pagina * 10}},{"id":${pagina * 10 + 1}}],'
      '"meta":{"current_page":$pagina,"last_page":3}}',
      200,
      headers: {
        Headers.contentTypeHeader: [Headers.jsonContentType],
      },
    );
  }

  @override
  void close({bool force = false}) {}
}

void main() {
  late CacheRepository cache;
  late _DashboardApiFalsa dashboardApi;
  late _ObrasApiFalsa obrasApi;
  late _ContratosApiFalsa contratosApi;

  setUp(() {
    cache = cacheDeTeste();
    dashboardApi = _DashboardApiFalsa();
    obrasApi = _ObrasApiFalsa();
    contratosApi = _ContratosApiFalsa();
  });

  List<Override> overrides({int usuario = 12}) => [
    usuarioIdProvider.overrideWithValue(usuario),
    cacheRepositoryProvider.overrideWithValue(cache),
    dashboardApiProvider.overrideWithValue(dashboardApi),
    obrasApiProvider.overrideWithValue(obrasApi),
    contratosApiProvider.overrideWithValue(contratosApi),
  ];

  ProviderContainer container({int usuario = 12}) {
    final c = ProviderContainer(overrides: overrides(usuario: usuario));
    addTearDown(c.dispose);
    return c;
  }

  /// Widget tests usam relógio falso; o SQLite (FFI) responde pelo event
  /// loop real. Alterna esperas reais com pump até a tela assentar.
  Future<void> assentar(WidgetTester tester) async {
    for (var i = 0; i < 30; i++) {
      await tester.runAsync(
        () => Future<void>.delayed(const Duration(milliseconds: 20)),
      );
      await tester.pump();
    }
  }

  /// A revalidação em segundo plano e o SQLite são assíncronos.
  Future<void> esperar(bool Function() condicao) async {
    for (var i = 0; i < 200 && !condicao(); i++) {
      await Future<void>.delayed(const Duration(milliseconds: 5));
    }
  }

  group('CacheRepository (SQLite)', () {
    test('salva, lê e substitui (UNIQUE usuário + chave)', () async {
      await cache.salvar(12, 'dashboard', {'v': 1});
      await cache.salvar(12, 'dashboard', {'v': 2});

      final entrada = await cache.ler(12, 'dashboard');
      expect(entrada?.payload, {'v': 2});
      expect(entrada?.atualizadoEm, isNotNull);
    });

    test('cache separado por usuário; limpeza só do usuário', () async {
      await cache.salvar(12, 'dashboard', {'de': 'A'});
      await cache.salvar(13, 'dashboard', {'de': 'B'});

      expect((await cache.ler(12, 'dashboard'))?.payload, {'de': 'A'});
      expect((await cache.ler(13, 'dashboard'))?.payload, {'de': 'B'});

      await cache.limparUsuario(12);
      expect(await cache.ler(12, 'dashboard'), isNull);
      expect(await cache.ler(13, 'dashboard'), isNotNull);
    });

    test('versão incompatível do banco descarta o cache antigo', () async {
      sqfliteFfiInit();
      final caminho =
          '${(await databaseFactoryFfi.getDatabasesPath())}/versao_teste.db';
      await databaseFactoryFfi.deleteDatabase(caminho);
      // Banco "de outra versão" com dados
      final antigo = await CacheDatabase.abrir(
        factory: databaseFactoryFfi,
        caminho: caminho,
      );
      await antigo.insert(CacheDatabase.tabela, {
        'user_id': 12,
        'cache_key': 'dashboard',
        'payload': '{}',
        'updated_at': DateTime.now().toIso8601String(),
      });
      await antigo.setVersion(CacheDatabase.versao + 1);
      await antigo.close();

      final repo = CacheRepository(
        () =>
            CacheDatabase.abrir(factory: databaseFactoryFfi, caminho: caminho),
      );
      expect(await repo.ler(12, 'dashboard'), isNull);
      await repo.fechar();
      await databaseFactoryFfi.deleteDatabase(caminho);
    });
  });

  group('Dashboard', () {
    test('online sem cache → consulta a API e salva no cache', () async {
      final c = container();
      final dados = await c.read(dashboardProvider.future);

      expect(dados.valor.obras.total, 23);
      expect(dados.doCache, isFalse);
      expect((await cache.ler(12, 'dashboard'))?.payload['obras'], {
        'total': 23,
      });
      expect(c.read(syncProvider).offline, isFalse);
    });

    test(
      'offline com cache → mostra o cache e entra em modo offline',
      () async {
        await cache.salvar(12, 'dashboard', {
          'obras': {'total': 20},
        });
        dashboardApi.erro = erroRede;
        final c = container();

        final dados = await c.read(dashboardProvider.future);
        expect(dados.valor.obras.total, 20);
        expect(dados.doCache, isTrue);

        await esperar(() => c.read(syncProvider).offline);
        expect(c.read(syncProvider).offline, isTrue);
        expect(c.read(dashboardProvider).valueOrNull?.valor.obras.total, 20);
      },
    );

    test(
      'cache primeiro; a atualização online substitui tela e cache',
      () async {
        await cache.salvar(12, 'dashboard', {
          'obras': {'total': 20},
        });
        final c = container();

        // Mostra o cache na hora...
        expect((await c.read(dashboardProvider.future)).valor.obras.total, 20);
        // ...e depois a resposta da API
        await esperar(
          () => c.read(dashboardProvider).valueOrNull?.valor.obras.total == 23,
        );
        expect(c.read(dashboardProvider).valueOrNull?.doCache, isFalse);
        expect((await cache.ler(12, 'dashboard'))?.payload['obras'], {
          'total': 23,
        });
      },
    );

    test('pull-to-refresh offline mantém os dados e devolve false', () async {
      final c = container();
      await c.read(dashboardProvider.future);
      dashboardApi.erro = erroRede;

      expect(await c.read(dashboardProvider.notifier).atualizar(), isFalse);
      expect(c.read(dashboardProvider).valueOrNull?.valor.obras.total, 23);
      expect(c.read(syncProvider).offline, isTrue);

      // Voltou a conexão: atualiza e sai do modo offline
      dashboardApi.erro = null;
      expect(await c.read(dashboardProvider.notifier).atualizar(), isTrue);
      expect(c.read(syncProvider).offline, isFalse);
    });

    test('sem cache e sem rede → erro; volta sozinho com a conexão', () async {
      dashboardApi.erro = erroRede;
      final c = container();
      await expectLater(c.read(dashboardProvider.future), throwsA(anything));

      // Outra consulta à API funcionou (offline → online)
      dashboardApi.erro = null;
      c.read(syncProvider.notifier).sucesso();

      await esperar(
        () => c.read(dashboardProvider).valueOrNull?.valor.obras.total == 23,
      );
      expect(c.read(dashboardProvider).valueOrNull?.valor.obras.total, 23);
    });

    test('cache antigo em tela se atualiza quando a conexão volta', () async {
      await cache.salvar(12, 'dashboard', {
        'obras': {'total': 20},
      });
      dashboardApi.erro = erroRede;
      final c = container();
      await c.read(dashboardProvider.future);
      await esperar(() => c.read(syncProvider).offline);

      dashboardApi.erro = null;
      c.read(syncProvider.notifier).sucesso();

      await esperar(
        () => c.read(dashboardProvider).valueOrNull?.valor.obras.total == 23,
      );
      expect(c.read(dashboardProvider).valueOrNull?.doCache, isFalse);
    });

    test('outro usuário não enxerga o cache do anterior', () async {
      await cache.salvar(12, 'dashboard', {
        'obras': {'total': 20},
      });
      dashboardApi.erro = erroRede;

      final c = container(usuario: 13);
      await expectLater(c.read(dashboardProvider.future), throwsA(anything));
      expect(dashboardApi.chamadas, 1); // sem cache → tentou a API
    });
  });

  group('Obras e Contratos (lista completa)', () {
    test('ObrasApi percorre TODAS as páginas com per_page=50', () async {
      final adapter = _PaginasAdapter();
      final api = ObrasApi(
        ApiClient(
          storage: FakeSessionStorage(),
          dio: Dio()..httpClientAdapter = adapter,
        ),
      );

      final json = await api.listarTodas();

      expect(adapter.paginasPedidas, [1, 2, 3]);
      expect(adapter.porPagina.toSet(), {50});
      expect((json['data'] as List).length, 6);
    });

    test('ContratosApi percorre TODAS as páginas', () async {
      final adapter = _PaginasAdapter();
      final api = ContratosApi(
        ApiClient(
          storage: FakeSessionStorage(),
          dio: Dio()..httpClientAdapter = adapter,
        ),
      );
      expect(((await api.listarTodos())['data'] as List).length, 6);
      expect(adapter.paginasPedidas, [1, 2, 3]);
    });

    test('obras: sincroniza e, offline, devolve o cache', () async {
      await container().read(obrasProvider.future); // online: grava
      obrasApi.erro = erroRede;

      final offline = container();
      final dados = await offline.read(obrasProvider.future);
      expect(dados.valor.map((o) => o.descricao), [
        'Reforma da Escola',
        'Pavimentação',
      ]);
      expect(dados.doCache, isTrue);
    });

    test('contratos: sincroniza e, offline, devolve o cache', () async {
      await container().read(contratosProvider.future);
      contratosApi.erro = erroRede;

      final dados = await container().read(contratosProvider.future);
      expect(dados.valor.single.titulo, 'Contrato 001/2026');
      expect(dados.valor.single.empresa?.razaoSocial, 'Construtora X LTDA');
    });
  });

  group('Detalhes', () {
    test('detalhe visitado online funciona offline', () async {
      final online = container();
      await online.read(obraDetalheProvider(1).future);
      obrasApi.erro = erroRede;

      final dados = await container().read(obraDetalheProvider(1).future);
      expect(dados.valor.contratos.single.numeroContrato, '001/2026');
      expect(dados.parcial, isFalse);
    });

    test('nunca visitado + offline → resumo da lista (parcial)', () async {
      await container().read(obrasProvider.future); // só a lista
      obrasApi.erro = erroRede;

      final dados = await container().read(obraDetalheProvider(2).future);
      expect(dados.parcial, isTrue);
      expect(dados.valor.obra.descricao, 'Pavimentação');
    });

    test(
      'resumo parcial vira detalhe completo quando a conexão volta',
      () async {
        final c = container();
        await c.read(obrasProvider.future);
        obrasApi.erro = erroRede;
        final sub = c.listen(obraDetalheProvider(1), (_, _) {});
        addTearDown(sub.close);
        expect((await c.read(obraDetalheProvider(1).future)).parcial, isTrue);
        await esperar(() => c.read(syncProvider).offline);

        obrasApi.erro = null;
        c.read(syncProvider.notifier).sucesso();

        await esperar(
          () => c.read(obraDetalheProvider(1)).valueOrNull?.parcial == false,
        );
        expect(
          c
              .read(obraDetalheProvider(1))
              .value
              ?.valor
              .contratos
              .single
              .numeroContrato,
          '001/2026',
        );
      },
    );

    test('pull-to-refresh no resumo parcial faz UMA requisição', () async {
      final c = container();
      await c.read(obrasProvider.future);
      obrasApi.erro = erroRede;
      final sub = c.listen(obraDetalheProvider(1), (_, _) {});
      addTearDown(sub.close);
      await c.read(obraDetalheProvider(1).future);
      await esperar(() => c.read(syncProvider).offline);

      obrasApi
        ..erro = null
        ..chamadasDetalhe = 0;
      expect(await c.read(obraDetalheProvider(1).notifier).atualizar(), isTrue);
      await Future<void>.delayed(const Duration(milliseconds: 100));

      expect(obrasApi.chamadasDetalhe, 1);
      expect(c.read(obraDetalheProvider(1)).valueOrNull?.parcial, isFalse);
    });

    test('nunca visitado, fora da lista e offline → SemDadosOffline', () async {
      obrasApi.erro = erroRede;
      await expectLater(
        container().read(obraDetalheProvider(99).future),
        throwsA(isA<SemDadosOffline>()),
      );
    });

    testWidgets('tela: detalhe parcial avisa que não foi sincronizado', (
      tester,
    ) async {
      await tester.runAsync(() async {
        await container().read(contratosProvider.future);
      });
      contratosApi.erro = erroRede;

      await tester.pumpWidget(
        ProviderScope(
          overrides: overrides(),
          child: const MaterialApp(home: ContratoDetalhePage(id: 7)),
        ),
      );
      await assentar(tester);

      expect(
        find.textContaining('ainda não foram sincronizados'),
        findsOneWidget,
      );
      expect(find.text('Contrato 001/2026'), findsOneWidget);
      expect(find.text('Últimas medições'), findsNothing);
    });

    testWidgets('tela: sem nenhum dado → mensagem amigável, sem erro técnico', (
      tester,
    ) async {
      obrasApi.erro = erroRede;
      await tester.pumpWidget(
        ProviderScope(
          overrides: overrides(),
          child: const MaterialApp(home: ObraDetalhePage(id: 99)),
        ),
      );
      await assentar(tester);

      expect(find.text(SemDadosOffline.mensagem), findsOneWidget);
      expect(find.text('Tentar novamente'), findsOneWidget);
      expect(find.textContaining('Exception'), findsNothing);
    });
  });

  testWidgets('AvisoOffline: "Modo offline" + horário só quando offline', (
    tester,
  ) async {
    final c = ProviderContainer();
    addTearDown(c.dispose);
    final quando = DateTime(2026, 9, 22, 8, 42);

    await tester.pumpWidget(
      UncontrolledProviderScope(
        container: c,
        child: MaterialApp(
          home: Scaffold(body: AvisoOffline(atualizadoEm: quando)),
        ),
      ),
    );
    expect(find.text('Modo offline'), findsNothing);

    c.read(syncProvider.notifier).marcarOffline();
    await tester.pump();
    expect(find.text('Modo offline'), findsOneWidget);
    expect(
      find.textContaining('Dados atualizados em 22/09/2026 às 08:42'),
      findsOneWidget,
    );
  });
}
