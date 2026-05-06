import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/ai_assistant/data/models/send_ai_message_request.dart';
import 'package:etamen_app/features/ai_assistant/domain/repositories/ai_repository.dart';

class QuickAskAi {
  const QuickAskAi(this._repository);

  final AiRepository _repository;

  Future<ApiResult<AiQuickAskResult>> call(SendAiMessageRequest request) {
    return _repository.quickAsk(request);
  }
}
