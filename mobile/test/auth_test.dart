import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:obras_rgs/core/api/api_client.dart';
import 'package:obras_rgs/core/api/api_exception.dart';
import 'package:obras_rgs/core/auth/auth_state.dart';
import 'package:obras_rgs/core/cache/cache_repository.dart';
import 'package:obras_rgs/core/cache/sync_state.dart';
import 'package:obras_rgs/core/providers.dart';

import 'fakes.dart';

void main() {
  late FakeSessionStorage storage;
  late FakeAuthApi api;
  late CacheRepository cache;
  late ProviderContainer container;

  setUp(() {
    storage = FakeSessionStorage();
    api = FakeAuthApi();
    cache = cacheDeTeste();
    container = ProviderContainer(
      overrides: [
        sessionStorageProvider.overrideWithValue(storage),
        authApiProvider.overrideWithValue(api),
        cacheRepositoryProvider.overrideWithValue(cache),
      ],
    );
  });

  /// Sessão anterior válida: token, usuário salvo e cache do dashboard.
  Future<void> sessaoAnterior({DateTime? expiraEm}) async {
    storage
      ..token = '1|abc'
      ..expiresAt = expiraEm ?? DateTime.now().add(const Duration(days: 10))
      ..usuario = usuarioTeste;
    await cache.salvar(usuarioTeste.id, 'dashboard', {'obras': {}});
  }

  SyncState sync() => container.read(syncProvider);

  tearDown(() => container.dispose());

  /// Aguarda [condicao] (a limpeza usa SQLite real, com E/S assíncrona).
  Future<void> esperar(Future<bool> Function() condicao) async {
    for (var i = 0; i < 200 && !await condicao(); i++) {
      await Future<void>.delayed(const Duration(milliseconds: 5));
    }
  }

  Future<AuthState> restaurar() async {
    // Como no app: o build agenda o restore; aguarda ele terminar.
    container.read(authProvider);
    await esperar(() async => container.read(authProvider) is! AuthChecking);
    return container.read(authProvider);
  }

  group('abertura do app', () {
    test('sem token → login, sem chamar a API', () async {
      expect(await restaurar(), isA<Unauthenticated>());
      expect(api.chamadasMe, 0);
    });

    test(
      'token válido + /me online → entra normal e salva o usuário',
      () async {
        storage.token = '1|abc';
        final estado = await restaurar();
        expect(estado, isA<Authenticated>());
        expect((estado as Authenticated).user.email, 'secretario@exemplo.com');
        expect(api.chamadasMe, 1);
        expect(storage.usuario?.id, usuarioTeste.id);
        expect(sync().offline, isFalse);
      },
    );

    test(
      'token válido + sem internet + usuário salvo → entra offline',
      () async {
        await sessaoAnterior();
        api.erroMe = erroRede;

        final estado = await restaurar();

        expect(estado, isA<Authenticated>());
        expect((estado as Authenticated).user.id, usuarioTeste.id);
        expect(sync().offline, isTrue);
        expect(storage.token, '1|abc');
        expect(await cache.ler(usuarioTeste.id, 'dashboard'), isNotNull);
      },
    );

    test('servidor fora do ar (5xx) + usuário salvo → entra offline', () async {
      await sessaoAnterior();
      api.erroMe = ApiException.fromStatus(503);
      expect(await restaurar(), isA<Authenticated>());
      expect(sync().offline, isTrue);
    });

    test('/me 401 → limpa token, usuário e cache e vai ao login', () async {
      await sessaoAnterior();
      api.erroMe = ApiException.fromStatus(401);

      final estado = await restaurar();

      expect(estado, isA<Unauthenticated>());
      expect((estado as Unauthenticated).message, isNotNull);
      expect(storage.token, isNull);
      expect(storage.usuario, isNull);
      expect(await cache.ler(usuarioTeste.id, 'dashboard'), isNull);
    });

    test(
      'token vencido → não entra offline, limpa tudo, sem chamar a API',
      () async {
        await sessaoAnterior(
          expiraEm: DateTime.now().subtract(const Duration(minutes: 1)),
        );
        api.erroMe = erroRede;

        expect(await restaurar(), isA<Unauthenticated>());
        expect(api.chamadasMe, 0);
        expect(storage.token, isNull);
        expect(storage.usuario, isNull);
        expect(await cache.ler(usuarioTeste.id, 'dashboard'), isNull);
      },
    );

    test(
      'sem conexão e sem usuário salvo → tela de tentar novamente',
      () async {
        storage.token = '1|abc';
        api.erroMe = erroRede;
        final estado = await restaurar();
        expect(estado, isA<AuthCheckFailed>());
        expect(storage.token, '1|abc');
      },
    );
  });

  group('login e logout', () {
    test('login salva token, validade e usuário (nunca a senha)', () async {
      await restaurar();
      await container.read(authProvider.notifier).login('a@b.com', 'x');
      expect(container.read(authProvider), isA<Authenticated>());
      expect(storage.token, '5|token-de-teste');
      expect(storage.expiresAt, isNotNull);
      expect(storage.usuario?.email, 'secretario@exemplo.com');
    });

    test('login apaga cache de outro usuário que tenha ficado', () async {
      await cache.salvar(99, 'dashboard', {'de': 'outro usuário'});
      await restaurar();
      await container.read(authProvider.notifier).login('a@b.com', 'x');
      expect(await cache.ler(99, 'dashboard'), isNull);
    });

    test('login inválido propaga o erro e não salva token', () async {
      await restaurar();
      api.erroLogin = ApiException.fromStatus(401);
      await expectLater(
        container.read(authProvider.notifier).login('a@b.com', 'errada'),
        throwsA(isA<ApiException>()),
      );
      expect(storage.token, isNull);
      expect(container.read(authProvider), isA<Unauthenticated>());
    });

    test(
      'logout sem conexão ainda limpa sessão, usuário, cache e sync',
      () async {
        await sessaoAnterior();
        api.erroMe = erroRede;
        await restaurar(); // entra offline
        api.erroLogout = erroRede;

        await container.read(authProvider.notifier).logout();

        expect(api.chamadasLogout, 1);
        expect(storage.token, isNull);
        expect(storage.usuario, isNull);
        expect(await cache.ler(usuarioTeste.id, 'dashboard'), isNull);
        expect(sync().offline, isFalse);
        expect(container.read(authProvider), isA<Unauthenticated>());
      },
    );

    test(
      '401 em qualquer consulta → sessão encerrada e cache apagado',
      () async {
        await sessaoAnterior();
        expect(await restaurar(), isA<Authenticated>());

        // O ApiClient apaga o token e avisa o AuthNotifier
        container.read(authProvider.notifier).sessionExpired();
        await esperar(
          () async => await cache.ler(usuarioTeste.id, 'dashboard') == null,
        );

        expect(container.read(authProvider), isA<Unauthenticated>());
        expect(await cache.ler(usuarioTeste.id, 'dashboard'), isNull);
        expect(storage.usuario, isNull);
      },
    );
  });

  group('ApiClient', () {
    test('anexa Bearer; 401 em rota protegida apaga token e avisa', () async {
      storage.token = '1|abc';
      var avisos = 0;
      final adapter = FakeAdapter(401, {'message': 'Unauthenticated.'});
      final client = ApiClient(
        storage: storage,
        onUnauthorized: () => avisos++,
        dio: Dio()..httpClientAdapter = adapter,
      );

      await expectLater(
        client.get('/dashboard'),
        throwsA(
          isA<ApiException>().having(
            (e) => e.type,
            'type',
            ApiErrorType.unauthorized,
          ),
        ),
      );
      expect(adapter.ultima?.headers['Authorization'], 'Bearer 1|abc');
      expect(adapter.ultima?.headers['Accept'], 'application/json');
      expect(storage.token, isNull);
      expect(avisos, 1);
    });

    test(
      '401 do /login (senha errada) não é tratado como sessão expirada',
      () async {
        var avisos = 0;
        final client = ApiClient(
          storage: storage,
          onUnauthorized: () => avisos++,
          dio: Dio()
            ..httpClientAdapter = FakeAdapter(401, {
              'message': 'Credenciais inválidas.',
            }),
        );

        await expectLater(
          client.post('/login', data: {'email': 'a@b.com', 'password': 'x'}),
          throwsA(
            isA<ApiException>().having((e) => e.statusCode, 'status', 401),
          ),
        );
        expect(avisos, 0);
      },
    );

    test('servidor fora do ar vira mensagem amigável', () async {
      final client = ApiClient(
        storage: storage,
        dio: Dio()..httpClientAdapter = OfflineAdapter(),
      );

      await expectLater(
        client.get('/me'),
        throwsA(
          isA<ApiException>()
              .having((e) => e.type, 'type', ApiErrorType.network)
              .having(
                (e) => e.message,
                'message',
                'Não foi possível conectar ao servidor.',
              ),
        ),
      );
    });
  });
}
