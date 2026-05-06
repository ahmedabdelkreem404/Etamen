enum MedicationRefillEventType {
  refillDue('refill_due'),
  refillDone('refill_done'),
  refillSkipped('refill_skipped'),
  unknown('unknown');

  const MedicationRefillEventType(this.wireValue);

  final String wireValue;

  static MedicationRefillEventType fromWire(Object? value) {
    final raw = value?.toString().trim().toLowerCase();
    return MedicationRefillEventType.values.firstWhere(
      (item) => item.wireValue == raw || item.name.toLowerCase() == raw,
      orElse: () => MedicationRefillEventType.unknown,
    );
  }
}

class MedicationRefillEvent {
  const MedicationRefillEvent({
    required this.id,
    required this.medicationReminderId,
    required this.eventType,
    this.eventDate,
    this.notes,
    this.createdAt,
  });

  final int id;
  final int medicationReminderId;
  final MedicationRefillEventType eventType;
  final String? eventDate;
  final String? notes;
  final DateTime? createdAt;
}
