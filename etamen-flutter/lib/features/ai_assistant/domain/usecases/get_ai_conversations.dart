import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_conversation.dart';
import 'package:etamen_app/features/ai_assistant/domain/repositories/ai_repository.dart';

class GetAiConversations {
  const GetAiConversations(this._repository);

  final AiRepository _repository;

  Future<ApiResult<List<AiConversation>>> call() {
    return _repository.getConversations();
  }
}
