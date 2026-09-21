import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

import '../config/app_config.dart';
import '../storage/secure_storage.dart';
import 'api_exception.dart';

/// Cliente HTTP único do app.
///
/// - Anexa `Authorization: Bearer <token>` quando há token salvo.
/// - Em 401 de rota protegida: apaga o token e avisa [onUnauthorized]
///   (que leva o usuário ao login). Não repete a requisição, então não há
///   risco de loop.
/// - Converte qualquer erro em [ApiException] com mensagem amigável.
class ApiClient {
  ApiClient({required SessionStorage storage, this.onUnauthorized, Dio? dio})
    : _storage = storage,
      dio = dio ?? Dio() {
    this.dio.options
      ..baseUrl = AppConfig.apiUrl
      ..connectTimeout = AppConfig.connectTimeout
      ..receiveTimeout = AppConfig.receiveTimeout
      ..headers = {'Accept': 'application/json'};

    this.dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await _storage.readToken();
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          options.extra['inicio'] = DateTime.now();
          handler.next(options);
        },
        onResponse: (response, handler) {
          _log(response.requestOptions, response.statusCode);
          handler.next(response);
        },
        onError: (error, handler) async {
          final options = error.requestOptions;
          _log(options, error.response?.statusCode ?? error.type.name);
          final enviouToken = options.headers.containsKey('Authorization');
          final ehAutenticacao =
              options.path.endsWith('/login') ||
              options.path.endsWith('/logout');

          if (error.response?.statusCode == 401 &&
              enviouToken &&
              !ehAutenticacao) {
            await _storage.clear();
            onUnauthorized?.call();
          }
          handler.next(error);
        },
      ),
    );
  }

  final Dio dio;
  final SessionStorage _storage;

  /// Chamado quando uma rota protegida responde 401.
  void Function()? onUnauthorized;

  /// Só em debug: método, rota, status e tempo. Nunca registra token/corpo.
  static void _log(RequestOptions o, Object? status) {
    if (!kDebugMode) return;
    final inicio = o.extra['inicio'];
    final ms = inicio is DateTime
        ? DateTime.now().difference(inicio).inMilliseconds
        : -1;
    debugPrint('[API] ${o.method} ${o.path} → $status (${ms}ms)');
  }

  Future<Map<String, dynamic>> get(
    String path, {
    Map<String, dynamic>? query,
  }) => _send(() => dio.get<dynamic>(path, queryParameters: query));

  Future<Map<String, dynamic>> post(String path, {Object? data}) => _send(
    () => dio.post<dynamic>(
      path,
      data: data,
      options: Options(contentType: Headers.jsonContentType),
    ),
  );

  Future<Map<String, dynamic>> _send(
    Future<Response<dynamic>> Function() request,
  ) async {
    try {
      final response = await request();
      final data = response.data;
      if (data is Map<String, dynamic>) return data;
      throw const ApiException(
        ApiErrorType.unknown,
        'Resposta inesperada do servidor.',
      );
    } on DioException catch (e) {
      throw ApiException.fromDio(e);
    }
  }
}
