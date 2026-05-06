class CarePlanCheckin {
  const CarePlanCheckin({
    required this.id,
    required this.carePlanId,
    this.checkinDate,
    this.commitmentScore,
    this.energyLevel,
    this.hungerLevel,
    this.sleepQuality,
    this.mood,
    this.symptomsNotes,
    this.generalNotes,
    this.createdAt,
  });

  final int id;
  final int carePlanId;
  final String? checkinDate;
  final int? commitmentScore;
  final int? energyLevel;
  final int? hungerLevel;
  final int? sleepQuality;
  final CheckinMood? mood;
  final String? symptomsNotes;
  final String? generalNotes;
  final DateTime? createdAt;
}

enum CheckinMood {
  veryBad('very_bad'),
  bad('bad'),
  neutral('neutral'),
  good('good'),
  veryGood('very_good'),
  unknown('unknown');

  const CheckinMood(this.wireValue);

  final String wireValue;

  static CheckinMood fromWire(Object? value) {
    final normalized = value?.toString();
    return CheckinMood.values.firstWhere(
      (item) => item.wireValue == normalized,
      orElse: () => CheckinMood.unknown,
    );
  }
}
