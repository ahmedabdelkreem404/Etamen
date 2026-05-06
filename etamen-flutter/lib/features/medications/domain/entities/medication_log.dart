enum MedicationLogAction {
  taken('taken'),
  skipped('skipped'),
  missed('missed'),
  unknown('unknown');

  const MedicationLogAction(this.wireValue);

  final String wireValue;

  static MedicationLogAction fromWire(Object? value) {
    final raw = value?.toString().trim().toLowerCase();
    return MedicationLogAction.values.firstWhere(
      (item) => item.wireValue == raw || item.name.toLowerCase() == raw,
      orElse: () => MedicationLogAction.unknown,
    );
  }
}

class MedicationLog {
  const MedicationLog({
    required this.id,
    required this.medicationReminderId,
    required this.action,
    this.scheduledFor,
    this.takenAt,
    this.notes,
    this.createdAt,
  });

  final int id;
  final int medicationReminderId;
  final DateTime? scheduledFor;
  final MedicationLogAction action;
  final DateTime? takenAt;
  final String? notes;
  final DateTime? createdAt;
}
