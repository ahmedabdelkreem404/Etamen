import 'package:etamen_app/features/care_plans/domain/entities/care_plan_checkin.dart';

class CreateCarePlanCheckinRequest {
  const CreateCarePlanCheckinRequest({
    required this.checkinDate,
    this.commitmentScore,
    this.energyLevel,
    this.hungerLevel,
    this.sleepQuality,
    this.mood,
    this.symptomsNotes,
    this.generalNotes,
  });

  final String checkinDate;
  final int? commitmentScore;
  final int? energyLevel;
  final int? hungerLevel;
  final int? sleepQuality;
  final CheckinMood? mood;
  final String? symptomsNotes;
  final String? generalNotes;

  Map<String, dynamic> toJson() {
    return {
      'checkin_date': checkinDate,
      if (commitmentScore != null) 'commitment_score': commitmentScore,
      if (energyLevel != null) 'energy_level': energyLevel,
      if (hungerLevel != null) 'hunger_level': hungerLevel,
      if (sleepQuality != null) 'sleep_quality': sleepQuality,
      if (mood != null && mood != CheckinMood.unknown) 'mood': mood!.wireValue,
      if (symptomsNotes?.trim().isNotEmpty == true)
        'symptoms_notes': symptomsNotes!.trim(),
      if (generalNotes?.trim().isNotEmpty == true)
        'general_notes': generalNotes!.trim(),
    };
  }
}
