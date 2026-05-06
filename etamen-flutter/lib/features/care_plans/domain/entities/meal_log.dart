import 'package:etamen_app/features/care_plans/domain/entities/care_plan_meal.dart';

class MealLog {
  const MealLog({
    required this.id,
    required this.carePlanId,
    required this.status,
    this.carePlanMealId,
    this.loggedAt,
    this.mealType,
    this.description,
    this.photoUrl,
    this.notes,
    this.createdAt,
  });

  final int id;
  final int carePlanId;
  final int? carePlanMealId;
  final DateTime? loggedAt;
  final MealType? mealType;
  final MealLogStatus status;
  final String? description;
  final String? photoUrl;
  final String? notes;
  final DateTime? createdAt;
}

enum MealLogStatus {
  followed('followed'),
  partiallyFollowed('partially_followed'),
  skipped('skipped'),
  replaced('replaced'),
  extraMeal('extra_meal'),
  unknown('unknown');

  const MealLogStatus(this.wireValue);

  final String wireValue;

  static MealLogStatus fromWire(Object? value) {
    final normalized = value?.toString();
    return MealLogStatus.values.firstWhere(
      (item) => item.wireValue == normalized,
      orElse: () => MealLogStatus.unknown,
    );
  }
}
