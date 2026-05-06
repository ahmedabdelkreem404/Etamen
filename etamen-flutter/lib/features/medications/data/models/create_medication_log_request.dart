import 'package:etamen_app/features/medications/domain/entities/medication_log.dart';

class CreateMedicationLogRequest {
  const CreateMedicationLogRequest({
    required this.action,
    required this.scheduledFor,
    this.takenAt,
    this.notes,
  });

  final MedicationLogAction action;
  final DateTime scheduledFor;
  final DateTime? takenAt;
  final String? notes;

  Map<String, dynamic> toJson() {
    if (action == MedicationLogAction.missed ||
        action == MedicationLogAction.unknown) {
      throw StateError('Flutter can only create taken or skipped logs.');
    }
    return {
      'action': action.wireValue,
      'scheduled_for': scheduledFor.toIso8601String(),
      if (takenAt != null) 'taken_at': takenAt!.toIso8601String(),
      if (notes?.trim().isNotEmpty == true) 'notes': notes!.trim(),
    };
  }
}

class QuickMedicationLogRequest {
  const QuickMedicationLogRequest({
    this.scheduledFor,
    this.takenAt,
    this.notes,
  });

  final DateTime? scheduledFor;
  final DateTime? takenAt;
  final String? notes;

  Map<String, dynamic> toJson() {
    return {
      if (scheduledFor != null)
        'scheduled_for': scheduledFor!.toIso8601String(),
      if (takenAt != null) 'taken_at': takenAt!.toIso8601String(),
      if (notes?.trim().isNotEmpty == true) 'notes': notes!.trim(),
    };
  }
}
