/// Leitura tolerante de JSON: a API pode mandar 1000, 1000.5 ou null.
/// Valores numéricos nunca voltam null/NaN.
double asDouble(Object? v) {
  if (v is num) return v.isFinite ? v.toDouble() : 0;
  if (v is String) return double.tryParse(v) ?? 0;
  return 0;
}

double? asDoubleOrNull(Object? v) => v == null ? null : asDouble(v);

int asInt(Object? v) {
  if (v is int) return v;
  if (v is num) return v.toInt();
  if (v is String) return int.tryParse(v) ?? 0;
  return 0;
}

String? asStringOrNull(Object? v) {
  if (v == null) return null;
  final s = v.toString().trim();
  return s.isEmpty ? null : s;
}

/// Datas "YYYY-MM-DD" (sem fuso) ou ISO 8601 completas.
DateTime? asDate(Object? v) => v is String ? DateTime.tryParse(v) : null;

Map<String, dynamic>? asMap(Object? v) =>
    v is Map ? Map<String, dynamic>.from(v) : null;

List<Map<String, dynamic>> asMapList(Object? v) => v is List
    ? v.whereType<Map>().map((e) => Map<String, dynamic>.from(e)).toList()
    : const [];
