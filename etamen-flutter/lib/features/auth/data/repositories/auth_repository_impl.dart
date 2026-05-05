import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/storage/token_storage.dart';
import 'package:etamen_app/features/auth/data/datasources/auth_remote_data_source.dart';
import 'package:etamen_app/features/auth/data/models/auth_response.dart';
import 'package:etamen_app/features/auth/data/models/login_request.dart';
import 'package:etamen_app/features/auth/data/models/register_request.dart';
import 'package:etamen_app/features/auth/domain/entities/auth_session.dart';
import 'package:etamen_app/features/auth/domain/entities/auth_user.dart';
import 'package:etamen_app/features/auth/domain/repositories/auth_repository.dart';

class AuthRepositoryImpl implements AuthRepository {
  AuthRepositoryImpl({
    required AuthRemoteDataSource remoteDataSource,
    required TokenStorage tokenStorage,
  }) : _remoteDataSource = remoteDataSource,
       _tokenStorage = tokenStorage;

  final AuthRemoteDataSource _remoteDataSource;
  final TokenStorage _tokenStorage;

  @override
  Future<ApiResult<AuthSession>> login(LoginRequest request) async {
    final result = await _remoteDataSource.login(request);
    if (result is ApiSuccess<AuthResponse>) {
      await _tokenStorage.saveToken(result.data.token);
    }
    return result;
  }

  @override
  Future<ApiResult<AuthSession>> register(RegisterRequest request) async {
    final result = await _remoteDataSource.register(request);
    if (result is ApiSuccess<AuthResponse>) {
      await _tokenStorage.saveToken(result.data.token);
    }
    return result;
  }

  @override
  Future<ApiResult<AuthUser>> restoreSession() async {
    final token = await _tokenStorage.readToken();
    if (token == null || token.isEmpty) {
      return const ApiFailure(
        ApiError(
          message: 'لا توجد جلسة محفوظة',
          type: ApiErrorType.unauthenticated,
        ),
      );
    }

    return _remoteDataSource.me();
  }

  @override
  Future<void> logout() async {
    await _remoteDataSource.logout();
    await _tokenStorage.clearToken();
  }
}
