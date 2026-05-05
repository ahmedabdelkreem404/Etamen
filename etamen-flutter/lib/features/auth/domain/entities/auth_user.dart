class AuthUser {
  const AuthUser({
    required this.id,
    required this.email,
    required this.roles,
    this.name,
  });

  final int id;
  final String email;
  final String? name;
  final List<String> roles;
}
