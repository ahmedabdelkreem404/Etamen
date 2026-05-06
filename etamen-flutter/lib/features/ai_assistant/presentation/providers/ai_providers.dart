import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/core/providers/core_providers.dart';
import 'package:etamen_app/features/ai_assistant/data/datasources/ai_remote_data_source.dart';
import 'package:etamen_app/features/ai_assistant/data/models/create_ai_conversation_request.dart';
import 'package:etamen_app/features/ai_assistant/data/models/send_ai_message_request.dart';
import 'package:etamen_app/features/ai_assistant/data/models/toggle_ai_context_request.dart';
import 'package:etamen_app/features/ai_assistant/data/repositories/ai_repository_impl.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_context_preview.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_conversation.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_message.dart';
import 'package:etamen_app/features/ai_assistant/domain/repositories/ai_repository.dart';
import 'package:etamen_app/features/ai_assistant/domain/usecases/create_ai_conversation.dart';
import 'package:etamen_app/features/ai_assistant/domain/usecases/delete_ai_conversation.dart';
import 'package:etamen_app/features/ai_assistant/domain/usecases/get_ai_context_preview.dart';
import 'package:etamen_app/features/ai_assistant/domain/usecases/get_ai_conversation_details.dart';
import 'package:etamen_app/features/ai_assistant/domain/usecases/get_ai_conversations.dart';
import 'package:etamen_app/features/ai_assistant/domain/usecases/get_ai_messages.dart';
import 'package:etamen_app/features/ai_assistant/domain/usecases/send_ai_message.dart';
import 'package:etamen_app/features/ai_assistant/domain/usecases/toggle_ai_context.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final aiRemoteDataSourceProvider = Provider<AiRemoteDataSource>((ref) {
  return AiRemoteDataSource(ref.watch(apiClientProvider));
});

final aiRepositoryProvider = Provider<AiRepository>((ref) {
  return AiRepositoryImpl(ref.watch(aiRemoteDataSourceProvider));
});

class AiConversationsState {
  const AiConversationsState({
    this.items = const [],
    this.isLoading = false,
    this.isSubmitting = false,
    this.error,
  });

  final List<AiConversation> items;
  final bool isLoading;
  final bool isSubmitting;
  final ApiError? error;

  bool get isEmpty => !isLoading && error == null && items.isEmpty;

  AiConversationsState copyWith({
    List<AiConversation>? items,
    bool? isLoading,
    bool? isSubmitting,
    ApiError? error,
    bool clearError = false,
  }) {
    return AiConversationsState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      isSubmitting: isSubmitting ?? this.isSubmitting,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final aiConversationsControllerProvider =
    StateNotifierProvider.autoDispose<
      AiConversationsController,
      AiConversationsState
    >((ref) {
      return AiConversationsController(ref.watch(aiRepositoryProvider))..load();
    });

class AiConversationsController extends StateNotifier<AiConversationsState> {
  AiConversationsController(AiRepository repository)
    : _getConversations = GetAiConversations(repository),
      _createConversation = CreateAiConversation(repository),
      _deleteConversation = DeleteAiConversation(repository),
      super(const AiConversationsState());

  final GetAiConversations _getConversations;
  final CreateAiConversation _createConversation;
  final DeleteAiConversation _deleteConversation;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _getConversations();
    state = result.when(
      success: (items) =>
          state.copyWith(items: items, isLoading: false, clearError: true),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  Future<AiConversation?> createNew({
    AiLanguage language = AiLanguage.ar,
  }) async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await _createConversation(
      CreateAiConversationRequest(language: language, contextEnabled: true),
    );
    return result.when(
      success: (conversation) {
        state = state.copyWith(
          isSubmitting: false,
          items: [conversation, ...state.items],
          clearError: true,
        );
        return conversation;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return null;
      },
    );
  }

  Future<bool> archive(int id) async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await _deleteConversation(id);
    return result.when(
      success: (conversation) {
        state = state.copyWith(
          isSubmitting: false,
          items: state.items
              .map((item) => item.id == id ? conversation : item)
              .toList(growable: false),
          clearError: true,
        );
        return true;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return false;
      },
    );
  }
}

class AiChatState {
  const AiChatState({
    this.conversation,
    this.messages = const [],
    this.isLoading = false,
    this.isSending = false,
    this.error,
    this.rateLimited = false,
    this.providerUnavailable = false,
  });

  final AiConversation? conversation;
  final List<AiMessage> messages;
  final bool isLoading;
  final bool isSending;
  final ApiError? error;
  final bool rateLimited;
  final bool providerUnavailable;

  bool get isEmpty =>
      !isLoading && error == null && messages.isEmpty && !isSending;

  AiMessage? get latestSafetyMessage {
    for (final message in messages.reversed) {
      if (!message.isUser && (message.isEmergency || message.isRefusal)) {
        return message;
      }
    }
    return null;
  }

  AiChatState copyWith({
    AiConversation? conversation,
    List<AiMessage>? messages,
    bool? isLoading,
    bool? isSending,
    ApiError? error,
    bool? rateLimited,
    bool? providerUnavailable,
    bool clearError = false,
  }) {
    return AiChatState(
      conversation: conversation ?? this.conversation,
      messages: messages ?? this.messages,
      isLoading: isLoading ?? this.isLoading,
      isSending: isSending ?? this.isSending,
      error: clearError ? null : error ?? this.error,
      rateLimited: rateLimited ?? this.rateLimited,
      providerUnavailable: providerUnavailable ?? this.providerUnavailable,
    );
  }
}

final aiChatControllerProvider = StateNotifierProvider.autoDispose
    .family<AiChatController, AiChatState, int>((ref, conversationId) {
      return AiChatController(conversationId, ref.watch(aiRepositoryProvider))
        ..load();
    });

class AiChatController extends StateNotifier<AiChatState> {
  AiChatController(this.conversationId, AiRepository repository)
    : _getConversationDetails = GetAiConversationDetails(repository),
      _getMessages = GetAiMessages(repository),
      _sendMessage = SendAiMessage(repository),
      _toggleContext = ToggleAiContext(repository),
      super(const AiChatState());

  final int conversationId;
  final GetAiConversationDetails _getConversationDetails;
  final GetAiMessages _getMessages;
  final SendAiMessage _sendMessage;
  final ToggleAiContext _toggleContext;

  Future<void> load() async {
    state = state.copyWith(
      isLoading: true,
      clearError: true,
      rateLimited: false,
      providerUnavailable: false,
    );
    final conversationResult = await _getConversationDetails(conversationId);
    final messagesResult = await _getMessages(conversationId);

    state = conversationResult.when(
      success: (conversation) => state.copyWith(
        conversation: conversation,
        messages: messagesResult is ApiSuccess<List<AiMessage>>
            ? messagesResult.data
            : const [],
        isLoading: false,
        clearError: true,
      ),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  Future<bool> send(String content) async {
    final trimmed = content.trim();
    if (trimmed.isEmpty) return false;

    state = state.copyWith(
      isSending: true,
      clearError: true,
      rateLimited: false,
      providerUnavailable: false,
    );
    final result = await _sendMessage(
      conversationId,
      SendAiMessageRequest(
        content: trimmed,
        language: state.conversation?.language == AiLanguage.en
            ? AiLanguage.en
            : AiLanguage.ar,
      ),
    );

    return result.when(
      success: (_) async {
        final messagesResult = await _getMessages(conversationId);
        state = messagesResult.when(
          success: (messages) => state.copyWith(
            messages: messages,
            isSending: false,
            clearError: true,
          ),
          failure: (failure) => state.copyWith(
            isSending: false,
            error: failure.error,
            rateLimited: _isRateLimited(failure.error),
            providerUnavailable: _isProviderUnavailable(failure.error),
          ),
        );
        return true;
      },
      failure: (failure) {
        state = state.copyWith(
          isSending: false,
          error: failure.error,
          rateLimited: _isRateLimited(failure.error),
          providerUnavailable: _isProviderUnavailable(failure.error),
        );
        return false;
      },
    );
  }

  Future<bool> toggleContext(bool enabled) async {
    state = state.copyWith(clearError: true);
    final result = await _toggleContext(
      conversationId,
      ToggleAiContextRequest(contextEnabled: enabled),
    );
    return result.when(
      success: (conversation) {
        state = state.copyWith(conversation: conversation, clearError: true);
        return true;
      },
      failure: (failure) {
        state = state.copyWith(error: failure.error);
        return false;
      },
    );
  }
}

class AiContextState {
  const AiContextState({this.preview, this.isLoading = false, this.error});

  final AiContextPreview? preview;
  final bool isLoading;
  final ApiError? error;

  AiContextState copyWith({
    AiContextPreview? preview,
    bool? isLoading,
    ApiError? error,
    bool clearError = false,
  }) {
    return AiContextState(
      preview: preview ?? this.preview,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final aiContextControllerProvider =
    StateNotifierProvider.autoDispose<AiContextController, AiContextState>((
      ref,
    ) {
      return AiContextController(ref.watch(aiRepositoryProvider))..load();
    });

class AiContextController extends StateNotifier<AiContextState> {
  AiContextController(AiRepository repository)
    : _getContextPreview = GetAiContextPreview(repository),
      super(const AiContextState());

  final GetAiContextPreview _getContextPreview;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _getContextPreview();
    state = result.when(
      success: (preview) =>
          state.copyWith(preview: preview, isLoading: false, clearError: true),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }
}

bool _isRateLimited(ApiError error) {
  return error.type == ApiErrorType.rateLimited || error.statusCode == 429;
}

bool _isProviderUnavailable(ApiError error) {
  final message = error.message.toLowerCase();
  return message.contains('غير متاح') ||
      message.contains('unavailable') ||
      message.contains('temporarily');
}
