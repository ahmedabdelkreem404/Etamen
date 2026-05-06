import 'package:etamen_app/features/ai_assistant/domain/entities/ai_conversation.dart';

class CreateAiConversationRequest {
  const CreateAiConversationRequest({
    this.title,
    this.language,
    this.contextEnabled,
  });

  final String? title;
  final AiLanguage? language;
  final bool? contextEnabled;

  Map<String, dynamic> toJson() {
    return {
      if (title?.trim().isNotEmpty == true) 'title': title!.trim(),
      if (language != null && language != AiLanguage.unknown)
        'language': language!.wireValue,
      if (contextEnabled != null) 'context_enabled': contextEnabled,
    };
  }
}
