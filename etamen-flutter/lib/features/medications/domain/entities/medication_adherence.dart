class MedicationAdherence {
  const MedicationAdherence({
    this.totalScheduled = 0,
    this.takenCount = 0,
    this.skippedCount = 0,
    this.missedCount = 0,
    this.adherencePercentage = 0,
    this.from,
    this.to,
    this.byReminder = const [],
    this.disclaimer,
  });

  final int totalScheduled;
  final int takenCount;
  final int skippedCount;
  final int missedCount;
  final double adherencePercentage;
  final String? from;
  final String? to;
  final List<Map<String, dynamic>> byReminder;
  final String? disclaimer;
}
