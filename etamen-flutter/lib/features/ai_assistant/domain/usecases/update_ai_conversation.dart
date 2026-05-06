import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/ai_assistant/data/models/update_ai_conversation_request.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_conversation.dart';
import 'package:etamen_app/features/ai_assistant/domain/repositories/ai_repository.dart';

class UpdateAiConversation {
  const UpdateAiConversation(this._repository);

  final AiRepository _repository;

  Future<ApiResult<AiConversation>> call(
    int id,
    UpdateAiConversationRequest request,
  ) {
    return _repository.updateConversation(id, request);
  }
}
