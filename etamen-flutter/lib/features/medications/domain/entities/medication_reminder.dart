import 'package:etamen_app/features/medications/domain/entities/medication_reminder_time.dart';

enum MedicationReminderStatus {
  active('active'),
  paused('paused'),
  completed('completed'),
  cancelled('cancelled'),
  unknown('unknown');

  const MedicationReminderStatus(this.wireValue);

  final String wireValue;

  static MedicationReminderStatus fromWire(Object? value) {
    final raw = value?.toString().trim().toLowerCase();
    return MedicationReminderStatus.values.firstWhere(
      (item) => item.wireValue == raw || item.name.toLowerCase() == raw,
      orElse: () => MedicationReminderStatus.unknown,
    );
  }
}

enum MedicationFrequencyType {
  onceDaily('once_daily'),
  twiceDaily('twice_daily'),
  threeTimesDaily('three_times_daily'),
  customTimes('custom_times'),
  everyXHours('every_x_hours'),
  specificDays('specific_days'),
  asNeeded('as_needed'),
  unknown('unknown');

  const MedicationFrequencyType(this.wireValue);

  final String wireValue;

  static MedicationFrequencyType fromWire(Object? value) {
    final raw = value?.toString().trim().toLowerCase();
    return MedicationFrequencyType.values.firstWhere(
      (item) => item.wireValue == raw || item.name.toLowerCase() == raw,
      orElse: () => MedicationFrequencyType.unknown,
    );
  }
}

class MedicationReminder {
  const MedicationReminder({
    required this.id,
    required this.medicationName,
    required this.frequencyType,
    required this.status,
    this.dosage,
    this.dosageUnit,
    this.instructions,
    this.intervalHours,
    this.startDate,
    this.endDate,
    this.timezone,
    this.prescribedBy,
    this.notes,
    this.refillEnabled = false,
    this.refillQuantity,
    this.refillThreshold,
    this.refillReminderDate,
    this.times = const [],
    this.disclaimer,
    this.createdAt,
    this.updatedAt,
  });

  final int id;
  final String medicationName;
  final String? dosage;
  final String? dosageUnit;
  final String? instructions;
  final MedicationFrequencyType frequencyType;
  final int? intervalHours;
  final String? startDate;
  final String? endDate;
  final String? timezone;
  final MedicationReminderStatus status;
  final String? prescribedBy;
  final String? notes;
  final bool refillEnabled;
  final int? refillQuantity;
  final int? refillThreshold;
  final String? refillReminderDate;
  final List<MedicationReminderTime> times;
  final String? disclaimer;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  String get dosageText {
    final parts = [dosage, dosageUnit]
        .where((item) => item?.trim().isNotEmpty == true)
        .map((item) => item!.trim())
        .toList(growable: false);
    return parts.join(' ');
  }

  bool get canPause => status == MedicationReminderStatus.active;

  bool get canResume => status == MedicationReminderStatus.paused;

  bool get canCancel =>
      status == MedicationReminderStatus.active ||
      status == MedicationReminderStatus.paused;
}
