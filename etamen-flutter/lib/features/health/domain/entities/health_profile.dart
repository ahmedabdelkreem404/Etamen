class HealthProfile {
  const HealthProfile({
    this.id,
    this.gender,
    this.birthDate,
    this.heightCm,
    this.weightKg,
    this.bloodType,
    this.chronicDiseases = const [],
    this.allergies = const [],
    this.currentMedications = const [],
    this.surgeries = const [],
    this.goals = const [],
    this.emergencyContactName,
    this.emergencyContactPhone,
    this.notes,
    this.createdAt,
    this.updatedAt,
  });

  final int? id;
  final String? gender;
  final String? birthDate;
  final String? heightCm;
  final String? weightKg;
  final String? bloodType;
  final List<String> chronicDiseases;
  final List<String> allergies;
  final List<String> currentMedications;
  final List<String> surgeries;
  final List<String> goals;
  final String? emergencyContactName;
  final String? emergencyContactPhone;
  final String? notes;
  final DateTime? createdAt;
  final DateTime? updatedAt;
}
