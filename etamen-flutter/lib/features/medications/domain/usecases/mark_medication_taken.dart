import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/medications/data/models/create_medication_log_request.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_log.dart';
import 'package:etamen_app/features/medications/domain/repositories/medications_repository.dart';

class MarkMedicationTaken {
  const MarkMedicationTaken(this._repository);

  final MedicationsRepository _repository;

  Future<ApiResult<MedicationLog>> call(
    int reminderId,
    QuickMedicationLogRequest request,
  ) {
    return _repository.markTaken(reminderId, request);
  }
}
