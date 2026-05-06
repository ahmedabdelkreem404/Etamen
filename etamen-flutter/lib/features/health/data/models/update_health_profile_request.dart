class UpdateHealthProfileRequest {
  const UpdateHealthProfileRequest({
    this.birthDate,
    this.gender,
    this.heightCm,
    this.weightKg,
    this.bloodType,
    this.emergencyContactName,
    this.emergencyContactPhone,
    this.notes,
  });

  final String? birthDate;
  final String? gender;
  final num? heightCm;
  final num? weightKg;
  final String? bloodType;
  final String? emergencyContactName;
  final String? emergencyContactPhone;
  final String? notes;

  Map<String, dynamic> toJson() {
    final json = <String, dynamic>{};
    if (birthDate?.trim().isNotEmpty == true) {
      json['date_of_birth'] = birthDate!.trim();
    }
    if (gender?.trim().isNotEmpty == true) json['gender'] = gender!.trim();
    if (heightCm != null) json['height_cm'] = heightCm;
    if (weightKg != null) json['weight_kg'] = weightKg;
    if (bloodType?.trim().isNotEmpty == true) {
      json['blood_type'] = bloodType!.trim();
    }
    if (emergencyContactName?.trim().isNotEmpty == true) {
      json['emergency_contact_name'] = emergencyContactName!.trim();
    }
    if (emergencyContactPhone?.trim().isNotEmpty == true) {
      json['emergency_contact_phone'] = emergencyContactPhone!.trim();
    }
    if (notes?.trim().isNotEmpty == true) json['notes'] = notes!.trim();
    return json;
  }
}
