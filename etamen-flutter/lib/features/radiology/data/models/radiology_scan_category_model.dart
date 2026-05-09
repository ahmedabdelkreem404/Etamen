import 'package:etamen_app/features/radiology/data/models/radiology_json_helpers.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_scan_category.dart';

class RadiologyScanCategoryModel extends RadiologyScanCategory {
  const RadiologyScanCategoryModel({
    required super.id,
    required super.code,
    required super.nameAr,
    super.nameEn,
    super.descriptionAr,
    super.descriptionEn,
    super.isActive,
    super.sortOrder,
  });

  factory RadiologyScanCategoryModel.fromJson(Map<String, dynamic> json) {
    return RadiologyScanCategoryModel(
      id: radiologyInt(json['id']) ?? 0,
      code: (json['code'] ?? '').toString(),
      nameAr: (json['name_ar'] ?? json['name'] ?? json['name_en'] ?? '')
          .toString(),
      nameEn: json['name_en']?.toString(),
      descriptionAr: json['description_ar']?.toString(),
      descriptionEn: json['description_en']?.toString(),
      isActive: radiologyBool(json['is_active'], defaultValue: true),
      sortOrder: radiologyInt(json['sort_order']) ?? 0,
    );
  }
}
