import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/ai_assistant/data/models/create_ai_conversation_request.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_conversation.dart';
import 'package:etamen_app/features/ai_assistant/domain/repositories/ai_repository.dart';

class CreateAiConversation {
  const CreateAiConversation(this._repository);

  final AiRepository _repository;

  Future<ApiResult<AiConversation>> call(CreateAiConversationRequest request) {
    return _repository.createConversation(request);
  }
}
