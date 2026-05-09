class HospitalBookingContext {
  const HospitalBookingContext({
    required this.hospitalId,
    required this.departmentId,
    this.hospitalDoctorId,
    this.hospitalName,
    this.departmentName,
  });

  final int hospitalId;
  final int departmentId;
  final int? hospitalDoctorId;
  final String? hospitalName;
  final String? departmentName;

  bool get isValid => hospitalId > 0 && departmentId > 0;

  Map<String, dynamic> toRequestJson() {
    return {
      'hospital_provider_id': hospitalId,
      'hospital_department_id': departmentId,
      if (hospitalDoctorId != null) 'hospital_doctor_id': hospitalDoctorId,
    };
  }

  Map<String, String> toQueryParameters() {
    return {
      'hospitalId': hospitalId.toString(),
      'departmentId': departmentId.toString(),
      if (hospitalDoctorId != null)
        'hospitalDoctorId': hospitalDoctorId.toString(),
      if (hospitalName != null && hospitalName!.trim().isNotEmpty)
        'hospitalName': hospitalName!.trim(),
      if (departmentName != null && departmentName!.trim().isNotEmpty)
        'departmentName': departmentName!.trim(),
    };
  }

  static HospitalBookingContext? fromQueryParameters(
    Map<String, String> query,
  ) {
    final hospitalId = int.tryParse(query['hospitalId'] ?? '');
    final departmentId = int.tryParse(query['departmentId'] ?? '');
    if (hospitalId == null || departmentId == null) return null;

    final context = HospitalBookingContext(
      hospitalId: hospitalId,
      departmentId: departmentId,
      hospitalDoctorId: int.tryParse(query['hospitalDoctorId'] ?? ''),
      hospitalName: _clean(query['hospitalName']),
      departmentName: _clean(query['departmentName']),
    );

    return context.isValid ? context : null;
  }

  static String? _clean(String? value) {
    final text = value?.trim();
    if (text == null || text.isEmpty || text == 'null') return null;
    return text;
  }
}
