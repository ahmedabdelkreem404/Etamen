import 'package:etamen_app/features/auth/data/models/user_model.dart';
import 'package:etamen_app/features/auth/domain/entities/auth_session.dart';

class AuthResponse extends AuthSession {
  const AuthResponse({
    required UserModel super.user,
    required super.token,
    required super.tokenType,
  });

  factory AuthResponse.fromJson(Map<String, dynamic> json) {
    return AuthResponse(
      user: UserModel.fromJson(json['user'] as Map<String, dynamic>),
      token: (json['token'] ?? '').toString(),
      tokenType: (json['token_type'] ?? 'Bearer').toString(),
    );
  }
}
