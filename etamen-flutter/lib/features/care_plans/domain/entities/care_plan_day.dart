import 'package:etamen_app/features/care_plans/domain/entities/care_plan_meal.dart';

class CarePlanDay {
  const CarePlanDay({
    required this.id,
    required this.carePlanId,
    this.dayNumber,
    this.dayDate,
    this.title,
    this.instructions,
    this.isActive = true,
    this.meals = const [],
  });

  final int id;
  final int carePlanId;
  final int? dayNumber;
  final String? dayDate;
  final String? title;
  final String? instructions;
  final bool isActive;
  final List<CarePlanMeal> meals;
}
