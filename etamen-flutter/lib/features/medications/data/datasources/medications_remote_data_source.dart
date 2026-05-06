import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/network/api_client.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/medications/data/models/create_medication_log_request.dart';
import 'package:etamen_app/features/medications/data/models/create_medication_reminder_request.dart';
import 'package:etamen_app/features/medications/data/models/medication_adherence_model.dart';
import 'package:etamen_app/features/medications/data/models/medication_log_model.dart';
import 'package:etamen_app/features/medications/data/models/medication_refill_event_model.dart';
import 'package:etamen_app/features/medications/data/models/medication_reminder_model.dart';
import 'package:etamen_app/features/medications/data/models/medication_schedule_item_model.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_reminder.dart';

class MedicationsRemoteDataSource {
  const MedicationsRemoteDataSource(this._client);

  final ApiClient _client;

  Future<ApiResult<List<MedicationReminderModel>>> getReminders({
    MedicationReminderStatus? status,
  }) {
    return _client.get<List<MedicationReminderModel>>(
      ApiEndpoints.medicationReminders,
      queryParameters: {
        if (status != null && status != MedicationReminderStatus.unknown)
          'status': status.wireValue,
      },
      parser: (raw) => _parseList(
        raw,
      ).map(MedicationReminderModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<MedicationReminderModel>> getReminder(int reminderId) {
    return _client.get<MedicationReminderModel>(
      ApiEndpoints.medicationReminder(reminderId),
      parser: (raw) => MedicationReminderModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<MedicationReminderModel>> createReminder(
    CreateMedicationReminderRequest request,
  ) {
    return _client.post<MedicationReminderModel>(
      ApiEndpoints.medicationReminders,
      data: request.toJson(),
      parser: (raw) => MedicationReminderModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<MedicationReminderModel>> updateReminder(
    int reminderId,
    CreateMedicationReminderRequest request,
  ) {
    return _client.put<MedicationReminderModel>(
      ApiEndpoints.medicationReminder(reminderId),
      data: request.toJson(),
      parser: (raw) => MedicationReminderModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<MedicationReminderModel>> pauseReminder(int reminderId) {
    return _statusAction(ApiEndpoints.medicationReminderPause(reminderId));
  }

  Future<ApiResult<MedicationReminderModel>> resumeReminder(int reminderId) {
    return _statusAction(ApiEndpoints.medicationReminderResume(reminderId));
  }

  Future<ApiResult<MedicationReminderModel>> cancelReminder(int reminderId) {
    return _statusAction(ApiEndpoints.medicationReminderCancel(reminderId));
  }

  Future<ApiResult<MedicationReminderModel>> _statusAction(String path) {
    return _client.post<MedicationReminderModel>(
      path,
      parser: (raw) => MedicationReminderModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<List<MedicationScheduleItemModel>>> getToday() {
    return _client.get<List<MedicationScheduleItemModel>>(
      ApiEndpoints.medicationToday,
      parser: (raw) => _parseList(
        raw,
      ).map(MedicationScheduleItemModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<List<MedicationScheduleItemModel>>> getUpcoming({
    int days = 7,
  }) {
    return _client.get<List<MedicationScheduleItemModel>>(
      ApiEndpoints.medicationUpcoming,
      queryParameters: {'days': days},
      parser: (raw) => _parseList(
        raw,
      ).map(MedicationScheduleItemModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<List<MedicationLogModel>>> getLogs({
    int? reminderId,
    int perPage = 20,
  }) {
    return _client.get<List<MedicationLogModel>>(
      ApiEndpoints.medicationLogs,
      queryParameters: {
        'per_page': perPage,
        if (reminderId != null) 'medication_reminder_id': reminderId,
      },
      parser: (raw) => _parseList(
        raw,
      ).map(MedicationLogModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<MedicationLogModel>> createLog(
    int reminderId,
    CreateMedicationLogRequest request,
  ) {
    return _client.post<MedicationLogModel>(
      ApiEndpoints.medicationReminderLogs(reminderId),
      data: request.toJson(),
      parser: (raw) => MedicationLogModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<MedicationLogModel>> markTaken(
    int reminderId,
    QuickMedicationLogRequest request,
  ) {
    return _client.post<MedicationLogModel>(
      ApiEndpoints.medicationTaken(reminderId),
      data: request.toJson(),
      parser: (raw) => MedicationLogModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<MedicationLogModel>> markSkipped(
    int reminderId,
    QuickMedicationLogRequest request,
  ) {
    return _client.post<MedicationLogModel>(
      ApiEndpoints.medicationSkipped(reminderId),
      data: request.toJson(),
      parser: (raw) => MedicationLogModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<MedicationAdherenceModel>> getAdherence() {
    return _client.get<MedicationAdherenceModel>(
      ApiEndpoints.medicationAdherence,
      parser: (raw) => MedicationAdherenceModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<List<MedicationRefillEventModel>>> getRefills() {
    return _client.get<List<MedicationRefillEventModel>>(
      ApiEndpoints.medicationRefills,
      parser: (raw) => _parseList(
        raw,
      ).map(MedicationRefillEventModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<MedicationRefillEventModel>> recordRefillDone(
    int reminderId, {
    String? notes,
  }) {
    return _refillAction(ApiEndpoints.medicationRefillDone(reminderId), notes);
  }

  Future<ApiResult<MedicationRefillEventModel>> recordRefillSkipped(
    int reminderId, {
    String? notes,
  }) {
    return _refillAction(
      ApiEndpoints.medicationRefillSkipped(reminderId),
      notes,
    );
  }

  Future<ApiResult<MedicationRefillEventModel>> _refillAction(
    String path,
    String? notes,
  ) {
    return _client.post<MedicationRefillEventModel>(
      path,
      data: {if (notes?.trim().isNotEmpty == true) 'notes': notes!.trim()},
      parser: (raw) => MedicationRefillEventModel.fromJson(_unwrapMap(raw)),
    );
  }

  static List<Map<String, dynamic>> _parseList(Object? raw) {
    final value = _unwrapCollection(raw);
    if (value is! List) return const [];
    return value
        .whereType<Map>()
        .map(
          (item) => item.map((key, value) => MapEntry(key.toString(), value)),
        )
        .toList(growable: false);
  }

  static Object? _unwrapCollection(Object? raw) {
    if (raw is Map) {
      return raw['data'] ??
          raw['items'] ??
          raw['reminders'] ??
          raw['logs'] ??
          raw['events'];
    }
    return raw;
  }

  static Map<String, dynamic> _unwrapMap(Object? raw) {
    if (raw is Map<String, dynamic>) {
      final nested =
          raw['data'] ??
          raw['reminder'] ??
          raw['log'] ??
          raw['event'] ??
          raw['adherence'];
      if (nested is Map<String, dynamic>) return nested;
      if (nested is Map) {
        return nested.map((key, value) => MapEntry(key.toString(), value));
      }
      return raw;
    }
    if (raw is Map) {
      return raw.map((key, value) => MapEntry(key.toString(), value));
    }
    return const {};
  }
}
