import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/auth/domain/entities/auth_user.dart';
import 'package:etamen_app/features/auth/domain/repositories/auth_repository.dart';

class RestoreSessionUseCase {
  const RestoreSessionUseCase(this._repository);

  final AuthRepository _repository;

  Future<ApiResult<AuthUser>> call() {
    return _repository.restoreSession();
  }
}
