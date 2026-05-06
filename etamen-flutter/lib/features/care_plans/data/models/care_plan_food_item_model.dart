import 'package:etamen_app/features/care_plans/domain/entities/care_plan_food_item.dart';

class CarePlanFoodItemModel extends CarePlanFoodItem {
  const CarePlanFoodItemModel({
    required super.id,
    required super.category,
    required super.name,
    super.notes,
    super.safetyNote,
  });

  factory CarePlanFoodItemModel.fromJson(Map<String, dynamic> json) {
    return CarePlanFoodItemModel(
      id: _toInt(json['id']) ?? 0,
      category: FoodCategory.fromWire(json['category']),
      name: _string(json['name']) ?? '',
      notes: _string(json['notes']),
      safetyNote: _string(json['safety_note']),
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
