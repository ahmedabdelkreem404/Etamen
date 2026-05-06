class AiMessage {
  const AiMessage({
    required this.id,
    required this.conversationId,
    required this.role,
    required this.content,
    required this.safetyClassification,
    this.wasRefused = false,
    this.provider,
    this.createdAt,
    this.updatedAt,
    this.metadata = const {},
  });

  final int id;
  final int conversationId;
  final AiMessageRole role;
  final String content;
  final AiSafetyClassification safetyClassification;
  final bool wasRefused;
  final String? provider;
  final DateTime? createdAt;
  final DateTime? updatedAt;
  final Map<String, dynamic> metadata;

  bool get isUser => role == AiMessageRole.user;

  bool get isEmergency =>
      safetyClassification == AiSafetyClassification.emergencyRedFlag ||
      safetyClassification == AiSafetyClassification.mentalHealthCrisis;

  bool get isRefusal =>
      wasRefused ||
      safetyClassification == AiSafetyClassification.medicalAdviceRequest ||
      safetyClassification == AiSafetyClassification.diagnosisRequest ||
      safetyClassification == AiSafetyClassification.medicationChangeRequest ||
      safetyClassification == AiSafetyClassification.unsafe;
}

enum AiMessageRole {
  user('user'),
  assistant('assistant'),
  system('system'),
  safety('safety'),
  unknown('unknown');

  const AiMessageRole(this.wireValue);

  final String wireValue;

  static AiMessageRole fromWire(Object? value) {
    final normalized = value?.toString();
    return AiMessageRole.values.firstWhere(
      (item) => item.wireValue == normalized,
      orElse: () => AiMessageRole.unknown,
    );
  }
}

enum AiSafetyClassification {
  safe('safe'),
  medicalAdviceRequest('medical_advice_request'),
  diagnosisRequest('diagnosis_request'),
  medicationChangeRequest('medication_change_request'),
  emergencyRedFlag('emergency_red_flag'),
  mentalHealthCrisis('mental_health_crisis'),
  unsafe('unsafe'),
  unknown('unknown');

  const AiSafetyClassification(this.wireValue);

  final String wireValue;

  static AiSafetyClassification fromWire(Object? value) {
    final normalized = value?.toString();
    return AiSafetyClassification.values.firstWhere(
      (item) => item.wireValue == normalized,
      orElse: () => AiSafetyClassification.unknown,
    );
  }
}
