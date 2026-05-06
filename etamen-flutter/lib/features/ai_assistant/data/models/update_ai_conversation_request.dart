import 'package:etamen_app/features/ai_assistant/domain/entities/ai_conversation.dart';

class UpdateAiConversationRequest {
  const UpdateAiConversationRequest({
    this.title,
    this.status,
    this.contextEnabled,
  });

  final String? title;
  final AiConversationStatus? status;
  final bool? contextEnabled;

  Map<String, dynamic> toJson() {
    return {
      if (title?.trim().isNotEmpty == true) 'title': title!.trim(),
      if (status == AiConversationStatus.active ||
          status == AiConversationStatus.archived)
        'status': status!.wireValue,
      if (contextEnabled != null) 'context_enabled': contextEnabled,
    };
  }
}
