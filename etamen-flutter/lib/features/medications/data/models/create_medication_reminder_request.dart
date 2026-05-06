import 'package:etamen_app/features/medications/domain/entities/medication_reminder.dart';

class CreateMedicationReminderRequest {
  const CreateMedicationReminderRequest({
    required this.medicationName,
    required this.frequencyType,
    required this.startDate,
    this.dosage,
    this.dosageUnit,
    this.instructions,
    this.intervalHours,
    this.endDate,
    this.timezone = 'Africa/Cairo',
    this.prescribedBy,
    this.notes,
    this.refillEnabled = false,
    this.refillQuantity,
    this.refillThreshold,
    this.refillReminderDate,
    this.times = const [],
    this.daysOfWeek = const [],
  });

  final String medicationName;
  final String? dosage;
  final String? dosageUnit;
  final String? instructions;
  final MedicationFrequencyType frequencyType;
  final int? intervalHours;
  final String startDate;
  final String? endDate;
  final String timezone;
  final String? prescribedBy;
  final String? notes;
  final bool refillEnabled;
  final int? refillQuantity;
  final int? refillThreshold;
  final String? refillReminderDate;
  final List<ReminderTimeInput> times;
  final List<int> daysOfWeek;

  Map<String, dynamic> toJson() {
    final json = <String, dynamic>{
      'medication_name': medicationName.trim(),
      'frequency_type': frequencyType.wireValue,
      'start_date': startDate,
      'timezone': timezone,
      'refill_enabled': refillEnabled,
    };
    void putString(String key, String? value) {
      if (value?.trim().isNotEmpty == true) json[key] = value!.trim();
    }

    putString('dosage', dosage);
    putString('dosage_unit', dosageUnit);
    putString('instructions', instructions);
    putString('end_date', endDate);
    putString('prescribed_by', prescribedBy);
    putString('notes', notes);
    putString('refill_reminder_date', refillReminderDate);
    if (intervalHours != null) json['interval_hours'] = intervalHours;
    if (refillQuantity != null) json['refill_quantity'] = refillQuantity;
    if (refillThreshold != null) json['refill_threshold'] = refillThreshold;
    if (times.isNotEmpty) {
      json['times'] = times
          .map((item) => item.toJson())
          .toList(growable: false);
    }
    if (daysOfWeek.isNotEmpty) {
      json['metadata'] = {'days_of_week': daysOfWeek};
    }
    return json;
  }
}

class ReminderTimeInput {
  const ReminderTimeInput({required this.timeOfDay, this.label});

  final String timeOfDay;
  final String? label;

  Map<String, dynamic> toJson() {
    return {
      'time_of_day': timeOfDay,
      if (label?.trim().isNotEmpty == true) 'label': label!.trim(),
    };
  }
}

class MedicationReminderValidator {
  const MedicationReminderValidator._();

  static String? validate(CreateMedicationReminderRequest request) {
    if (request.medicationName.trim().isEmpty) return 'اسم الدواء مطلوب';
    final count = request.times.length;
    switch (request.frequencyType) {
      case MedicationFrequencyType.onceDaily:
        if (count != 1) return 'هذا النوع يحتاج وقت تذكير واحد';
        break;
      case MedicationFrequencyType.twiceDaily:
        if (count != 2) return 'هذا النوع يحتاج وقتين للتذكير';
        break;
      case MedicationFrequencyType.threeTimesDaily:
        if (count != 3) return 'هذا النوع يحتاج ثلاثة أوقات للتذكير';
        break;
      case MedicationFrequencyType.customTimes:
        if (count < 1 || count > 10) return 'الأوقات المخصصة من 1 إلى 10';
        break;
      case MedicationFrequencyType.everyXHours:
        final interval = request.intervalHours;
        if (interval == null || interval < 1 || interval > 24) {
          return 'عدد الساعات يجب أن يكون بين 1 و24';
        }
        break;
      case MedicationFrequencyType.specificDays:
        if (count < 1 || request.daysOfWeek.isEmpty) {
          return 'الأيام المحددة تحتاج يومًا واحدًا ووقتًا واحدًا على الأقل';
        }
        break;
      case MedicationFrequencyType.asNeeded:
        break;
      case MedicationFrequencyType.unknown:
        return 'عدد المرات غير صحيح';
    }
    return null;
  }
}
