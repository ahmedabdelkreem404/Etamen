import 'package:etamen_app/features/care_plans/domain/entities/care_plan_checkin.dart';

class CarePlanCheckinModel extends CarePlanCheckin {
  const CarePlanCheckinModel({
    required super.id,
    required super.carePlanId,
    super.checkinDate,
    super.commitmentScore,
    super.energyLevel,
    super.hungerLevel,
    super.sleepQuality,
    super.mood,
    super.symptomsNotes,
    super.generalNotes,
    super.createdAt,
  });

  factory CarePlanCheckinModel.fromJson(Map<String, dynamic> json) {
    return CarePlanCheckinModel(
      id: _toInt(json['id']) ?? 0,
      carePlanId: _toInt(json['care_plan_id']) ?? 0,
      checkinDate: _string(json['checkin_date']),
      commitmentScore: _toInt(json['commitment_score']),
      energyLevel: _toInt(json['energy_level']),
      hungerLevel: _toInt(json['hunger_level']),
      sleepQuality: _toInt(json['sleep_quality']),
      mood: CheckinMood.fromWire(json['mood']),
      symptomsNotes: _string(json['symptoms_notes']),
      generalNotes: _string(json['general_notes']),
      createdAt: _toDateTime(json['created_at']),
    );
  }
}

int? _toInt(Object? value) {
  if (value == null) return null;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString());
}

String? _string(Object? value) {
  if (value == null) return null;
  final text = value.toString();
  return text.isEmpty ? null : text;
}

DateTime? _toDateTime(Object? value) {
  final text = _string(value);
  return text == null ? null : DateTime.tryParse(text);
}
