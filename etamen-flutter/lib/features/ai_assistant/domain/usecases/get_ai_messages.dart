import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_message.dart';
import 'package:etamen_app/features/ai_assistant/domain/repositories/ai_repository.dart';

class GetAiMessages {
  const GetAiMessages(this._repository);

  final AiRepository _repository;

  Future<ApiResult<List<AiMessage>>> call(int conversationId) {
    return _repository.getMessages(conversationId);
  }
}
