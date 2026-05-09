import 'package:etamen_app/features/radiology/data/models/radiology_json_helpers.dart';
import 'package:etamen_app/features/radiology/data/models/radiology_provider_summary_model.dart';
import 'package:etamen_app/features/radiology/data/models/radiology_scan_category_model.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_scan.dart';

class RadiologyScanModel extends RadiologyScan {
  const RadiologyScanModel({
    required super.id,
    required super.providerId,
    required super.categoryId,
    required super.nameAr,
    super.branchId,
    super.nameEn,
    super.descriptionAr,
    super.descriptionEn,
    super.preparationAr,
    super.preparationEn,
    super.durationMinutes,
    super.basePrice,
    super.requiresPreparation,
    super.requiresFasting,
    super.contrastRequired,
    super.homeAvailable,
    super.branchAvailable,
    super.reportDeliveryEnabled,
    super.isActive,
    super.category,
    super.provider,
    super.branch,
  });

  factory RadiologyScanModel.fromJson(Map<String, dynamic> json) {
    final category = asRadiologyMap(json['category']);
    final provider = asRadiologyMap(json['provider']);
    final branch = asRadiologyMap(json['branch']);

    return RadiologyScanModel(
      id: radiologyInt(json['id']) ?? 0,
      providerId: radiologyInt(json['provider_id']) ?? 0,
      branchId: radiologyInt(json['branch_id']),
      categoryId: radiologyInt(json['radiology_scan_category_id']) ?? 0,
      nameAr: (json['name_ar'] ?? json['name'] ?? json['name_en'] ?? '')
          .toString(),
      nameEn: json['name_en']?.toString(),
      descriptionAr: json['description_ar']?.toString(),
      descriptionEn: json['description_en']?.toString(),
      preparationAr: json['preparation_ar']?.toString(),
      preparationEn: json['preparation_en']?.toString(),
      durationMinutes: radiologyInt(json['duration_minutes']),
      basePrice: json['base_price']?.toString(),
      requiresPreparation: radiologyBool(json['requires_preparation']),
      requiresFasting: radiologyBool(json['requires_fasting']),
      contrastRequired: radiologyBool(json['contrast_required']),
      homeAvailable: radiologyBool(json['home_available']),
      branchAvailable: radiologyBool(
        json['branch_available'],
        defaultValue: true,
      ),
      reportDeliveryEnabled: radiologyBool(
        json['report_delivery_enabled'],
        defaultValue: true,
      ),
      isActive: radiologyBool(json['is_active'], defaultValue: true),
      category: category == null
          ? null
          : RadiologyScanCategoryModel.fromJson(category),
      provider: provider == null
          ? null
          : RadiologyProviderSummaryModel.fromJson(provider),
      branch: branch == null
          ? null
          : RadiologyBranchSummaryModel.fromJson(branch),
    );
  }
}
