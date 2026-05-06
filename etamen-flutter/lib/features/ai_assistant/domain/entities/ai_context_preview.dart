class AiContextPreview {
  const AiContextPreview({
    this.enabled,
    this.age,
    this.gender,
    this.latestVitals = const [],
    this.chronicDiseases = const [],
    this.allergies = const [],
    this.currentMedications = const [],
    this.medicationAdherence,
    this.carePlanSummary = const [],
    this.disclaimer,
    this.privacyNote,
  });

  final bool? enabled;
  final int? age;
  final String? gender;
  final List<Map<String, dynamic>> latestVitals;
  final List<String> chronicDiseases;
  final List<String> allergies;
  final List<String> currentMedications;
  final Map<String, dynamic>? medicationAdherence;
  final List<Map<String, dynamic>> carePlanSummary;
  final String? disclaimer;
  final String? privacyNote;

  bool get hasAnyContext =>
      age != null ||
      gender != null ||
      latestVitals.isNotEmpty ||
      chronicDiseases.isNotEmpty ||
      allergies.isNotEmpty ||
      currentMedications.isNotEmpty ||
      medicationAdherence?.isNotEmpty == true ||
      carePlanSummary.isNotEmpty;
}
