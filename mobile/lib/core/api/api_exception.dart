import 'package:dio/dio.dart';

enum ApiErrorType {
  network,
  unauthorized,
  forbidden,
  notFound,
  validation,
  tooManyRequests,
  server,
  unknown,
}

/// Erro da API já traduzido para uma mensagem amigável em português.
/// Nunca expõe detalhes técnicos do Dio ou do servidor.
class ApiException implements Exception {
  const ApiException(this.type, this.message, {this.statusCode});

  final ApiErrorType type;
  final String message;
  final int? statusCode;

  static const _semConexao = 'Não foi possível conectar ao servidor.';

  /// O servidor não pôde ser alcançado (sem conexão, timeout, DNS...) ou
  /// está fora do ar (5xx). Nesses casos o app usa os dados locais.
  /// Nunca inclui 401: token rejeitado sempre encerra a sessão.
  bool get servidorInacessivel =>
      type == ApiErrorType.network || type == ApiErrorType.server;

  factory ApiException.fromDio(DioException e) {
    switch (e.type) {
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
      case DioExceptionType.connectionError:
        return const ApiException(ApiErrorType.network, _semConexao);
      default:
        // Sem resposta HTTP (DNS, socket, certificado...) = problema de rede
        final status = e.response?.statusCode;
        if (status == null) {
          return const ApiException(ApiErrorType.network, _semConexao);
        }
        return ApiException.fromStatus(status);
    }
  }

  factory ApiException.fromStatus(int status) {
    final (tipo, mensagem) = switch (status) {
      401 => (
        ApiErrorType.unauthorized,
        'Sessão expirada. Faça login novamente.',
      ),
      403 => (ApiErrorType.forbidden, 'Acesso não permitido.'),
      404 => (ApiErrorType.notFound, 'Registro não encontrado.'),
      422 => (
        ApiErrorType.validation,
        'Dados inválidos. Verifique e tente novamente.',
      ),
      429 => (
        ApiErrorType.tooManyRequests,
        'Muitas tentativas. Aguarde um minuto e tente novamente.',
      ),
      >= 500 => (
        ApiErrorType.server,
        'O servidor encontrou um erro. Tente novamente mais tarde.',
      ),
      _ => (ApiErrorType.unknown, 'Erro ao carregar dados.'),
    };
    return ApiException(tipo, mensagem, statusCode: status);
  }

  @override
  String toString() => 'ApiException($type, $statusCode)';
}
