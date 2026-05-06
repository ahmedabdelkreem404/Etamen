import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/ai_assistant/data/models/send_ai_message_request.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_message.dart';
import 'package:etamen_app/features/ai_assistant/domain/repositories/ai_repository.dart';

class SendAiMessage {
  const SendAiMessage(this._repository);

  final AiRepository _repository;

  Future<ApiResult<AiMessage>> call(
    int conversationId,
    SendAiMessageRequest request,
  ) {
    return _repository.sendMessage(conversationId, request);
  }
}
