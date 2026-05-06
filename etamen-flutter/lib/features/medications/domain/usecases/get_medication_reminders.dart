import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_reminder.dart';
import 'package:etamen_app/features/medications/domain/repositories/medications_repository.dart';

class GetMedicationReminders {
  const GetMedicationReminders(this._repository);

  final MedicationsRepository _repository;

  Future<ApiResult<List<MedicationReminder>>> call({
    MedicationReminderStatus? status,
  }) {
    return _repository.getReminders(status: status);
  }
}
