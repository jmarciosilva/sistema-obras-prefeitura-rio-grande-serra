import 'dart:convert';
import 'dart:typed_data';

import 'package:dio/dio.dart';
import 'package:obras_rgs/core/api/api_exception.dart';
import 'package:obras_rgs/core/cache/cache_database.dart';
import 'package:obras_rgs/core/cache/cache_repository.dart';
import 'package:obras_rgs/core/storage/secure_storage.dart';
import 'package:obras_rgs/features/auth/data/auth_api.dart';
import 'package:obras_rgs/features/auth/models/login_response.dart';
import 'package:obras_rgs/features/auth/models/user.dart';
import 'package:sqflite_common_ffi/sqflite_ffi.dart';

/// Armazenamento em memória (o flutter_secure_storage real exige plugin).
class FakeSessionStorage implements SessionStorage {
  String? token;
  DateTime? expiresAt;
  User? usuario;

  @override
  Future<String?> readToken() async => token;

  @override
  Future<DateTime?> readExpiresAt() async => expiresAt;

  @override
  Future<void> saveToken(String token, {DateTime? expiresAt}) async {
    this.token = token;
    this.expiresAt = expiresAt;
  }

  @override
  Future<User?> readUser() async => usuario;

  @override
  Future<void> saveUser(User user) async => usuario = user;

  @override
  Future<void> clear() async {
    token = null;
    expiresAt = null;
    usuario = null;
  }
}

/// Cache com SQLite REAL (via FFI, sem plugin), em memória e exclusivo do
/// teste — valida o SQL de verdade (UNIQUE, filtros por usuário) sem criar
/// arquivos.
CacheRepository cacheDeTeste() {
  sqfliteFfiInit();
  return CacheRepository(
    () => CacheDatabase.abrir(
      factory: databaseFactoryFfi,
      caminho: inMemoryDatabasePath,
      unicaInstancia: false,
    ),
  );
}

const usuarioTeste = User(
  id: 12,
  name: 'Secretário Teste',
  email: 'secretario@exemplo.com',
  perfil: 'secretario',
);

/// AuthApi controlável: cada método devolve o que o teste configurar.
class FakeAuthApi implements AuthApi {
  Object? erroMe;
  Object? erroLogin;
  Object? erroLogout;
  int chamadasMe = 0;
  int chamadasLogout = 0;

  @override
  Future<User> me() async {
    chamadasMe++;
    if (erroMe != null) throw erroMe!;
    return usuarioTeste;
  }

  @override
  Future<LoginResponse> login(String email, String password) async {
    if (erroLogin != null) throw erroLogin!;
    return LoginResponse(
      token: '5|token-de-teste',
      expiresAt: DateTime.now().add(const Duration(days: 30)),
      user: usuarioTeste,
    );
  }

  @override
  Future<void> logout() async {
    chamadasLogout++;
    if (erroLogout != null) throw erroLogout!;
  }
}

const erroRede = ApiException(
  ApiErrorType.network,
  'Não foi possível conectar ao servidor.',
);

/// Adaptador HTTP falso para o Dio: responde [status] + [body] a tudo e
/// guarda a última requisição recebida.
class FakeAdapter implements HttpClientAdapter {
  FakeAdapter(this.status, this.body);

  final int status;
  final Object body;
  RequestOptions? ultima;

  @override
  Future<ResponseBody> fetch(
    RequestOptions options,
    Stream<Uint8List>? requestStream,
    Future<void>? cancelFuture,
  ) async {
    ultima = options;
    return ResponseBody.fromString(
      jsonEncode(body),
      status,
      headers: {
        Headers.contentTypeHeader: [Headers.jsonContentType],
      },
    );
  }

  @override
  void close({bool force = false}) {}
}

/// Adaptador que simula servidor fora do ar.
class OfflineAdapter implements HttpClientAdapter {
  @override
  Future<ResponseBody> fetch(
    RequestOptions options,
    Stream<Uint8List>? requestStream,
    Future<void>? cancelFuture,
  ) async => throw DioException.connectionError(
    requestOptions: options,
    reason: 'Connection refused',
  );

  @override
  void close({bool force = false}) {}
}
