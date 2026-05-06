import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/medications/data/models/create_medication_log_request.dart';
import 'package:etamen_app/features/medications/data/models/create_medication_reminder_request.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_adherence.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_log.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_refill_event.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_reminder.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_schedule_item.dart';

abstract class MedicationsRepository {
  Future<ApiResult<List<MedicationReminder>>> getReminders({
    MedicationReminderStatus? status,
  });

  Future<ApiResult<MedicationReminder>> getReminder(int reminderId);

  Future<ApiResult<MedicationReminder>> createReminder(
    CreateMedicationReminderRequest request,
  );

  Future<ApiResult<MedicationReminder>> updateReminder(
    int reminderId,
    CreateMedicationReminderRequest request,
  );

  Future<ApiResult<MedicationReminder>> pauseReminder(int reminderId);

  Future<ApiResult<MedicationReminder>> resumeReminder(int reminderId);

  Future<ApiResult<MedicationReminder>> cancelReminder(int reminderId);

  Future<ApiResult<List<MedicationScheduleItem>>> getToday();

  Future<ApiResult<List<MedicationScheduleItem>>> getUpcoming({int days = 7});

  Future<ApiResult<List<MedicationLog>>> getLogs({int? reminderId});

  Future<ApiResult<MedicationLog>> createLog(
    int reminderId,
    CreateMedicationLogRequest request,
  );

  Future<ApiResult<MedicationLog>> markTaken(
    int reminderId,
    QuickMedicationLogRequest request,
  );

  Future<ApiResult<MedicationLog>> markSkipped(
    int reminderId,
    QuickMedicationLogRequest request,
  );

  Future<ApiResult<MedicationAdherence>> getAdherence();

  Future<ApiResult<List<MedicationRefillEvent>>> getRefills();

  Future<ApiResult<MedicationRefillEvent>> recordRefillDone(
    int reminderId, {
    String? notes,
  });

  Future<ApiResult<MedicationRefillEvent>> recordRefillSkipped(
    int reminderId, {
    String? notes,
  });
}
