import 'package:etamen_app/features/medications/data/models/medication_reminder_time_model.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_reminder.dart';

class MedicationReminderModel extends MedicationReminder {
  const MedicationReminderModel({
    required super.id,
    required super.medicationName,
    required super.frequencyType,
    required super.status,
    super.dosage,
    super.dosageUnit,
    super.instructions,
    super.intervalHours,
    super.startDate,
    super.endDate,
    super.timezone,
    super.prescribedBy,
    super.notes,
    super.refillEnabled,
    super.refillQuantity,
    super.refillThreshold,
    super.refillReminderDate,
    super.times,
    super.disclaimer,
    super.createdAt,
    super.updatedAt,
  });

  factory MedicationReminderModel.fromJson(Map<String, dynamic> json) {
    return MedicationReminderModel(
      id: _toInt(json['id']) ?? 0,
      medicationName: (json['medication_name'] ?? '').toString(),
      dosage: json['dosage']?.toString(),
      dosageUnit: json['dosage_unit']?.toString(),
      instructions: json['instructions']?.toString(),
      frequencyType: MedicationFrequencyType.fromWire(json['frequency_type']),
      intervalHours: _toInt(json['interval_hours']),
      startDate: json['start_date']?.toString(),
      endDate: json['end_date']?.toString(),
      timezone: json['timezone']?.toString(),
      status: MedicationReminderStatus.fromWire(json['status']),
      prescribedBy: json['prescribed_by']?.toString(),
      notes: json['notes']?.toString(),
      refillEnabled: json['refill_enabled'] == true,
      refillQuantity: _toInt(json['refill_quantity']),
      refillThreshold: _toInt(json['refill_threshold']),
      refillReminderDate: json['refill_reminder_date']?.toString(),
      times: _parseTimes(json['times']),
      disclaimer: json['disclaimer']?.toString(),
      createdAt: _toDateTime(json['created_at']),
      updatedAt: _toDateTime(json['updated_at']),
    );
  }

  static List<MedicationReminderTimeModel> _parseTimes(Object? value) {
    final unwrapped = value is Map ? value['data'] ?? value['items'] : value;
    if (unwrapped is! List) return const [];
    return unwrapped
        .whereType<Map>()
        .map(
          (item) => MedicationReminderTimeModel.fromJson(
            item.map((key, value) => MapEntry(key.toString(), value)),
          ),
        )
        .toList(growable: false);
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
