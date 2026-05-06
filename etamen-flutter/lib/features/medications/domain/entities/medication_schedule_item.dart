import 'package:etamen_app/features/medications/domain/entities/medication_log.dart';

class MedicationScheduleItem {
  const MedicationScheduleItem({
    required this.reminderId,
    required this.medicationName,
    required this.scheduledFor,
    this.dosage,
    this.timeOfDay,
    this.label,
    this.logAction = MedicationLogAction.unknown,
    this.canMarkTaken = true,
    this.canMarkSkipped = true,
  });

  final int reminderId;
  final String medicationName;
  final String? dosage;
  final DateTime? scheduledFor;
  final String? timeOfDay;
  final String? label;
  final MedicationLogAction logAction;
  final bool canMarkTaken;
  final bool canMarkSkipped;

  bool get isLogged =>
      logAction == MedicationLogAction.taken ||
      logAction == MedicationLogAction.skipped ||
      logAction == MedicationLogAction.missed;
}
