import 'package:etamen_app/features/doctors/domain/entities/doctor.dart';

class DoctorModel extends Doctor {
  const DoctorModel({
    required super.id,
    required super.name,
    required super.isActive,
    super.bio,
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
        .toList(growable: false);

    return DoctorModel(
      id: (json['id'] as num).toInt(),
      name: (json['name_ar'] ?? json['name_en'] ?? '').toString(),
      isActive: json['is_active'] == true,
      bio: (profile['bio_ar'] ?? profile['bio_en'])?.toString(),
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
}
