import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/health/domain/entities/vital_record.dart';
import 'package:etamen_app/features/health/domain/repositories/health_repository.dart';

class GetVitals {
  const GetVitals(this._repository);

  final HealthRepository _repository;

  Future<ApiResult<List<VitalRecord>>> call({VitalType? type}) {
    return _repository.getVitals(type: type);
  }
}
