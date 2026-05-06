import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_conversation.dart';
import 'package:etamen_app/features/ai_assistant/domain/repositories/ai_repository.dart';

class GetAiConversationDetails {
  const GetAiConversationDetails(this._repository);

  final AiRepository _repository;

  Future<ApiResult<AiConversation>> call(int id) {
    return _repository.getConversationDetails(id);
  }
}
