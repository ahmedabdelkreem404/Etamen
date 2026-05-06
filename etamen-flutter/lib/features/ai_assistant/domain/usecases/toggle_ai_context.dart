import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/ai_assistant/data/models/toggle_ai_context_request.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_conversation.dart';
import 'package:etamen_app/features/ai_assistant/domain/repositories/ai_repository.dart';

class ToggleAiContext {
  const ToggleAiContext(this._repository);

  final AiRepository _repository;

  Future<ApiResult<AiConversation>> call(
    int conversationId,
    ToggleAiContextRequest request,
  ) {
    return _repository.toggleContext(conversationId, request);
  }
}
