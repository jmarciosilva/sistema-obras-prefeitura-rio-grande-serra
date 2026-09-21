import '../../../shared/utils/json.dart';

/// Usuário autenticado — contrato de `GET /me` e de `user` no login:
/// { "id": 12, "name": "...", "email": "...", "perfil": "secretario" }
class User {
  const User({
    required this.id,
    required this.name,
    required this.email,
    required this.perfil,
  });

  final int id;
  final String name;
  final String email;
  final String perfil;

  factory User.fromJson(Map<String, dynamic> json) => User(
    id: asInt(json['id']),
    name: asStringOrNull(json['name']) ?? '',
    email: asStringOrNull(json['email']) ?? '',
    perfil: asStringOrNull(json['perfil']) ?? '',
  );

  /// Mesmos rótulos de User::labelPerfil() no Laravel.
  String get perfilLabel => switch (perfil) {
    'admin' => 'Administrador',
    'secretario' => 'Secretário de Obras',
    'operador' => 'Operador Administrativo',
    'tecnico' => 'Técnico de Campo',
    _ => 'Usuário',
  };
}
