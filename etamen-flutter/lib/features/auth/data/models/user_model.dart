import 'package:etamen_app/features/auth/domain/entities/auth_user.dart';

class UserModel extends AuthUser {
  const UserModel({
    required super.id,
    required super.email,
    required super.roles,
    super.name,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: (json['id'] as num).toInt(),
      name: json['name']?.toString(),
      email: (json['email'] ?? '').toString(),
      roles: (json['roles'] as List? ?? const [])
          .map((item) => item.toString())
          .toList(growable: false),
    );
  }
}
