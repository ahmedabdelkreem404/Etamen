class MedicationReminderTime {
  const MedicationReminderTime({
    required this.id,
    required this.medicationReminderId,
    required this.timeOfDay,
    this.label,
    this.isActive = true,
    this.createdAt,
    this.updatedAt,
  });

  final int id;
  final int medicationReminderId;
  final String timeOfDay;
  final String? label;
  final bool isActive;
  final DateTime? createdAt;
  final DateTime? updatedAt;
}
