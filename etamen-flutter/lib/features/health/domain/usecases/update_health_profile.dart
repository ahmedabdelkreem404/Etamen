import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/health/data/models/update_health_profile_request.dart';
import 'package:etamen_app/features/health/domain/entities/health_profile.dart';
import 'package:etamen_app/features/health/domain/repositories/health_repository.dart';

class UpdateHealthProfile {
  const UpdateHealthProfile(this._repository);

  final HealthRepository _repository;

  Future<ApiResult<HealthProfile>> call(UpdateHealthProfileRequest request) {
    return _repository.updateProfile(request);
  }
}
