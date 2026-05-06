import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/health/domain/entities/vital_record.dart';
import 'package:etamen_app/features/health/domain/entities/vital_trend.dart';
import 'package:etamen_app/features/health/domain/repositories/health_repository.dart';

class GetVitalTrends {
  const GetVitalTrends(this._repository);

  final HealthRepository _repository;

  Future<ApiResult<VitalTrend>> call({required VitalType type}) {
    return _repository.getTrends(type: type);
  }
}
