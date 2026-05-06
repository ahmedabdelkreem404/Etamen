import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/medications/data/datasources/medications_remote_data_source.dart';
import 'package:etamen_app/features/medications/data/models/create_medication_log_request.dart';
import 'package:etamen_app/features/medications/data/models/create_medication_reminder_request.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_adherence.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_log.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_refill_event.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_reminder.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_schedule_item.dart';
import 'package:etamen_app/features/medications/domain/repositories/medications_repository.dart';

class MedicationsRepositoryImpl implements MedicationsRepository {
  const MedicationsRepositoryImpl(this._remoteDataSource);

  final MedicationsRemoteDataSource _remoteDataSource;

  @override
  Future<ApiResult<List<MedicationReminder>>> getReminders({
    MedicationReminderStatus? status,
  }) {
    return _remoteDataSource.getReminders(status: status);
  }

  @override
  Future<ApiResult<MedicationReminder>> getReminder(int reminderId) {
    return _remoteDataSource.getReminder(reminderId);
  }

  @override
  Future<ApiResult<MedicationReminder>> createReminder(
    CreateMedicationReminderRequest request,
  ) {
    return _remoteDataSource.createReminder(request);
  }

  @override
  Future<ApiResult<MedicationReminder>> updateReminder(
    int reminderId,
    CreateMedicationReminderRequest request,
  ) {
    return _remoteDataSource.updateReminder(reminderId, request);
  }

  @override
  Future<ApiResult<MedicationReminder>> pauseReminder(int reminderId) {
    return _remoteDataSource.pauseReminder(reminderId);
  }

  @override
  Future<ApiResult<MedicationReminder>> resumeReminder(int reminderId) {
    return _remoteDataSource.resumeReminder(reminderId);
  }

  @override
  Future<ApiResult<MedicationReminder>> cancelReminder(int reminderId) {
    return _remoteDataSource.cancelReminder(reminderId);
  }

  @override
  Future<ApiResult<List<MedicationScheduleItem>>> getToday() {
    return _remoteDataSource.getToday();
  }

  @override
  Future<ApiResult<List<MedicationScheduleItem>>> getUpcoming({int days = 7}) {
    return _remoteDataSource.getUpcoming(days: days);
  }

  @override
  Future<ApiResult<List<MedicationLog>>> getLogs({int? reminderId}) {
    return _remoteDataSource.getLogs(reminderId: reminderId);
  }

  @override
  Future<ApiResult<MedicationLog>> createLog(
    int reminderId,
    CreateMedicationLogRequest request,
  ) {
    return _remoteDataSource.createLog(reminderId, request);
  }

  @override
  Future<ApiResult<MedicationLog>> markTaken(
    int reminderId,
    QuickMedicationLogRequest request,
  ) {
    return _remoteDataSource.markTaken(reminderId, request);
  }

  @override
  Future<ApiResult<MedicationLog>> markSkipped(
    int reminderId,
    QuickMedicationLogRequest request,
  ) {
    return _remoteDataSource.markSkipped(reminderId, request);
  }

  @override
  Future<ApiResult<MedicationAdherence>> getAdherence() {
    return _remoteDataSource.getAdherence();
  }

  @override
  Future<ApiResult<List<MedicationRefillEvent>>> getRefills() {
    return _remoteDataSource.getRefills();
  }

  @override
  Future<ApiResult<MedicationRefillEvent>> recordRefillDone(
    int reminderId, {
    String? notes,
  }) {
    return _remoteDataSource.recordRefillDone(reminderId, notes: notes);
  }

  @override
  Future<ApiResult<MedicationRefillEvent>> recordRefillSkipped(
    int reminderId, {
    String? notes,
  }) {
    return _remoteDataSource.recordRefillSkipped(reminderId, notes: notes);
  }
}
