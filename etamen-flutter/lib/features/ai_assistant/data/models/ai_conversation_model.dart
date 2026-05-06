import 'package:etamen_app/features/ai_assistant/domain/entities/ai_conversation.dart';

class AiConversationModel extends AiConversation {
  const AiConversationModel({
    required super.id,
    required super.status,
    required super.language,
    super.title,
    super.provider,
    super.contextEnabled,
    super.safetyLevel,
    super.safetyDisclaimer,
    super.messagesCount,
    super.lastMessageAt,
    super.createdAt,
    super.updatedAt,
  });

  factory AiConversationModel.fromJson(Map<String, dynamic> json) {
    return AiConversationModel(
      id: _toInt(json['id']) ?? 0,
      title: _string(json['title']),
      status: AiConversationStatus.fromWire(json['status']),
      provider: _string(json['provider']),
      language: AiLanguage.fromWire(json['language']),
      contextEnabled: _toBool(json['context_enabled']) ?? true,
      safetyLevel: _string(json['safety_level']),
      safetyDisclaimer: _string(json['safety_disclaimer']),
      messagesCount: _toInt(json['messages_count']),
      lastMessageAt: _toDateTime(json['last_message_at']),
      createdAt: _toDateTime(json['created_at']),
      updatedAt: _toDateTime(json['updated_at']),
    );
  }
}

int? _toInt(Object? value) {
  if (value == null) return null;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString());
}

bool? _toBool(Object? value) {
  if (value == null) return null;
  if (value is bool) return value;
  if (value is num) return value != 0;
  final text = value.toString().toLowerCase();
  if (text == 'true' || text == '1') return true;
  if (text == 'false' || text == '0') return false;
  return null;
}

String? _string(Object? value) {
  if (value == null) return null;
  final text = value.toString();
  return text.isEmpty ? null : text;
}

DateTime? _toDateTime(Object? value) {
  final text = _string(value);
  return text == null ? null : DateTime.tryParse(text);
}
