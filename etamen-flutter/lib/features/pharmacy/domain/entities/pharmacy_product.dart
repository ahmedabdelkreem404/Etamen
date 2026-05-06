class PharmacyProduct {
  const PharmacyProduct({
    required this.id,
    required this.name,
    required this.price,
    required this.currency,
    required this.requiresPrescription,
    required this.isActive,
    this.pharmacyId,
    this.nameAr,
    this.nameEn,
    this.description,
    this.imageUrl,
    this.stockStatus,
    this.category,
  });

  final int id;
  final int? pharmacyId;
  final String name;
  final String? nameAr;
  final String? nameEn;
  final String? description;
  final String price;
  final String currency;
  final bool requiresPrescription;
  final bool isActive;
  final String? imageUrl;
  final String? stockStatus;
  final String? category;
}
