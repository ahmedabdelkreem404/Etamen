import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/health/domain/entities/health_profile.dart';
import 'package:etamen_app/features/health/domain/repositories/health_repository.dart';

class GetHealthProfile {
  const GetHealthProfile(this._repository);

  final HealthRepository _repository;

  Future<ApiResult<HealthProfile>> call() => _repository.getProfile();
}
