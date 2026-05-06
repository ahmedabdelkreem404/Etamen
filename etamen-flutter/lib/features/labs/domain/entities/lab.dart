class Lab {
  const Lab({
    required this.id,
    required this.name,
    this.nameAr,
    this.nameEn,
    this.logoUrl,
    this.city,
    this.area,
    this.address,
    this.phone,
    this.isActive,
    this.rating,
    this.workingHours,
  });

  final int id;
  final String name;
  final String? nameAr;
  final String? nameEn;
  final String? logoUrl;
  final String? city;
  final String? area;
  final String? address;
  final String? phone;
  final bool? isActive;
  final String? rating;
  final String? workingHours;

  String get location {
    final parts = [
      area,
      city,
    ].whereType<String>().where((item) => item.trim().isNotEmpty).toList();
    return parts.isEmpty ? (address ?? '') : parts.join(' - ');
  }
}
