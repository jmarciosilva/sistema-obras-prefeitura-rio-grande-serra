import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../api/api_exception.dart';
import '../auth/auth_state.dart';
import 'cache_entry.dart';
import 'cache_repository.dart';
import 'sync_state.dart';

/// Resultado de uma consulta: o valor e de quando ele é.
class Dados<T> {
  const Dados(
    this.valor, {
    required this.atualizadoEm,
    this.doCache = false,
    this.parcial = false,
  });

  final T valor;

  /// Quando a API forneceu esses dados (sempre exibido quando offline).
  final DateTime atualizadoEm;

  /// Veio do armazenamento local (ainda não revalidado com a API).
  final bool doCache;

  /// Resumo montado a partir de outra consulta (ex.: item da lista), porque
  /// o detalhe completo nunca foi sincronizado.
  final bool parcial;
}

/// Sem conexão e sem nenhum dado local para mostrar.
class SemDadosOffline implements Exception {
  const SemDadosOffline();

  static const mensagem =
      'Os detalhes completos deste item ainda não foram sincronizados.';

  @override
  String toString() => 'SemDadosOffline';
}

/// Ponte entre a API e o cache local do usuário logado.
class ConsultaOffline {
  ConsultaOffline({
    required this.cache,
    required this.sync,
    required this.userId,
  });

  final CacheRepository cache;
  final SyncNotifier sync;
  final int userId;

  Future<CacheEntry?> lerCache(String chave) => cache.ler(userId, chave);

  /// Consulta a API, grava a resposta no cache e atualiza o estado global.
  /// Lança [ApiException] (o chamador decide se usa o cache).
  Future<CacheEntry> buscar(
    String chave,
    Future<Map<String, dynamic>> Function() api,
  ) async {
    sync.iniciou();
    try {
      final json = await api();
      final agora = DateTime.now();
      await cache.salvar(userId, chave, json, quando: agora);
      sync.sucesso(agora);
      return CacheEntry(chave: chave, payload: json, atualizadoEm: agora);
    } on ApiException catch (e) {
      sync.falhou(semConexao: e.servidorInacessivel);
      rethrow;
    } catch (_) {
      sync.falhou(semConexao: false);
      rethrow;
    }
  }
}

/// Id do usuário logado (null sem sessão). O cache é sempre por usuário.
final usuarioIdProvider = Provider<int?>((ref) {
  final auth = ref.watch(authProvider);
  return auth is Authenticated ? auth.user.id : null;
});

/// Recriado ao trocar de usuário: todas as consultas recarregam.
final consultaOfflineProvider = Provider<ConsultaOffline>((ref) {
  final userId = ref.watch(usuarioIdProvider);
  if (userId == null) throw StateError('Sem sessão');
  return ConsultaOffline(
    cache: ref.watch(cacheRepositoryProvider),
    sync: ref.read(syncProvider.notifier),
    userId: userId,
  );
});

/// Stale-while-revalidate:
/// 1. se há cache, devolve IMEDIATAMENTE e revalida com a API em segundo
///    plano (o resultado chega por [publicar]);
/// 2. sem cache, consulta a API;
/// 3. sem cache e sem conexão, tenta [alternativaOffline] (resumo parcial).
class Swr<T> {
  Swr({
    required this.consulta,
    required this.chave,
    required this.api,
    required this.converter,
    this.alternativaOffline,
  });

  final ConsultaOffline consulta;
  final String chave;
  final Future<Map<String, dynamic>> Function() api;
  final T Function(Map<String, dynamic> json) converter;
  final Future<Dados<T>?> Function()? alternativaOffline;

  Future<Dados<T>> carregar(void Function(Dados<T>) publicar) async {
    final local = await consulta.lerCache(chave);
    if (local != null) {
      // Future(): roda depois que o valor do cache já estiver na tela
      Future(() => atualizar(publicar));
      return _dados(local, doCache: true);
    }
    try {
      return _dados(await consulta.buscar(chave, api));
    } on ApiException catch (e) {
      if (!e.servidorInacessivel || alternativaOffline == null) rethrow;
      return await alternativaOffline!() ?? (throw const SemDadosOffline());
    }
  }

  /// Consulta a API e publica. `false` se não conseguiu (dados mantidos).
  Future<bool> atualizar(void Function(Dados<T>) publicar) async {
    try {
      publicar(_dados(await consulta.buscar(chave, api)));
      return true;
    } catch (_) {
      // Rede: fica o cache (estado global já está offline).
      // 401: o ApiClient já encerrou a sessão.
      return false;
    }
  }

  Dados<T> _dados(CacheEntry e, {bool doCache = false}) => Dados(
    converter(e.payload),
    atualizadoEm: e.atualizadoEm,
    doCache: doCache,
  );
}

/// Quando outra chamada à API volta a funcionar (offline → online):
/// - consulta em erro ou com resumo parcial → recarrega do zero;
/// - consulta exibindo cache não revalidado → atualiza em segundo plano.
/// Não gera requisições enquanto continua offline.
void _aoVoltarConexao<T>(
  Ref ref,
  AsyncValue<Dados<T>> Function() estado,
  Future<bool> Function() atualizar,
) {
  var ativo = true;
  ref.onDispose(() => ativo = false);
  ref.listen<bool>(syncProvider.select((s) => s.offline), (antes, agora) {
    if (antes != true || agora) return;
    // Future(): se foi ESTA consulta que voltou a funcionar, ela já terá
    // publicado o resultado — evita repetir a requisição.
    Future(() {
      if (!ativo) return;
      final atual = estado();
      if (atual.hasError || (atual.valueOrNull?.parcial ?? false)) {
        ref.invalidateSelf();
      } else if (atual.valueOrNull?.doCache ?? false) {
        atualizar();
      }
    });
  });
}

/// Base dos providers de consulta sem parâmetro (Dashboard, listas).
abstract class ConsultaNotifier<T> extends AsyncNotifier<Dados<T>> {
  int _geracao = 0;

  Swr<T> swr(ConsultaOffline consulta);

  @override
  Future<Dados<T>> build() {
    final consulta = ref.watch(consultaOfflineProvider);
    final geracao = ++_geracao;
    ref.onDispose(() => _geracao++);
    _aoVoltarConexao(ref, () => state, atualizar);
    return swr(consulta).carregar((d) => _publicar(geracao, d));
  }

  /// Pull-to-refresh. `false` = sem conexão (a tela avisa e mantém o cache).
  Future<bool> atualizar() {
    final geracao = _geracao;
    return swr(
      ref.read(consultaOfflineProvider),
    ).atualizar((d) => _publicar(geracao, d));
  }

  /// Ignora respostas de uma sessão/instância anterior (ex.: troca de
  /// usuário no meio de uma revalidação).
  void _publicar(int geracao, Dados<T> dados) {
    if (geracao == _geracao) state = AsyncData(dados);
  }
}

/// Base dos providers de detalhe (`obra:{id}`, `contrato:{id}`).
abstract class ConsultaDetalheNotifier<T>
    extends AutoDisposeFamilyAsyncNotifier<Dados<T>, int> {
  int _geracao = 0;

  Swr<T> swr(ConsultaOffline consulta, int id);

  @override
  Future<Dados<T>> build(int arg) {
    final consulta = ref.watch(consultaOfflineProvider);
    final geracao = ++_geracao;
    ref.onDispose(() => _geracao++);
    _aoVoltarConexao(ref, () => state, atualizar);
    return swr(consulta, arg).carregar((d) => _publicar(geracao, d));
  }

  Future<bool> atualizar() {
    final geracao = _geracao;
    return swr(
      ref.read(consultaOfflineProvider),
      arg,
    ).atualizar((d) => _publicar(geracao, d));
  }

  void _publicar(int geracao, Dados<T> dados) {
    if (geracao == _geracao) state = AsyncData(dados);
  }
}
