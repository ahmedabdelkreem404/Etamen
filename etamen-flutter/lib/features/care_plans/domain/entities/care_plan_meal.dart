class CarePlanMeal {
  const CarePlanMeal({
    required this.id,
    required this.mealType,
    this.carePlanDayId,
    this.title,
    this.description,
    this.calories,
    this.proteinG,
    this.carbsG,
    this.fatG,
    this.instructions,
    this.sortOrder,
    this.isRequired = false,
  });

  final int id;
  final int? carePlanDayId;
  final MealType mealType;
  final String? title;
  final String? description;
  final int? calories;
  final double? proteinG;
  final double? carbsG;
  final double? fatG;
  final String? instructions;
  final int? sortOrder;
  final bool isRequired;
}

enum MealType {
  breakfast('breakfast'),
  snack1('snack_1'),
  lunch('lunch'),
  snack2('snack_2'),
  dinner('dinner'),
  snack3('snack_3'),
  other('other'),
  unknown('unknown');

  const MealType(this.wireValue);

  final String wireValue;

  static MealType fromWire(Object? value) {
    final normalized = value?.toString();
    return MealType.values.firstWhere(
      (item) => item.wireValue == normalized,
      orElse: () => MealType.unknown,
    );
  }
}
