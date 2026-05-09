import 'package:etamen_app/features/radiology/domain/entities/radiology_provider_summary.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_scan_category.dart';

class RadiologyScan {
  const RadiologyScan({
    required this.id,
    required this.providerId,
    required this.categoryId,
    required this.nameAr,
    this.branchId,
    this.nameEn,
    this.descriptionAr,
    this.descriptionEn,
    this.preparationAr,
    this.preparationEn,
    this.durationMinutes,
    this.basePrice,
    this.requiresPreparation = false,
    this.requiresFasting = false,
    this.contrastRequired = false,
    this.homeAvailable = false,
    this.branchAvailable = true,
    this.reportDeliveryEnabled = true,
    this.isActive = true,
    this.category,
    this.provider,
    this.branch,
  });

  final int id;
  final int providerId;
  final int? branchId;
  final int categoryId;
  final String nameAr;
  final String? nameEn;
  final String? descriptionAr;
  final String? descriptionEn;
  final String? preparationAr;
  final String? preparationEn;
  final int? durationMinutes;
  final String? basePrice;
  final bool requiresPreparation;
  final bool requiresFasting;
  final bool contrastRequired;
  final bool homeAvailable;
  final bool branchAvailable;
  final bool reportDeliveryEnabled;
  final bool isActive;
  final RadiologyScanCategory? category;
  final RadiologyProviderSummary? provider;
  final RadiologyBranchSummary? branch;

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

  String? preparation(bool isArabic) {
    if (!isArabic && preparationEn?.trim().isNotEmpty == true) {
      return preparationEn!.trim();
    }
    return preparationAr;
  }

  double get priceValue => double.tryParse(basePrice ?? '') ?? 0;

  String get priceLabel {
    if (basePrice == null || basePrice!.trim().isEmpty) return '-';
    return '$basePrice EGP';
  }
}
