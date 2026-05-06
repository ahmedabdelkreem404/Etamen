import 'package:etamen_app/features/ai_assistant/data/models/ai_message_metadata_sanitizer.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_conversation.dart';

class SendAiMessageRequest {
  const SendAiMessageRequest({
    required this.content,
    this.language,
    this.metadata = const {},
  });

  final String content;
  final AiLanguage? language;
  final Map<String, dynamic> metadata;

  Map<String, dynamic> toJson() {
    final safeMetadata = const AiMessageMetadataSanitizer().sanitize(metadata);
    return {
      'content': content.trim(),
      if (language != null && language != AiLanguage.unknown)
        'language': language!.wireValue,
      if (safeMetadata.isNotEmpty) 'metadata': safeMetadata,
    };
  }
}
