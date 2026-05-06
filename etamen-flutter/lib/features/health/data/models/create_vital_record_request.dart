import 'package:etamen_app/features/health/domain/entities/vital_record.dart';

class CreateVitalRecordRequest {
  const CreateVitalRecordRequest({
    required this.vitalType,
    required this.measuredAt,
    this.value,
    this.secondaryValue,
    this.notes,
    this.metadata = const {},
  });

  factory CreateVitalRecordRequest.bloodPressure({
    required DateTime measuredAt,
    required num systolic,
    required num diastolic,
    String? notes,
  }) {
    return CreateVitalRecordRequest(
      vitalType: VitalType.bloodPressure,
      measuredAt: measuredAt,
      value: systolic,
      secondaryValue: diastolic,
      notes: notes,
    );
  }

  factory CreateVitalRecordRequest.bloodSugar({
    required DateTime measuredAt,
    required num value,
    required BloodSugarContext context,
    String? notes,
  }) {
    return CreateVitalRecordRequest(
      vitalType: VitalType.bloodSugar,
      measuredAt: measuredAt,
      value: value,
      notes: notes,
      metadata: {'context': context.wireValue},
    );
  }

  factory CreateVitalRecordRequest.simple({
    required VitalType vitalType,
    required DateTime measuredAt,
    required num value,
    String? notes,
    Map<String, dynamic> metadata = const {},
  }) {
    return CreateVitalRecordRequest(
      vitalType: vitalType,
      measuredAt: measuredAt,
      value: value,
      notes: notes,
      metadata: metadata,
    );
  }

  factory CreateVitalRecordRequest.mood({
    required DateTime measuredAt,
    required Mood mood,
    String? notes,
  }) {
    return CreateVitalRecordRequest(
      vitalType: VitalType.mood,
      measuredAt: measuredAt,
      notes: notes,
      metadata: {'mood': mood.wireValue},
    );
  }

  factory CreateVitalRecordRequest.symptoms({
    required DateTime measuredAt,
    required String symptoms,
    String? notes,
  }) {
    return CreateVitalRecordRequest(
      vitalType: VitalType.symptoms,
      measuredAt: measuredAt,
      notes: notes?.trim().isNotEmpty == true ? notes : symptoms,
      metadata: {'symptoms': symptoms},
    );
  }

  final VitalType vitalType;
  final DateTime measuredAt;
  final num? value;
  final num? secondaryValue;
  final String? notes;
  final Map<String, dynamic> metadata;

  Map<String, dynamic> toJson() {
    final json = <String, dynamic>{
      'vital_type': vitalType.wireValue,
      'measured_at': measuredAt.toIso8601String(),
    };
    if (value != null) json['value_decimal'] = value;
    if (secondaryValue != null) {
      json['value_secondary_decimal'] = secondaryValue;
    }
    if (notes?.trim().isNotEmpty == true) json['notes'] = notes!.trim();
    if (metadata.isNotEmpty) json['metadata'] = metadata;
    return json;
  }
}

class VitalInputValidator {
  const VitalInputValidator._();

  static String? validate(CreateVitalRecordRequest request) {
    final value = request.value?.toDouble();
    final secondary = request.secondaryValue?.toDouble();

    switch (request.vitalType) {
      case VitalType.bloodPressure:
        if (value == null || secondary == null) return _requiredMessage;
        if (!_within(value, 40, 300) || !_within(secondary, 30, 200)) {
          return _rangeMessage;
        }
        break;
      case VitalType.bloodSugar:
        if (value == null) return _requiredMessage;
        if (!_within(value, 20, 800)) return _rangeMessage;
        break;
      case VitalType.heartRate:
        if (value == null) return _requiredMessage;
        if (!_within(value, 20, 250)) return _rangeMessage;
        break;
      case VitalType.oxygen:
        if (value == null) return _requiredMessage;
        if (!_within(value, 50, 100)) return _rangeMessage;
        break;
      case VitalType.temperature:
        if (value == null) return _requiredMessage;
        if (!_within(value, 30, 45)) return _rangeMessage;
        break;
      case VitalType.weight:
        if (value == null) return _requiredMessage;
        if (!_within(value, 1, 400)) return _rangeMessage;
        break;
      case VitalType.sleep:
        if (value == null) return _requiredMessage;
        if (!_within(value, 0, 24)) return _rangeMessage;
        break;
      case VitalType.mood:
        if (Mood.fromWire(request.metadata['mood']) == Mood.unknown) {
          return _requiredMessage;
        }
        break;
      case VitalType.symptoms:
        final symptoms = request.metadata['symptoms']?.toString().trim();
        final notes = request.notes?.trim();
        if ((symptoms == null || symptoms.isEmpty) &&
            (notes == null || notes.isEmpty)) {
          return _requiredMessage;
        }
        break;
      case VitalType.unknown:
        return _requiredMessage;
    }
    return null;
  }

  static const _requiredMessage = 'هذا الحقل مطلوب';
  static const _rangeMessage = 'تأكد من إدخال الرقم بشكل صحيح';

  static bool _within(double value, double min, double max) {
    return value >= min && value <= max;
  }
}
