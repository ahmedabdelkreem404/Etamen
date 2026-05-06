import 'package:etamen_app/features/health/domain/entities/health_profile.dart';

class HealthProfileModel extends HealthProfile {
  const HealthProfileModel({
    super.id,
    super.gender,
    super.birthDate,
    super.heightCm,
    super.weightKg,
    super.bloodType,
    super.chronicDiseases,
    super.allergies,
    super.currentMedications,
    super.surgeries,
    super.goals,
    super.emergencyContactName,
    super.emergencyContactPhone,
    super.notes,
    super.createdAt,
    super.updatedAt,
  });

  factory HealthProfileModel.fromJson(Map<String, dynamic> json) {
    return HealthProfileModel(
      id: _toInt(json['id']),
      gender: json['gender']?.toString(),
      birthDate: (json['date_of_birth'] ?? json['birth_date'])?.toString(),
      heightCm: _toStringNumber(json['height_cm']),
      weightKg: _toStringNumber(json['weight_kg']),
      bloodType: json['blood_type']?.toString(),
      chronicDiseases: _parseNames(
        json['chronic_diseases'] ?? json['active_chronic_diseases'],
      ),
      allergies: _parseNames(json['allergies'] ?? json['active_allergies']),
      currentMedications: _parseNames(
        json['current_medications'] ?? json['active_current_medications'],
      ),
      surgeries: _parseNames(json['surgeries']),
      goals: _parseNames(json['goals'] ?? json['health_goals']),
      emergencyContactName: json['emergency_contact_name']?.toString(),
      emergencyContactPhone: json['emergency_contact_phone']?.toString(),
      notes: json['notes']?.toString(),
      createdAt: _toDateTime(json['created_at']),
      updatedAt: _toDateTime(json['updated_at']),
    );
  }

  static List<String> _parseNames(Object? value) {
    if (value is! List) return const [];
    return value
        .map((item) {
          if (item is Map) {
            return item['name_ar'] ??
                item['name_en'] ??
                item['name'] ??
                item['title'] ??
                item['medication_name'];
          }
          return item;
        })
        .where((item) => item != null && item.toString().trim().isNotEmpty)
        .map((item) => item.toString())
        .toList(growable: false);
  }

  static int? _toInt(Object? value) {
    if (value == null) return null;
    if (value is num) return value.toInt();
    return int.tryParse(value.toString());
  }

  static String? _toStringNumber(Object? value) {
    if (value == null) return null;
    return value.toString();
  }

  static DateTime? _toDateTime(Object? value) {
    if (value == null) return null;
    return DateTime.tryParse(value.toString());
  }
}
