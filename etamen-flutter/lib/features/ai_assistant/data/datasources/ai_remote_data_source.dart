import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/network/api_client.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/ai_assistant/data/models/ai_context_preview_model.dart';
import 'package:etamen_app/features/ai_assistant/data/models/ai_conversation_model.dart';
import 'package:etamen_app/features/ai_assistant/data/models/ai_message_model.dart';
import 'package:etamen_app/features/ai_assistant/data/models/create_ai_conversation_request.dart';
import 'package:etamen_app/features/ai_assistant/data/models/send_ai_message_request.dart';
import 'package:etamen_app/features/ai_assistant/data/models/toggle_ai_context_request.dart';
import 'package:etamen_app/features/ai_assistant/data/models/update_ai_conversation_request.dart';
import 'package:etamen_app/features/ai_assistant/domain/repositories/ai_repository.dart';

class AiRemoteDataSource {
  const AiRemoteDataSource(this._client);

  final ApiClient _client;

  Future<ApiResult<List<AiConversationModel>>> getConversations() {
    return _client.get<List<AiConversationModel>>(
      ApiEndpoints.aiConversations,
      queryParameters: const {'per_page': 20},
      parser: (raw) => _parseList(
        raw,
      ).map(AiConversationModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<AiConversationModel>> createConversation(
    CreateAiConversationRequest request,
  ) {
    return _client.post<AiConversationModel>(
      ApiEndpoints.aiConversations,
      data: request.toJson(),
      parser: (raw) => AiConversationModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<AiConversationModel>> getConversationDetails(int id) {
    return _client.get<AiConversationModel>(
      ApiEndpoints.aiConversation(id),
      parser: (raw) => AiConversationModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<AiConversationModel>> updateConversation(
    int id,
    UpdateAiConversationRequest request,
  ) {
    return _client.put<AiConversationModel>(
      ApiEndpoints.aiConversation(id),
      data: request.toJson(),
      parser: (raw) => AiConversationModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<AiConversationModel>> deleteConversation(int id) {
    return _client.delete<AiConversationModel>(
      ApiEndpoints.aiConversation(id),
      parser: (raw) => AiConversationModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<List<AiMessageModel>>> getMessages(int conversationId) {
    return _client.get<List<AiMessageModel>>(
      ApiEndpoints.aiConversationMessages(conversationId),
      queryParameters: const {'per_page': 50},
      parser: (raw) =>
          _parseList(raw).map(AiMessageModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<AiMessageModel>> sendMessage(
    int conversationId,
    SendAiMessageRequest request,
  ) {
    return _client.post<AiMessageModel>(
      ApiEndpoints.aiConversationMessages(conversationId),
      data: request.toJson(),
      parser: (raw) => AiMessageModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<AiQuickAskResult>> quickAsk(SendAiMessageRequest request) {
    return _client.post<AiQuickAskResult>(
      ApiEndpoints.aiAsk,
      data: request.toJson(),
      parser: (raw) {
        final map = _unwrapMap(raw);
        return AiQuickAskResult(
          conversation: AiConversationModel.fromJson(
            _unwrapNestedMap(map['conversation']),
          ),
          message: AiMessageModel.fromJson(_unwrapNestedMap(map['message'])),
        );
      },
    );
  }

  Future<ApiResult<AiContextPreviewModel>> getContextPreview() {
    return _client.get<AiContextPreviewModel>(
      ApiEndpoints.aiContextPreview,
      parser: (raw) => AiContextPreviewModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<AiConversationModel>> toggleContext(
    int conversationId,
    ToggleAiContextRequest request,
  ) {
    return _client.post<AiConversationModel>(
      ApiEndpoints.aiToggleContext(conversationId),
      data: request.toJson(),
      parser: (raw) => AiConversationModel.fromJson(_unwrapMap(raw)),
    );
  }

  static List<Map<String, dynamic>> _parseList(Object? raw) {
    final value = _unwrapCollection(raw);
    if (value is! List) return const [];
    return value
        .whereType<Map>()
        .map(
          (item) => item.map((key, value) => MapEntry(key.toString(), value)),
        )
        .toList(growable: false);
  }

  static Object? _unwrapCollection(Object? raw) {
    if (raw is Map) {
      return raw['data'] ??
          raw['items'] ??
          raw['conversations'] ??
          raw['messages'];
    }
    return raw;
  }

  static Map<String, dynamic> _unwrapMap(Object? raw) {
    if (raw is Map<String, dynamic>) {
      final nested =
          raw['data'] ??
          raw['conversation'] ??
          raw['message'] ??
          raw['context'];
      if (nested is Map<String, dynamic>) return nested;
      if (nested is Map) {
        return nested.map((key, value) => MapEntry(key.toString(), value));
      }
      return raw;
    }
    if (raw is Map) {
      return raw.map((key, value) => MapEntry(key.toString(), value));
    }
    return const {};
  }

  static Map<String, dynamic> _unwrapNestedMap(Object? raw) {
    if (raw is Map<String, dynamic>) {
      final nested = raw['data'];
      if (nested is Map<String, dynamic>) return nested;
      if (nested is Map) {
        return nested.map((key, value) => MapEntry(key.toString(), value));
      }
      return raw;
    }
    if (raw is Map) {
      return raw.map((key, value) => MapEntry(key.toString(), value));
    }
    return const {};
  }
}
