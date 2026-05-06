import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/health/domain/entities/vital_summary.dart';
import 'package:etamen_app/features/health/domain/repositories/health_repository.dart';

class GetVitalsSummary {
  const GetVitalsSummary(this._repository);

  final HealthRepository _repository;

  Future<ApiResult<VitalSummary>> call() => _repository.getSummary();
}
