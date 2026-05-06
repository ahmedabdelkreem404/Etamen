import 'package:etamen_app/features/medications/domain/entities/medication_reminder_time.dart';

class MedicationReminderTimeModel extends MedicationReminderTime {
  const MedicationReminderTimeModel({
    required super.id,
    required super.medicationReminderId,
    required super.timeOfDay,
    super.label,
    super.isActive,
    super.createdAt,
    super.updatedAt,
  });

  factory MedicationReminderTimeModel.fromJson(Map<String, dynamic> json) {
    return MedicationReminderTimeModel(
      id: _toInt(json['id']) ?? 0,
      medicationReminderId: _toInt(json['medication_reminder_id']) ?? 0,
      timeOfDay: (json['time_of_day'] ?? '').toString(),
      label: json['label']?.toString(),
      isActive: json['is_active'] != false,
      createdAt: _toDateTime(json['created_at']),
      updatedAt: _toDateTime(json['updated_at']),
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
