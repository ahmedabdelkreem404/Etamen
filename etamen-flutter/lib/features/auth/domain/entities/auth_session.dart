import 'package:etamen_app/features/auth/domain/entities/auth_user.dart';

class AuthSession {
  const AuthSession({
    required this.user,
    required this.token,
    required this.tokenType,
  });

  final AuthUser user;
  final String token;
  final String tokenType;
}
