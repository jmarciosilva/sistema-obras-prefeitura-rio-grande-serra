import 'package:flutter_test/flutter_test.dart';
import 'package:obras_rgs/shared/utils/formatters.dart';

/// O intl usa espaço não separável (U+00A0) entre "R$" e o valor.
String _normaliza(String s) => s.replaceAll(' ', ' ');

void main() {
  test('moeda no padrão brasileiro', () {
    expect(_normaliza(Fmt.moeda(1250500.35)), r'R$ 1.250.500,35');
    expect(_normaliza(Fmt.moeda(0)), r'R$ 0,00');
  });

  test('percentual com vírgula decimal', () {
    expect(Fmt.percentual(19.15), '19,15%');
    expect(Fmt.percentual(40), '40%');
    expect(Fmt.percentual(100), '100%');
  });

  test('data dd/MM/yyyy e traço quando ausente', () {
    expect(Fmt.data(DateTime(2025, 8, 15)), '15/08/2025');
    expect(Fmt.data(null), '—');
  });

  test('cor inválida da API não quebra', () {
    expect(Fmt.cor('#0d6efd').toARGB32(), 0xFF0D6EFD);
    expect(Fmt.cor(null), isNotNull);
    expect(Fmt.cor('xyz'), isNotNull);
  });
}
