import 'package:flutter_riverpod/flutter_riverpod.dart';

enum StatusSync { online, offline, sincronizando }

/// Estado global de sincronização com a API.
///
/// A fonte de verdade é o resultado REAL das chamadas à API (ter Wi-Fi não
/// significa alcançar o servidor): sucesso → online; falha de conexão ou
/// servidor fora do ar → offline.
class SyncState {
  const SyncState({
    this.offline = false,
    this.emAndamento = 0,
    this.ultimaSincronizacao,
  });

  /// A última tentativa de falar com a API falhou por conexão.
  final bool offline;

  /// Quantas consultas à API estão em andamento agora.
  final int emAndamento;

  /// Horário da última resposta bem-sucedida da API.
  final DateTime? ultimaSincronizacao;

  StatusSync get status => emAndamento > 0
      ? StatusSync.sincronizando
      : (offline ? StatusSync.offline : StatusSync.online);
}

class SyncNotifier extends Notifier<SyncState> {
  @override
  SyncState build() => const SyncState();

  void iniciou() => state = SyncState(
    offline: state.offline,
    emAndamento: state.emAndamento + 1,
    ultimaSincronizacao: state.ultimaSincronizacao,
  );

  void sucesso([DateTime? quando]) => state = SyncState(
    emAndamento: _menosUm,
    ultimaSincronizacao: quando ?? DateTime.now(),
  );

  /// [semConexao]: a falha foi de rede/servidor (entra em modo offline).
  /// Outras falhas (ex.: 404) não mudam o modo.
  void falhou({required bool semConexao}) => state = SyncState(
    offline: semConexao || state.offline,
    emAndamento: _menosUm,
    ultimaSincronizacao: state.ultimaSincronizacao,
  );

  /// Sessão restaurada sem alcançar o servidor.
  void marcarOffline() => state = SyncState(
    offline: true,
    emAndamento: state.emAndamento,
    ultimaSincronizacao: state.ultimaSincronizacao,
  );

  /// Logout / sessão encerrada.
  void reset() => state = const SyncState();

  int get _menosUm => state.emAndamento > 0 ? state.emAndamento - 1 : 0;
}

final syncProvider = NotifierProvider<SyncNotifier, SyncState>(
  SyncNotifier.new,
);
