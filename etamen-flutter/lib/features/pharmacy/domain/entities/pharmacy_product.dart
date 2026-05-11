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
    this.stockQuantity,
    this.serverInStock,
    this.stockLabelAr,
    this.stockLabelEn,
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
  final int? stockQuantity;
  final bool? serverInStock;
  final String? stockLabelAr;
  final String? stockLabelEn;
  final String? category;

  bool get inStock => serverInStock ?? ((stockQuantity ?? 1) > 0 && isActive);

  String stockLabel(bool isArabic) {
    final serverLabel = isArabic ? stockLabelAr : stockLabelEn;
    if (serverLabel?.trim().isNotEmpty == true) return serverLabel!;
    if (stockQuantity != null) {
      return inStock
          ? (isArabic ? 'متاح: $stockQuantity' : 'In stock: $stockQuantity')
          : (isArabic ? 'غير متاح' : 'Out of stock');
    }
    return inStock
        ? (isArabic ? 'متاح' : 'In stock')
        : (isArabic ? 'غير متاح' : 'Out of stock');
  }
}
