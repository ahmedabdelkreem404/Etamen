class AiConversation {
  const AiConversation({
    required this.id,
    required this.status,
    required this.language,
    this.title,
    this.provider,
    this.contextEnabled = true,
    this.safetyLevel,
    this.safetyDisclaimer,
    this.messagesCount,
    this.lastMessageAt,
    this.createdAt,
    this.updatedAt,
  });

  final int id;
  final String? title;
  final AiConversationStatus status;
  final String? provider;
  final AiLanguage language;
  final bool contextEnabled;
  final String? safetyLevel;
  final String? safetyDisclaimer;
  final int? messagesCount;
  final DateTime? lastMessageAt;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  bool get isActive => status == AiConversationStatus.active;
}

enum AiConversationStatus {
  active('active'),
  archived('archived'),
  blocked('blocked'),
  unknown('unknown');

  const AiConversationStatus(this.wireValue);

  final String wireValue;

  static AiConversationStatus fromWire(Object? value) {
    final normalized = value?.toString();
    return AiConversationStatus.values.firstWhere(
      (item) => item.wireValue == normalized,
      orElse: () => AiConversationStatus.unknown,
    );
  }
}

enum AiLanguage {
  ar('ar'),
  en('en'),
  unknown('unknown');

  const AiLanguage(this.wireValue);

  final String wireValue;

  static AiLanguage fromWire(Object? value) {
    final normalized = value?.toString();
    return AiLanguage.values.firstWhere(
      (item) => item.wireValue == normalized,
      orElse: () => AiLanguage.unknown,
    );
  }
}
