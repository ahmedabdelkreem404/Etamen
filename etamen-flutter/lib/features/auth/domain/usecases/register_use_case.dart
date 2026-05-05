import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/auth/data/models/register_request.dart';
import 'package:etamen_app/features/auth/domain/entities/auth_session.dart';
import 'package:etamen_app/features/auth/domain/repositories/auth_repository.dart';

class RegisterUseCase {
  const RegisterUseCase(this._repository);

  final AuthRepository _repository;

  Future<ApiResult<AuthSession>> call(RegisterRequest request) {
    return _repository.register(request);
  }
}
