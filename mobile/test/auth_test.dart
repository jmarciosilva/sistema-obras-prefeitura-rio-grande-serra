import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:obras_rgs/core/api/api_client.dart';
import 'package:obras_rgs/core/api/api_exception.dart';
import 'package:obras_rgs/core/auth/auth_state.dart';
import 'package:obras_rgs/core/providers.dart';

import 'fakes.dart';

void main() {
  late FakeSessionStorage storage;
  late FakeAuthApi api;
  late ProviderContainer container;

  setUp(() {
    storage = FakeSessionStorage();
    api = FakeAuthApi();
    container = ProviderContainer(
      overrides: [
        sessionStorageProvider.overrideWithValue(storage),
        authApiProvider.overrideWithValue(api),
      ],
    );
  });

  tearDown(() => container.dispose());

  Future<AuthState> restaurar() async {
    // Como no app: o build agenda o restore; aguarda ele terminar.
    container.read(authProvider);
    await pumpEventQueue();
    return container.read(authProvider);
  }

  group('abertura do app', () {
    test('sem token → login, sem chamar a API', () async {
      expect(await restaurar(), isA<Unauthenticated>());
      expect(api.chamadasMe, 0);
    });

    test('token válido → valida com /me e entra', () async {
      storage.token = '1|abc';
      final estado = await restaurar();
      expect(estado, isA<Authenticated>());
      expect((estado as Authenticated).user.email, 'secretario@exemplo.com');
      expect(api.chamadasMe, 1);
    });

    test('token rejeitado (401) → apaga token e vai ao login', () async {
      storage.token = '1|abc';
      api.erroMe = ApiException.fromStatus(401);
      final estado = await restaurar();
      expect(estado, isA<Unauthenticated>());
      expect((estado as Unauthenticated).message, isNotNull);
      expect(storage.token, isNull);
    });

    test('token vencido localmente → login sem chamar a API', () async {
      storage
        ..token = '1|abc'
        ..expiresAt = DateTime.now().subtract(const Duration(minutes: 1));
      expect(await restaurar(), isA<Unauthenticated>());
      expect(api.chamadasMe, 0);
      expect(storage.token, isNull);
    });

    test('sem conexão → mantém token e permite tentar novamente', () async {
      storage.token = '1|abc';
      api.erroMe = erroRede;
      final estado = await restaurar();
      expect(estado, isA<AuthCheckFailed>());
      expect(storage.token, '1|abc');
    });
  });

  group('login e logout', () {
    test('login salva token e validade', () async {
      await restaurar();
      await container.read(authProvider.notifier).login('a@b.com', 'x');
      expect(container.read(authProvider), isA<Authenticated>());
      expect(storage.token, '5|token-de-teste');
      expect(storage.expiresAt, isNotNull);
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

    test('logout sem conexão ainda limpa a sessão local', () async {
      storage.token = '1|abc';
      await restaurar();
      api.erroLogout = erroRede;
      await container.read(authProvider.notifier).logout();
      expect(api.chamadasLogout, 1);
      expect(storage.token, isNull);
      expect(container.read(authProvider), isA<Unauthenticated>());
    });
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
