import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/health/data/models/create_vital_record_request.dart';
import 'package:etamen_app/features/health/domain/entities/vital_record.dart';
import 'package:etamen_app/features/health/domain/repositories/health_repository.dart';

class CreateVitalRecord {
  const CreateVitalRecord(this._repository);

  final HealthRepository _repository;

  Future<ApiResult<VitalRecord>> call(CreateVitalRecordRequest request) {
    return _repository.createVital(request);
  }
}
