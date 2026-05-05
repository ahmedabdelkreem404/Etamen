import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/auth/data/models/login_request.dart';
import 'package:etamen_app/features/auth/data/models/register_request.dart';
import 'package:etamen_app/features/auth/domain/entities/auth_session.dart';
import 'package:etamen_app/features/auth/domain/entities/auth_user.dart';

abstract class AuthRepository {
  Future<ApiResult<AuthSession>> login(LoginRequest request);

  Future<ApiResult<AuthSession>> register(RegisterRequest request);

  Future<ApiResult<AuthUser>> restoreSession();

  Future<void> logout();
}
