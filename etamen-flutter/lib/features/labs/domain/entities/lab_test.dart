class LabTest {
  const LabTest({
    required this.id,
    required this.name,
    required this.price,
    required this.currency,
    required this.isActive,
    this.labId,
    this.nameAr,
    this.nameEn,
    this.description,
    this.sampleType,
    this.preparationInstructions,
    this.resultTimeHours,
  });

  final int id;
  final int? labId;
  final String name;
  final String? nameAr;
  final String? nameEn;
  final String? description;
  final String price;
  final String currency;
  final String? sampleType;
  final String? preparationInstructions;
  final int? resultTimeHours;
  final bool isActive;
}
