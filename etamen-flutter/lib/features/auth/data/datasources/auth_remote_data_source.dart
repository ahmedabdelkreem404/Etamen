import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/network/api_client.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/auth/data/models/auth_response.dart';
import 'package:etamen_app/features/auth/data/models/login_request.dart';
import 'package:etamen_app/features/auth/data/models/register_request.dart';
import 'package:etamen_app/features/auth/data/models/user_model.dart';

class AuthRemoteDataSource {
  AuthRemoteDataSource(this._client);

  final ApiClient _client;

  Future<ApiResult<AuthResponse>> login(LoginRequest request) {
    return _client.post<AuthResponse>(
      ApiEndpoints.login,
      data: request.toJson(),
      parser: (raw) => AuthResponse.fromJson(raw as Map<String, dynamic>),
    );
  }

  Future<ApiResult<AuthResponse>> register(RegisterRequest request) {
    return _client.post<AuthResponse>(
      ApiEndpoints.register,
      data: request.toJson(),
      parser: (raw) => AuthResponse.fromJson(raw as Map<String, dynamic>),
    );
  }

  Future<ApiResult<UserModel>> me() {
    return _client.get<UserModel>(
      ApiEndpoints.me,
      parser: (raw) => UserModel.fromJson(raw as Map<String, dynamic>),
    );
  }

  Future<ApiResult<Object?>> logout() {
    return _client.post<Object?>(ApiEndpoints.logout, parser: (raw) => raw);
  }
}
