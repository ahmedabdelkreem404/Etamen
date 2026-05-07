import 'package:etamen_app/features/doctors/domain/entities/doctor.dart';

class DoctorModel extends Doctor {
  const DoctorModel({
    required super.id,
    required super.name,
    required super.isActive,
    super.bio,
    super.avatarUrl,
    super.ratingAverage,
    super.reviewsCount,
    super.doctorProfileId,
    super.consultationFee,
    super.yearsOfExperience,
    super.specialties,
    super.branches,
  });

  factory DoctorModel.fromJson(Map<String, dynamic> json) {
    final profile = json['doctor_profile'] is Map<String, dynamic>
        ? json['doctor_profile'] as Map<String, dynamic>
        : <String, dynamic>{};
    final specialties = (profile['specialties'] as List? ?? const [])
        .whereType<Map>()
        .map((item) => (item['name_ar'] ?? item['name_en'] ?? '').toString())
        .where((item) => item.isNotEmpty)
        .toList(growable: false);
    final branches = (json['branches'] as List? ?? const [])
        .whereType<Map>()
        .map((item) {
          final city = item['city'];
          final area = item['area'];
          final cityName = city is Map
              ? city['name_ar'] ?? city['name_en']
              : null;
          final areaName = area is Map
              ? area['name_ar'] ?? area['name_en']
              : null;
          return [areaName, cityName].whereType<Object>().join(' - ');
        })
        .where((item) => item.isNotEmpty)
        .toList();
    if (branches.isEmpty) {
      final primaryLocation = [
        json['primary_branch_name']?.toString(),
        json['primary_area_name']?.toString(),
        json['primary_city_name']?.toString(),
      ].whereType<String>().where((item) => item.trim().isNotEmpty).join(' - ');
      if (primaryLocation.isNotEmpty) {
        branches.add(primaryLocation);
      }
    }

    return DoctorModel(
      id: (json['id'] as num).toInt(),
      name: (json['name_ar'] ?? json['name_en'] ?? '').toString(),
      isActive: json['is_active'] == true,
      bio: (profile['bio_ar'] ?? profile['bio_en'])?.toString(),
      avatarUrl: _stringOrNull(profile['avatar_url']),
      ratingAverage: _doubleOrNull(profile['rating_average']),
      reviewsCount: profile['reviews_count'] == null
          ? 0
          : (profile['reviews_count'] as num).toInt(),
      doctorProfileId: profile['id'] == null
          ? null
          : (profile['id'] as num).toInt(),
      consultationFee: profile['consultation_fee']?.toString(),
      yearsOfExperience: profile['years_of_experience'] == null
          ? null
          : (profile['years_of_experience'] as num).toInt(),
      specialties: specialties,
      branches: branches,
    );
  }

  static String? _stringOrNull(Object? value) {
    final text = value?.toString().trim();
    if (text == null || text.isEmpty) {
      return null;
    }
    return text;
  }

  static double? _doubleOrNull(Object? value) {
    if (value == null) {
      return null;
    }
    if (value is num) {
      return value.toDouble();
    }
    return double.tryParse(value.toString());
  }
}
