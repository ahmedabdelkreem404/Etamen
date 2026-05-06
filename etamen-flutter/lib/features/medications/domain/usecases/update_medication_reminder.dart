import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/medications/data/models/create_medication_reminder_request.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_reminder.dart';
import 'package:etamen_app/features/medications/domain/repositories/medications_repository.dart';

class UpdateMedicationReminder {
  const UpdateMedicationReminder(this._repository);

  final MedicationsRepository _repository;

  Future<ApiResult<MedicationReminder>> call(
    int reminderId,
    CreateMedicationReminderRequest request,
  ) {
    return _repository.updateReminder(reminderId, request);
  }
}
