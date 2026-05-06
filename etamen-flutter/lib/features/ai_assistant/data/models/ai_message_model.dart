import 'package:etamen_app/features/ai_assistant/data/models/ai_message_metadata_sanitizer.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_message.dart';

class AiMessageModel extends AiMessage {
  AiMessageModel({
    required super.id,
    required super.conversationId,
    required super.role,
    required super.content,
    required super.safetyClassification,
    super.wasRefused,
    super.provider,
    super.createdAt,
    super.updatedAt,
    super.metadata,
  });

  factory AiMessageModel.fromJson(Map<String, dynamic> json) {
    return AiMessageModel(
      id: _toInt(json['id']) ?? 0,
      conversationId: _toInt(json['conversation_id']) ?? 0,
      role: AiMessageRole.fromWire(json['role']),
      content: _string(json['content']) ?? '',
      safetyClassification: AiSafetyClassification.fromWire(
        json['safety_classification'],
      ),
      wasRefused: _toBool(json['was_refused']) ?? false,
      provider: _string(json['provider']),
      metadata: const AiMessageMetadataSanitizer().sanitize(
        _asMap(json['metadata']) ?? const {},
      ),
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

Map<String, dynamic>? _asMap(Object? value) {
  if (value is Map<String, dynamic>) return value;
  if (value is Map) {
    return value.map((key, value) => MapEntry(key.toString(), value));
  }
  return null;
}
