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

  /// Cor hexadecimal da API (#0d6efd) → Color. Cinza-azulado se inválida.
  static Color cor(String? hex) {
    final h = (hex ?? '').replaceAll('#', '');
    final valor = h.length == 6 ? int.tryParse('FF$h', radix: 16) : null;
    return valor == null ? Colors.blueGrey : Color(valor);
  }
}
