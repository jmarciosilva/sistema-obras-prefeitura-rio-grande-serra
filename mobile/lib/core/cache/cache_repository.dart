import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:sqflite/sqflite.dart';

import 'cache_database.dart';
import 'cache_entry.dart';

/// Leitura/escrita do cache local, SEMPRE separado por usuário.
///
/// Falhas do SQLite nunca derrubam a tela: leitura com erro = "sem cache";
/// escrita com erro é ignorada (a informação da API já está na tela).
class CacheRepository {
  CacheRepository(this._abrir);

  final Future<Database> Function() _abrir;
  Future<Database>? _db;

  Future<Database> get _banco => _db ??= _abrir();

  static const _t = CacheDatabase.tabela;

  Future<CacheEntry?> ler(int userId, String chave) async {
    try {
      final linhas = await (await _banco).query(
        _t,
        columns: ['payload', 'updated_at'],
        where: 'user_id = ? AND cache_key = ?',
        whereArgs: [userId, chave],
        limit: 1,
      );
      if (linhas.isEmpty) return null;
      final payload = jsonDecode(linhas.first['payload']! as String);
      final quando = DateTime.tryParse(linhas.first['updated_at']! as String);
      if (payload is! Map<String, dynamic> || quando == null) return null;
      return CacheEntry(
        chave: chave,
        payload: payload,
        atualizadoEm: quando.toLocal(),
      );
    } catch (e) {
      debugPrint('[CACHE] falha ao ler $chave: $e');
      return null;
    }
  }

  /// Grava (ou substitui) a resposta [payload] de [chave] para o usuário.
  Future<void> salvar(
    int userId,
    String chave,
    Map<String, dynamic> payload, {
    DateTime? quando,
  }) async {
    try {
      await (await _banco).insert(_t, {
        'user_id': userId,
        'cache_key': chave,
        'payload': jsonEncode(payload),
        'updated_at': (quando ?? DateTime.now()).toUtc().toIso8601String(),
      }, conflictAlgorithm: ConflictAlgorithm.replace);
    } catch (e) {
      debugPrint('[CACHE] falha ao salvar $chave: $e');
    }
  }

  /// Logout / 401 / sessão expirada: nada do usuário pode sobrar.
  Future<void> limparUsuario(int userId) => _executar(
    (db) => db.delete(_t, where: 'user_id = ?', whereArgs: [userId]),
  );

  /// Novo login: garante que nenhum dado de outro usuário fique no aparelho.
  Future<void> limparOutrosUsuarios(int userId) => _executar(
    (db) => db.delete(_t, where: 'user_id <> ?', whereArgs: [userId]),
  );

  /// Sessão encerrada sem saber de qual usuário era: apaga tudo.
  Future<void> limparTudo() => _executar((db) => db.delete(_t));

  /// Fecha o banco (usado pelos testes para apagar o arquivo).
  @visibleForTesting
  Future<void> fechar() async {
    final db = _db;
    _db = null;
    if (db != null) await (await db).close();
  }

  Future<void> _executar(Future<Object?> Function(Database db) acao) async {
    try {
      await acao(await _banco);
    } catch (e) {
      debugPrint('[CACHE] falha ao limpar: $e');
    }
  }
}

final cacheRepositoryProvider = Provider<CacheRepository>(
  (ref) => CacheRepository(CacheDatabase.abrir),
);
