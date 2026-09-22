import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

/// Formatação pt-BR. Ex.: R$ 1.250.500,35 · 19,15% · 21/09/2026
class Fmt {
  Fmt._();

  static final NumberFormat _moeda = NumberFormat.currency(
    locale: 'pt_BR',
    symbol: r'R$',
    decimalDigits: 2,
  );
  static final NumberFormat _percentual = NumberFormat('#,##0.##', 'pt_BR');
  static final NumberFormat _inteiro = NumberFormat.decimalPattern('pt_BR');
  static final DateFormat _data = DateFormat('dd/MM/yyyy');
  static final DateFormat _dataHora = DateFormat('dd/MM/yyyy HH:mm');

  static String moeda(double valor) => _moeda.format(valor);

  static String percentual(double valor) => '${_percentual.format(valor)}%';

  static String inteiro(int valor) => _inteiro.format(valor);

  static String data(DateTime? d) => d == null ? '—' : _data.format(d);

  static String dataHora(DateTime? d) =>
      d == null ? '—' : _dataHora.format(d.toLocal());

  /// "22/09/2026 às 08:42"
  static String dataHoraExtenso(DateTime d) {
    final local = d.toLocal();
    return '${_data.format(local)} às ${DateFormat('HH:mm').format(local)}';
  }

  /// Tempo decorrido: "agora", "há 5 min", "há 2 h", "há 3 dias".
  static String relativo(DateTime d, {DateTime? agora}) {
    final dif = (agora ?? DateTime.now()).difference(d);
    if (dif.inMinutes < 1) return 'agora';
    if (dif.inMinutes < 60) return 'há ${dif.inMinutes} min';
    if (dif.inHours < 24) return 'há ${dif.inHours} h';
    return dif.inDays == 1 ? 'há 1 dia' : 'há ${dif.inDays} dias';
  }

  /// Cor hexadecimal da API (#0d6efd) → Color. Cinza-azulado se inválida.
  static Color cor(String? hex) {
    final h = (hex ?? '').replaceAll('#', '');
    final valor = h.length == 6 ? int.tryParse('FF$h', radix: 16) : null;
    return valor == null ? Colors.blueGrey : Color(valor);
  }
}
