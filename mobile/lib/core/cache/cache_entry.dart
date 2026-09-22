/// Uma resposta da API guardada localmente.
class CacheEntry {
  const CacheEntry({
    required this.chave,
    required this.payload,
    required this.atualizadoEm,
  });

  /// Ex.: `dashboard`, `obras`, `obra:12`, `contrato:7`.
  final String chave;

  /// JSON original retornado pela API.
  final Map<String, dynamic> payload;

  /// Quando a API respondeu com sucesso (horário do aparelho).
  final DateTime atualizadoEm;
}
