import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/ai_assistant/data/models/create_ai_conversation_request.dart';
import 'package:etamen_app/features/ai_assistant/data/models/send_ai_message_request.dart';
import 'package:etamen_app/features/ai_assistant/data/models/toggle_ai_context_request.dart';
import 'package:etamen_app/features/ai_assistant/data/models/update_ai_conversation_request.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_context_preview.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_conversation.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_message.dart';

class AiQuickAskResult {
  const AiQuickAskResult({required this.conversation, required this.message});

  final AiConversation conversation;
  final AiMessage message;
}

abstract class AiRepository {
  Future<ApiResult<List<AiConversation>>> getConversations();

  Future<ApiResult<AiConversation>> createConversation(
    CreateAiConversationRequest request,
  );

  Future<ApiResult<AiConversation>> getConversationDetails(int id);

  Future<ApiResult<AiConversation>> updateConversation(
    int id,
    UpdateAiConversationRequest request,
  );

  Future<ApiResult<AiConversation>> deleteConversation(int id);

  Future<ApiResult<List<AiMessage>>> getMessages(int conversationId);

  Future<ApiResult<AiMessage>> sendMessage(
    int conversationId,
    SendAiMessageRequest request,
  );

  Future<ApiResult<AiQuickAskResult>> quickAsk(SendAiMessageRequest request);

  Future<ApiResult<AiContextPreview>> getContextPreview();

  Future<ApiResult<AiConversation>> toggleContext(
    int conversationId,
    ToggleAiContextRequest request,
  );
}
