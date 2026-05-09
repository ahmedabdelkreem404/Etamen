class RadiologyProviderSummary {
  const RadiologyProviderSummary({
    required this.id,
    required this.nameAr,
    this.nameEn,
    this.type,
    this.primaryBranchName,
    this.primaryAreaName,
    this.primaryCityName,
  });

  final int id;
  final String nameAr;
  final String? nameEn;
  final String? type;
  final String? primaryBranchName;
  final String? primaryAreaName;
  final String? primaryCityName;

  String name(bool isArabic) {
    if (!isArabic && nameEn?.trim().isNotEmpty == true) return nameEn!.trim();
    return nameAr;
  }

  String get locationLabel {
    final parts = [primaryAreaName, primaryCityName]
        .where((item) => item?.trim().isNotEmpty == true)
        .map((item) => item!.trim())
        .toList(growable: false);
    return parts.join(' - ');
  }
}

class RadiologyBranchSummary {
  const RadiologyBranchSummary({
    required this.id,
    this.providerId,
    this.nameAr,
    this.nameEn,
    this.addressAr,
    this.addressEn,
    this.addressLine1,
    this.addressLine2,
    this.district,
    this.cityName,
    this.areaName,
    this.latitude,
    this.longitude,
  });

  final int id;
  final int? providerId;
  final String? nameAr;
  final String? nameEn;
  final String? addressAr;
  final String? addressEn;
  final String? addressLine1;
  final String? addressLine2;
  final String? district;
  final String? cityName;
  final String? areaName;
  final String? latitude;
  final String? longitude;

  String name(bool isArabic) {
    if (!isArabic && nameEn?.trim().isNotEmpty == true) return nameEn!.trim();
    if (nameAr?.trim().isNotEmpty == true) return nameAr!.trim();
    return isArabic ? 'فرع المركز' : 'Center branch';
  }

  String address(bool isArabic) {
    final localized = !isArabic && addressEn?.trim().isNotEmpty == true
        ? addressEn
        : addressAr;
    final parts = [localized, addressLine1, district, areaName, cityName]
        .where((item) => item?.trim().isNotEmpty == true)
        .map((item) {
          return item!.trim();
        })
        .toList(growable: false);
    return parts.join(' - ');
  }
}
