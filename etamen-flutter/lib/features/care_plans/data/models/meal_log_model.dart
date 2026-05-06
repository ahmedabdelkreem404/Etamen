import 'package:etamen_app/features/care_plans/domain/entities/care_plan_meal.dart';
import 'package:etamen_app/features/care_plans/domain/entities/meal_log.dart';

class MealLogModel extends MealLog {
  const MealLogModel({
    required super.id,
    required super.carePlanId,
    required super.status,
    super.carePlanMealId,
    super.loggedAt,
    super.mealType,
    super.description,
    super.photoUrl,
    super.notes,
    super.createdAt,
  });

  factory MealLogModel.fromJson(Map<String, dynamic> json) {
    return MealLogModel(
      id: _toInt(json['id']) ?? 0,
      carePlanId: _toInt(json['care_plan_id']) ?? 0,
      carePlanMealId: _toInt(json['care_plan_meal_id']),
      loggedAt: _toDateTime(json['logged_at']),
      mealType: MealType.fromWire(json['meal_type']),
      status: MealLogStatus.fromWire(json['status']),
      description: _string(json['description']),
      photoUrl:
          _string(json['photo_url']) ?? _string(_asMap(json['photo'])?['url']),
      notes: _string(json['notes']),
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

Map<String, dynamic>? _asMap(Object? value) {
  if (value is Map<String, dynamic>) return value;
  if (value is Map) {
    return value.map((key, item) => MapEntry(key.toString(), item));
  }
  return null;
}
