import 'package:etamen_app/features/care_plans/domain/entities/care_plan_meal.dart';
import 'package:etamen_app/features/care_plans/domain/entities/meal_log.dart';

class CreateMealLogRequest {
  const CreateMealLogRequest({
    required this.loggedAt,
    required this.status,
    this.carePlanMealId,
    this.mealType,
    this.description,
    this.notes,
  });

  final DateTime loggedAt;
  final MealLogStatus status;
  final int? carePlanMealId;
  final MealType? mealType;
  final String? description;
  final String? notes;

  Map<String, dynamic> toJson() {
    return {
      'logged_at': loggedAt.toIso8601String(),
      'status': status.wireValue,
      if (carePlanMealId != null) 'care_plan_meal_id': carePlanMealId,
      if (mealType != null && mealType != MealType.unknown)
        'meal_type': mealType!.wireValue,
      if (description?.trim().isNotEmpty == true)
        'description': description!.trim(),
      if (notes?.trim().isNotEmpty == true) 'notes': notes!.trim(),
    };
  }
}
