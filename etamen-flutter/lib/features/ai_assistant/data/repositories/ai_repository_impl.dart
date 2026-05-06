import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/ai_assistant/data/datasources/ai_remote_data_source.dart';
import 'package:etamen_app/features/ai_assistant/data/models/create_ai_conversation_request.dart';
import 'package:etamen_app/features/ai_assistant/data/models/send_ai_message_request.dart';
import 'package:etamen_app/features/ai_assistant/data/models/toggle_ai_context_request.dart';
import 'package:etamen_app/features/ai_assistant/data/models/update_ai_conversation_request.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_context_preview.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_conversation.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_message.dart';
import 'package:etamen_app/features/ai_assistant/domain/repositories/ai_repository.dart';

class AiRepositoryImpl implements AiRepository {
  const AiRepositoryImpl(this._remote);

  final AiRemoteDataSource _remote;

  @override
  Future<ApiResult<AiConversation>> createConversation(
    CreateAiConversationRequest request,
  ) {
    return _remote.createConversation(request);
  }

  @override
  Future<ApiResult<AiConversation>> deleteConversation(int id) {
    return _remote.deleteConversation(id);
  }

  @override
  Future<ApiResult<AiContextPreview>> getContextPreview() {
    return _remote.getContextPreview();
  }

  @override
  Future<ApiResult<AiConversation>> getConversationDetails(int id) {
    return _remote.getConversationDetails(id);
  }

  @override
  Future<ApiResult<List<AiConversation>>> getConversations() {
    return _remote.getConversations();
  }

  @override
  Future<ApiResult<List<AiMessage>>> getMessages(int conversationId) {
    return _remote.getMessages(conversationId);
  }

  @override
  Future<ApiResult<AiQuickAskResult>> quickAsk(SendAiMessageRequest request) {
    return _remote.quickAsk(request);
  }

  @override
  Future<ApiResult<AiMessage>> sendMessage(
    int conversationId,
    SendAiMessageRequest request,
  ) {
    return _remote.sendMessage(conversationId, request);
  }

  @override
  Future<ApiResult<AiConversation>> toggleContext(
    int conversationId,
    ToggleAiContextRequest request,
  ) {
    return _remote.toggleContext(conversationId, request);
  }

  @override
  Future<ApiResult<AiConversation>> updateConversation(
    int id,
    UpdateAiConversationRequest request,
  ) {
    return _remote.updateConversation(id, request);
  }
}
