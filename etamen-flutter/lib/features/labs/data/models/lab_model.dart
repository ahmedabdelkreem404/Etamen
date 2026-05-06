import 'package:etamen_app/features/labs/domain/entities/lab.dart';

class LabModel extends Lab {
  const LabModel({
    required super.id,
    required super.name,
    super.nameAr,
    super.nameEn,
    super.logoUrl,
    super.city,
    super.area,
    super.address,
    super.phone,
    super.isActive,
    super.rating,
    super.workingHours,
  });

  factory LabModel.fromJson(Map<String, dynamic> json) {
    final city = _asMap(json['city']);
    final area = _asMap(json['area']);
    return LabModel(
      id: (json['id'] as num).toInt(),
      name:
          _firstString([
            json['name_ar'],
            json['name_en'],
            json['name'],
            json['display_name'],
          ]) ??
          'Lab',
      nameAr: json['name_ar']?.toString(),
      nameEn: json['name_en']?.toString(),
      logoUrl: (json['logo_url'] ?? json['image_url'])?.toString(),
      city: _firstString([city?['name_ar'], city?['name_en'], json['city']]),
      area: _firstString([area?['name_ar'], area?['name_en'], json['area']]),
      address: (json['address_ar'] ?? json['address_en'] ?? json['address'])
          ?.toString(),
      phone: json['phone']?.toString(),
      isActive: json['is_active'] is bool ? json['is_active'] as bool : null,
      rating: (json['rating_average'] ?? json['rating'])?.toString(),
      workingHours: json['working_hours']?.toString(),
    );
  }

  static Map<String, dynamic>? _asMap(Object? value) {
    if (value is Map<String, dynamic>) return value;
    if (value is Map) {
      return value.map((key, item) => MapEntry(key.toString(), item));
    }
    return null;
  }

  static String? _firstString(List<Object?> values) {
    for (final value in values) {
      final text = value?.toString().trim();
      if (text != null && text.isNotEmpty && text != 'null') return text;
    }
    return null;
  }
}
