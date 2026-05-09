class RadiologyScanCategory {
  const RadiologyScanCategory({
    required this.id,
    required this.code,
    required this.nameAr,
    this.nameEn,
    this.descriptionAr,
    this.descriptionEn,
    this.isActive = true,
    this.sortOrder = 0,
  });

  final int id;
  final String code;
  final String nameAr;
  final String? nameEn;
  final String? descriptionAr;
  final String? descriptionEn;
  final bool isActive;
  final int sortOrder;

  String name(bool isArabic) {
    if (!isArabic && nameEn?.trim().isNotEmpty == true) return nameEn!.trim();
    return nameAr;
  }

  String? description(bool isArabic) {
    if (!isArabic && descriptionEn?.trim().isNotEmpty == true) {
      return descriptionEn!.trim();
    }
    return descriptionAr;
  }
}
