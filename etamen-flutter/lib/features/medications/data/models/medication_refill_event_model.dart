import 'package:etamen_app/features/medications/domain/entities/medication_refill_event.dart';

class MedicationRefillEventModel extends MedicationRefillEvent {
  const MedicationRefillEventModel({
    required super.id,
    required super.medicationReminderId,
    required super.eventType,
    super.eventDate,
    super.notes,
    super.createdAt,
  });

  factory MedicationRefillEventModel.fromJson(Map<String, dynamic> json) {
    return MedicationRefillEventModel(
      id: _toInt(json['id']) ?? 0,
      medicationReminderId: _toInt(json['medication_reminder_id']) ?? 0,
      eventType: MedicationRefillEventType.fromWire(json['event_type']),
      eventDate: json['event_date']?.toString(),
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
