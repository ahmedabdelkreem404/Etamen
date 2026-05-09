import 'package:etamen_app/features/radiology/data/models/radiology_json_helpers.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_provider_summary.dart';

class RadiologyProviderSummaryModel extends RadiologyProviderSummary {
  const RadiologyProviderSummaryModel({
    required super.id,
    required super.nameAr,
    super.nameEn,
    super.type,
    super.primaryBranchName,
    super.primaryAreaName,
    super.primaryCityName,
  });

  factory RadiologyProviderSummaryModel.fromJson(Map<String, dynamic> json) {
    return RadiologyProviderSummaryModel(
      id: radiologyInt(json['id']) ?? 0,
      nameAr: (json['name_ar'] ?? json['name'] ?? json['name_en'] ?? '')
          .toString(),
      nameEn: json['name_en']?.toString(),
      type: json['type']?.toString(),
      primaryBranchName: json['primary_branch_name']?.toString(),
      primaryAreaName: json['primary_area_name']?.toString(),
      primaryCityName: json['primary_city_name']?.toString(),
    );
  }
}

class RadiologyBranchSummaryModel extends RadiologyBranchSummary {
  const RadiologyBranchSummaryModel({
    required super.id,
    super.providerId,
    super.nameAr,
    super.nameEn,
    super.addressAr,
    super.addressEn,
    super.addressLine1,
    super.addressLine2,
    super.district,
    super.cityName,
    super.areaName,
    super.latitude,
    super.longitude,
  });

  factory RadiologyBranchSummaryModel.fromJson(Map<String, dynamic> json) {
    final city = asRadiologyMap(json['city']);
    final area = asRadiologyMap(json['area']);

    return RadiologyBranchSummaryModel(
      id: radiologyInt(json['id']) ?? 0,
      providerId: radiologyInt(json['provider_id']),
      nameAr: json['name_ar']?.toString(),
      nameEn: json['name_en']?.toString(),
      addressAr: json['address_ar']?.toString(),
      addressEn: json['address_en']?.toString(),
      addressLine1: json['address_line_1']?.toString(),
      addressLine2: json['address_line_2']?.toString(),
      district: json['district']?.toString(),
      cityName: (json['city_name'] ?? city?['name_ar'] ?? city?['name_en'])
          ?.toString(),
      areaName: (json['area_name'] ?? area?['name_ar'] ?? area?['name_en'])
          ?.toString(),
      latitude: json['latitude']?.toString(),
      longitude: json['longitude']?.toString(),
    );
  }
}
