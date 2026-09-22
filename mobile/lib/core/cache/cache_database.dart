import 'package:sqflite/sqflite.dart';

/// Banco SQLite local com o cache de CONSULTA (somente leitura da API).
///
/// Uma única tabela genérica: cada linha guarda o JSON original de uma
/// resposta da API, por usuário. Assim os models atuais (`fromJson`) são
/// reaproveitados sem duplicar as tabelas do Laravel.
class CacheDatabase {
  CacheDatabase._();

  /// Versão do formato do cache. Ao mudar a estrutura dos JSONs de forma
  /// incompatível, incremente: o cache antigo é descartado na abertura.
  static const versao = 1;

  static const tabela = 'cache_entries';
  static const _arquivo = 'obras_rgs_cache.db';

  /// [factory], [caminho] e [unicaInstancia] existem para os testes
  /// (SQLite em memória, um banco novo por teste).
  static Future<Database> abrir({
    DatabaseFactory? factory,
    String? caminho,
    bool unicaInstancia = true,
  }) async {
    final f = factory ?? databaseFactory;
    return f.openDatabase(
      caminho ?? '${await f.getDatabasesPath()}/$_arquivo',
      options: OpenDatabaseOptions(
        version: versao,
        singleInstance: unicaInstancia,
        onCreate: (db, _) => _criar(db),
        onUpgrade: (db, _, _) => _recriar(db),
        onDowngrade: (db, _, _) => _recriar(db),
      ),
    );
  }

  static Future<void> _criar(Database db) => db.execute('''
    CREATE TABLE $tabela (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id INTEGER NOT NULL,
      cache_key TEXT NOT NULL,
      payload TEXT NOT NULL,
      updated_at TEXT NOT NULL,
      UNIQUE (user_id, cache_key)
    )
  ''');

  /// Versão incompatível: descarta tudo (é só cache; a API repõe).
  static Future<void> _recriar(Database db) async {
    await db.execute('DROP TABLE IF EXISTS $tabela');
    await _criar(db);
  }
}
