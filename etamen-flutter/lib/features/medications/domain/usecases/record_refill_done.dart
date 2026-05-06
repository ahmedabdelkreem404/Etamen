import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_refill_event.dart';
import 'package:etamen_app/features/medications/domain/repositories/medications_repository.dart';

class RecordRefillDone {
  const RecordRefillDone(this._repository);

  final MedicationsRepository _repository;

  Future<ApiResult<MedicationRefillEvent>> call(int reminderId) {
    return _repository.recordRefillDone(reminderId);
  }
}
