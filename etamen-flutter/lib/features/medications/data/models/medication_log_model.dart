import 'package:etamen_app/features/medications/domain/entities/medication_log.dart';

class MedicationLogModel extends MedicationLog {
  const MedicationLogModel({
    required super.id,
    required super.medicationReminderId,
    required super.action,
    super.scheduledFor,
    super.takenAt,
    super.notes,
    super.createdAt,
  });

  factory MedicationLogModel.fromJson(Map<String, dynamic> json) {
    return MedicationLogModel(
      id: _toInt(json['id']) ?? 0,
      medicationReminderId: _toInt(json['medication_reminder_id']) ?? 0,
      scheduledFor: _toDateTime(json['scheduled_for']),
      action: MedicationLogAction.fromWire(json['action']),
      takenAt: _toDateTime(json['taken_at']),
      notes: json['notes']?.toString(),
      createdAt: _toDateTime(json['created_at']),
    );
  }

  static int? _toInt(Object? value) {
    if (value == null) return null;
    if (value is num) return value.toInt();
    return int.tryParse(value.toString());
  }

  static DateTime? _toDateTime(Object? value) {
    if (value == null) return null;
    return DateTime.tryParse(value.toString());
  }
}
