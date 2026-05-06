enum VitalType {
  bloodPressure('blood_pressure'),
  bloodSugar('blood_sugar'),
  heartRate('heart_rate'),
  oxygen('oxygen_saturation'),
  temperature('temperature'),
  weight('weight'),
  sleep('sleep'),
  mood('mood'),
  symptoms('symptom'),
  unknown('unknown');

  const VitalType(this.wireValue);

  final String wireValue;

  static VitalType fromWire(Object? value) {
    final raw = value?.toString().trim().toLowerCase();
    return VitalType.values.firstWhere(
      (item) => item.wireValue == raw || item.name.toLowerCase() == raw,
      orElse: () => VitalType.unknown,
    );
  }
}

enum VitalFlag {
  veryLow('very_low'),
  low('low'),
  normal('normal'),
  high('high'),
  veryHigh('very_high'),
  unknown('unknown');

  const VitalFlag(this.wireValue);

  final String wireValue;

  static VitalFlag fromWire(Object? value) {
    final raw = value?.toString().trim().toLowerCase();
    return VitalFlag.values.firstWhere(
      (item) => item.wireValue == raw || item.name.toLowerCase() == raw,
      orElse: () => VitalFlag.unknown,
    );
  }
}

enum BloodSugarContext {
  fasting('fasting'),
  afterMeal('after_meal'),
  random('random'),
  beforeMeal('before_meal'),
  bedtime('before_sleep'),
  unknown('unknown');

  const BloodSugarContext(this.wireValue);

  final String wireValue;

  static BloodSugarContext fromWire(Object? value) {
    final raw = value?.toString().trim().toLowerCase();
    return BloodSugarContext.values.firstWhere(
      (item) => item.wireValue == raw || item.name.toLowerCase() == raw,
      orElse: () => BloodSugarContext.unknown,
    );
  }
}

enum Mood {
  veryBad('very_bad'),
  bad('bad'),
  neutral('neutral'),
  good('good'),
  veryGood('very_good'),
  unknown('unknown');

  const Mood(this.wireValue);

  final String wireValue;

  static Mood fromWire(Object? value) {
    final raw = value?.toString().trim().toLowerCase();
    return Mood.values.firstWhere(
      (item) => item.wireValue == raw || item.name.toLowerCase() == raw,
      orElse: () => Mood.unknown,
    );
  }
}

class VitalRecord {
  const VitalRecord({
    required this.id,
    required this.vitalType,
    this.measuredAt,
    this.value,
    this.secondaryValue,
    this.unit,
    this.flag = VitalFlag.unknown,
    this.notes,
    this.metadata = const {},
    this.safeMessage,
    this.createdAt,
  });

  final int id;
  final VitalType vitalType;
  final DateTime? measuredAt;
  final String? value;
  final String? secondaryValue;
  final String? unit;
  final VitalFlag flag;
  final String? notes;
  final Map<String, dynamic> metadata;
  final String? safeMessage;
  final DateTime? createdAt;

  String? get systolic => vitalType == VitalType.bloodPressure ? value : null;

  String? get diastolic =>
      vitalType == VitalType.bloodPressure ? secondaryValue : null;

  String? get bloodSugarValue =>
      vitalType == VitalType.bloodSugar ? value : null;

  BloodSugarContext get bloodSugarContext =>
      BloodSugarContext.fromWire(metadata['context']);

  String? get heartRate => vitalType == VitalType.heartRate ? value : null;

  String? get oxygenSaturation => vitalType == VitalType.oxygen ? value : null;

  String? get temperature => vitalType == VitalType.temperature ? value : null;

  String? get weight => vitalType == VitalType.weight ? value : null;

  String? get sleepHours => vitalType == VitalType.sleep ? value : null;

  String? get sleepQuality => metadata['quality']?.toString();

  Mood get mood => Mood.fromWire(metadata['mood']);

  String? get symptomsNotes => vitalType == VitalType.symptoms
      ? notes ?? metadata['symptoms']?.toString()
      : null;

  String get formattedValue {
    if (vitalType == VitalType.bloodPressure) {
      final first = value ?? '-';
      final second = secondaryValue ?? '-';
      return '$first/$second ${unit ?? ''}'.trim();
    }
    if (vitalType == VitalType.symptoms) {
      return symptomsNotes?.trim().isNotEmpty == true ? symptomsNotes! : '-';
    }
    if (vitalType == VitalType.mood) {
      return metadata['mood']?.toString() ?? '-';
    }
    return '${value ?? '-'} ${unit ?? ''}'.trim();
  }
}
