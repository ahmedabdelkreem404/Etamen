import 'package:etamen_app/features/medications/domain/entities/medication_log.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_schedule_item.dart';

class MedicationScheduleItemModel extends MedicationScheduleItem {
  const MedicationScheduleItemModel({
    required super.reminderId,
    required super.medicationName,
    required super.scheduledFor,
    super.dosage,
    super.timeOfDay,
    super.label,
    super.logAction,
    super.canMarkTaken,
    super.canMarkSkipped,
  });

  factory MedicationScheduleItemModel.fromJson(Map<String, dynamic> json) {
    final scheduledFor = _toDateTime(json['scheduled_for']);
    return MedicationScheduleItemModel(
      reminderId:
          _toInt(json['reminder_id'] ?? json['medication_reminder_id']) ?? 0,
      medicationName: (json['medication_name'] ?? '').toString(),
      dosage: json['dosage']?.toString(),
      scheduledFor: scheduledFor,
      timeOfDay:
          (json['time_of_day'] ??
                  (scheduledFor == null ? null : _timeOnly(scheduledFor)))
              ?.toString(),
      label: json['label']?.toString(),
      logAction: MedicationLogAction.fromWire(
        json['log_action'] ?? json['action'] ?? json['status'],
      ),
      canMarkTaken: json['can_mark_taken'] != false,
      canMarkSkipped: json['can_mark_skipped'] != false,
    );
  }

  static String _timeOnly(DateTime value) {
    final local = value.toLocal();
    String two(int number) => number.toString().padLeft(2, '0');
    return '${two(local.hour)}:${two(local.minute)}';
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
