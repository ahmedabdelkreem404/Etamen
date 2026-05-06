class CarePlanFoodItem {
  const CarePlanFoodItem({
    required this.id,
    required this.category,
    required this.name,
    this.notes,
    this.safetyNote,
  });

  final int id;
  final FoodCategory category;
  final String name;
  final String? notes;
  final String? safetyNote;
}

enum FoodCategory {
  allowed('allowed'),
  forbidden('forbidden'),
  limited('limited'),
  recommended('recommended'),
  unknown('unknown');

  const FoodCategory(this.wireValue);

  final String wireValue;

  static FoodCategory fromWire(Object? value) {
    final normalized = value?.toString();
    return FoodCategory.values.firstWhere(
      (item) => item.wireValue == normalized,
      orElse: () => FoodCategory.unknown,
    );
  }
}
