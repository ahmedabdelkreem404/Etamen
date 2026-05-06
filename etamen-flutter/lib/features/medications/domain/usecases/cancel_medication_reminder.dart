import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_reminder.dart';
import 'package:etamen_app/features/medications/domain/repositories/medications_repository.dart';

class CancelMedicationReminder {
  const CancelMedicationReminder(this._repository);

  final MedicationsRepository _repository;

  Future<ApiResult<MedicationReminder>> call(int reminderId) {
    return _repository.cancelReminder(reminderId);
  }
}
