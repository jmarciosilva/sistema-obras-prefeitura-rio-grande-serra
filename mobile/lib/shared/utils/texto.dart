/// Normaliza para busca local: minúsculas e sem acentos.
/// Aproxima o `LIKE` do MySQL (collation *_ci), usado na busca da API.
String normalizar(String texto) {
  const de = 'áàâãäéèêëíìîïóòôõöúùûüçñ';
  const para = 'aaaaaeeeeiiiiooooouuuucn';
  final minusculo = texto.toLowerCase();
  final buffer = StringBuffer();
  for (final c in minusculo.split('')) {
    final i = de.indexOf(c);
    buffer.write(i >= 0 ? para[i] : c);
  }
  return buffer.toString();
}

/// O trecho [busca] aparece em algum dos [campos]? (como `LIKE %busca%`)
/// Busca vazia casa com tudo.
bool contemBusca(String busca, Iterable<String?> campos) {
  final termo = normalizar(busca.trim());
  if (termo.isEmpty) return true;
  return campos.any((c) => c != null && normalizar(c).contains(termo));
}
