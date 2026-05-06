import 'package:etamen_app/features/care_plans/domain/entities/care_plan_meal.dart';

class CarePlanMealModel extends CarePlanMeal {
  const CarePlanMealModel({
    required super.id,
    required super.mealType,
    super.carePlanDayId,
    super.title,
    super.description,
    super.calories,
    super.proteinG,
    super.carbsG,
    super.fatG,
    super.instructions,
    super.sortOrder,
    super.isRequired = false,
  });

  factory CarePlanMealModel.fromJson(Map<String, dynamic> json) {
    return CarePlanMealModel(
      id: _toInt(json['id']) ?? 0,
      carePlanDayId: _toInt(json['care_plan_day_id']),
      mealType: MealType.fromWire(json['meal_type']),
      title: _string(json['title']),
      description: _string(json['description']),
      calories: _toInt(json['calories']),
      proteinG: _toDouble(json['protein_g']),
      carbsG: _toDouble(json['carbs_g']),
      fatG: _toDouble(json['fat_g']),
      instructions: _string(json['instructions']),
      sortOrder: _toInt(json['sort_order']),
      isRequired: _toBool(json['is_required']) ?? false,
    );
  }
}

int? _toInt(Object? value) {
  if (value == null) return null;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString());
}

double? _toDouble(Object? value) {
  if (value == null) return null;
  if (value is num) return value.toDouble();
  return double.tryParse(value.toString());
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
