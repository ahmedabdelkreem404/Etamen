import 'package:etamen_app/features/care_plans/data/models/care_plan_meal_model.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_day.dart';

class CarePlanDayModel extends CarePlanDay {
  const CarePlanDayModel({
    required super.id,
    required super.carePlanId,
    super.dayNumber,
    super.dayDate,
    super.title,
    super.instructions,
    super.isActive = true,
    super.meals = const [],
  });

  factory CarePlanDayModel.fromJson(Map<String, dynamic> json) {
    return CarePlanDayModel(
      id: _toInt(json['id']) ?? 0,
      carePlanId: _toInt(json['care_plan_id']) ?? 0,
      dayNumber: _toInt(json['day_number']),
      dayDate: _string(json['day_date']),
      title: _string(json['title']),
      instructions: _string(json['instructions']),
      isActive: _toBool(json['is_active']) ?? true,
      meals: _parseList(
        json['meals'],
      ).map(CarePlanMealModel.fromJson).toList(growable: false),
    );
  }
}

List<Map<String, dynamic>> _parseList(Object? value) {
  if (value is! List) return const [];
  return value
      .whereType<Map>()
      .map((item) => item.map((key, value) => MapEntry(key.toString(), value)))
      .toList(growable: false);
}

int? _toInt(Object? value) {
  if (value == null) return null;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString());
}

bool? _toBool(Object? value) {
  if (value == null) return null;
  if (value is bool) return value;
  if (value is num) return value != 0;
  final text = value.toString().toLowerCase();
  if (text == 'true' || text == '1') return true;
  if (text == 'false' || text == '0') return false;
  return null;
}

String? _string(Object? value) {
  if (value == null) return null;
  final text = value.toString();
  return text.isEmpty ? null : text;
}
