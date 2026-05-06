import 'package:etamen_app/features/health/domain/entities/vital_record.dart';

class VitalRecordModel extends VitalRecord {
  const VitalRecordModel({
    required super.id,
    required super.vitalType,
    super.measuredAt,
    super.value,
    super.secondaryValue,
    super.unit,
    super.flag,
    super.notes,
    super.metadata,
    super.safeMessage,
    super.createdAt,
  });

  factory VitalRecordModel.fromJson(Map<String, dynamic> json) {
    return VitalRecordModel(
      id: _toInt(json['id']) ?? 0,
      vitalType: VitalType.fromWire(json['vital_type']),
      measuredAt: _toDateTime(json['measured_at']),
      value: _toStringNumber(
        json['value_decimal'] ??
            json['value'] ??
            json['blood_sugar_value'] ??
            json['heart_rate'] ??
            json['oxygen_saturation'] ??
            json['temperature'] ??
            json['weight'] ??
            json['sleep_hours'],
      ),
      secondaryValue: _toStringNumber(
        json['value_secondary_decimal'] ?? json['diastolic'],
      ),
      unit: json['unit']?.toString(),
      flag: VitalFlag.fromWire(json['flag']),
      notes: (json['notes'] ?? json['symptoms_notes'])?.toString(),
      metadata: _asMap(json['metadata']),
      safeMessage: (json['safe_message'] ?? json['safety_note'])?.toString(),
      createdAt: _toDateTime(json['created_at']),
    );
  }

  static Map<String, dynamic> _asMap(Object? value) {
    if (value is Map<String, dynamic>) return value;
    if (value is Map) {
      return value.map((key, item) => MapEntry(key.toString(), item));
    }
    return const {};
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

  static String? _toStringNumber(Object? value) {
    if (value == null) return null;
    return value.toString();
  }
}
