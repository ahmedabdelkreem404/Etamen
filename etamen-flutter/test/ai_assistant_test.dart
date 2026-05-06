import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/ai_assistant/data/models/ai_context_preview_model.dart';
import 'package:etamen_app/features/ai_assistant/data/models/ai_conversation_model.dart';
import 'package:etamen_app/features/ai_assistant/data/models/ai_message_metadata_sanitizer.dart';
import 'package:etamen_app/features/ai_assistant/data/models/ai_message_model.dart';
import 'package:etamen_app/features/ai_assistant/data/models/create_ai_conversation_request.dart';
import 'package:etamen_app/features/ai_assistant/data/models/send_ai_message_request.dart';
import 'package:etamen_app/features/ai_assistant/data/models/toggle_ai_context_request.dart';
import 'package:etamen_app/features/ai_assistant/data/models/update_ai_conversation_request.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_context_preview.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_conversation.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_message.dart';
import 'package:etamen_app/features/ai_assistant/domain/repositories/ai_repository.dart';
import 'package:etamen_app/features/ai_assistant/presentation/providers/ai_providers.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('AiConversationModel parses nullable backend fields', () {
    final conversation = AiConversationModel.fromJson({
      'id': 4,
      'title': 'Vitals summary',
      'status': 'active',
      'provider': 'deepseek',
      'language': 'ar',
      'context_enabled': true,
      'safety_level': 'normal',
      'messages_count': 3,
      'last_message_at': '2026-05-06T10:00:00Z',
    });

    expect(conversation.id, 4);
    expect(conversation.status, AiConversationStatus.active);
    expect(conversation.language, AiLanguage.ar);
    expect(conversation.contextEnabled, true);
    expect(conversation.messagesCount, 3);
  });

  test('AiMessageModel parses safety fields and sanitizes metadata', () {
    final message = AiMessageModel.fromJson({
      'id': 8,
      'conversation_id': 4,
      'role': 'assistant',
      'content': 'I can help organize information.',
      'safety_classification': 'diagnosis_request',
      'was_refused': true,
      'provider': 'gemini',
      'metadata': {
        'route_key': 'ai',
        'system_prompt': 'hidden',
        'provider_response': 'raw',
        'health_context': {'age': 30},
      },
    });

    expect(message.role, AiMessageRole.assistant);
    expect(
      message.safetyClassification,
      AiSafetyClassification.diagnosisRequest,
    );
    expect(message.isRefusal, true);
    expect(message.metadata['route_key'], 'ai');
    expect(message.metadata.containsKey('system_prompt'), false);
    expect(message.metadata.containsKey('provider_response'), false);
    expect(message.metadata.containsKey('health_context'), false);
  });

  test(
    'AiContextPreviewModel parses backend resource and hides unsafe keys',
    () {
      final preview = AiContextPreviewModel.fromJson({
        'context': {
          'notice': 'Do not diagnose.',
          'profile': {'age': 34, 'gender': 'male'},
          'latest_vitals': [
            {
              'type': 'blood_pressure',
              'value': 120,
              'file_path': '/private.pdf',
            },
          ],
          'active_chronic_diseases': ['Hypertension'],
          'active_allergies': ['Penicillin'],
          'current_medications': ['Medication name only'],
          'medication_adherence_summary': {'taken': 5, 'raw_prompt': 'hidden'},
          'active_care_plans': [
            {'title': 'Nutrition', 'provider_response': 'hidden'},
          ],
        },
        'privacy_note': 'Safe context only.',
      });

      expect(preview.age, 34);
      expect(preview.chronicDiseases, ['Hypertension']);
      expect(preview.latestVitals.first.containsKey('file_path'), false);
      expect(preview.medicationAdherence!.containsKey('raw_prompt'), false);
      expect(
        preview.carePlanSummary.first.containsKey('provider_response'),
        false,
      );
    },
  );

  test('AI enum mappings tolerate unknown values', () {
    expect(
      AiConversationStatus.fromWire('blocked'),
      AiConversationStatus.blocked,
    );
    expect(AiConversationStatus.fromWire('bad'), AiConversationStatus.unknown);
    expect(AiMessageRole.fromWire('safety'), AiMessageRole.safety);
    expect(
      AiSafetyClassification.fromWire('emergency_red_flag'),
      AiSafetyClassification.emergencyRedFlag,
    );
    expect(AiLanguage.fromWire('en'), AiLanguage.en);
  });

  test(
    'CreateAiConversationRequest excludes forbidden ownership and provider fields',
    () {
      final json = const CreateAiConversationRequest(
        title: 'Safe chat',
        language: AiLanguage.ar,
        contextEnabled: true,
      ).toJson();

      expect(json['title'], 'Safe chat');
      expect(json['language'], 'ar');
      expect(json['context_enabled'], true);
      expect(json.containsKey('patient_user_id'), false);
      expect(json.containsKey('user_id'), false);
      expect(json.containsKey('provider'), false);
      expect(json.containsKey('safety_level'), false);
    },
  );

  test(
    'SendAiMessageRequest excludes role provider safety system context and api key',
    () {
      final json = const SendAiMessageRequest(
        content: 'Help me organize questions',
        language: AiLanguage.en,
        metadata: {
          'client': 'flutter',
          'system_prompt': 'hidden',
          'health_context': {'raw': true},
          'api_key': 'secret',
        },
      ).toJson();

      expect(json['content'], 'Help me organize questions');
      expect(json['language'], 'en');
      expect(json['metadata'], {'client': 'flutter'});
      expect(json.containsKey('patient_user_id'), false);
      expect(json.containsKey('user_id'), false);
      expect(json.containsKey('role'), false);
      expect(json.containsKey('provider'), false);
      expect(json.containsKey('safety_classification'), false);
      expect(json.containsKey('was_refused'), false);
      expect(json.containsKey('system_prompt'), false);
      expect(json.containsKey('health_context'), false);
      expect(json.containsKey('api_key'), false);
    },
  );

  test('ToggleAiContextRequest serializes only backend enabled flag', () {
    final json = const ToggleAiContextRequest(contextEnabled: false).toJson();

    expect(json, {'enabled': false});
    expect(json.containsKey('patient_user_id'), false);
    expect(json.containsKey('context_enabled'), false);
  });

  test('UpdateAiConversationRequest permits only safe archive update', () {
    final json = const UpdateAiConversationRequest(
      title: 'Archive me',
      status: AiConversationStatus.archived,
      contextEnabled: false,
    ).toJson();

    expect(json['status'], 'archived');
    expect(json.containsKey('provider'), false);
    expect(json.containsKey('patient_user_id'), false);
  });

  test('AiMessageMetadataSanitizer removes unsafe metadata keys', () {
    final data = const AiMessageMetadataSanitizer().sanitize({
      'safe_entity_id': 5,
      'system': 'hidden',
      'prompt': 'hidden',
      'raw_provider_response': 'hidden',
      'payment': {'id': 1},
      'wallet': 'hidden',
      'private_file_path': '/private',
      'hmac': 'hidden',
      'config': 'hidden',
    });

    expect(data, {'safe_entity_id': 5});
  });

  test(
    'conversation and chat controllers load create send and classify safety',
    () async {
      final repository = FakeAiRepository();
      final conversations = AiConversationsController(repository);
      await conversations.load();
      expect(conversations.state.items, hasLength(1));

      final created = await conversations.createNew();
      expect(created, isNotNull);

      final chat = AiChatController(1, repository);
      await chat.load();
      expect(chat.state.messages, hasLength(1));

      final sent = await chat.send('diagnose me');
      expect(sent, true);
      expect(chat.state.messages.last.isRefusal, true);
      expect(chat.state.latestSafetyMessage, isNotNull);

      final toggled = await chat.toggleContext(false);
      expect(toggled, true);
      expect(chat.state.conversation?.contextEnabled, false);
    },
  );

  test(
    'chat controller maps provider unavailable and rate limit states',
    () async {
      final unavailable = AiChatController(
        1,
        FakeAiRepository(
          sendError: const ApiError(
            message: 'المساعد غير متاح مؤقتًا، جرّب لاحقًا.',
            type: ApiErrorType.server,
          ),
        ),
      );
      await unavailable.load();
      await unavailable.send('safe');
      expect(unavailable.state.providerUnavailable, true);

      final limited = AiChatController(
        1,
        FakeAiRepository(
          sendError: const ApiError(
            message: 'Too many messages',
            type: ApiErrorType.rateLimited,
            statusCode: 429,
          ),
        ),
      );
      await limited.load();
      await limited.send('safe');
      expect(limited.state.rateLimited, true);
    },
  );

  test('context controller loads preview', () async {
    final controller = AiContextController(FakeAiRepository());
    await controller.load();

    expect(controller.state.preview?.hasAnyContext, true);
  });
}

class FakeAiRepository implements AiRepository {
  FakeAiRepository({this.sendError});

  final ApiError? sendError;

  AiConversation conversation = const AiConversation(
    id: 1,
    title: 'Safe chat',
    status: AiConversationStatus.active,
    language: AiLanguage.ar,
    contextEnabled: true,
  );

  List<AiMessage> messages = const [
    AiMessage(
      id: 1,
      conversationId: 1,
      role: AiMessageRole.assistant,
      content: 'How can I help organize your information?',
      safetyClassification: AiSafetyClassification.safe,
    ),
  ];

  @override
  Future<ApiResult<AiConversation>> createConversation(
    CreateAiConversationRequest request,
  ) {
    return Future.value(
      const ApiSuccess(
        AiConversation(
          id: 2,
          status: AiConversationStatus.active,
          language: AiLanguage.ar,
        ),
      ),
    );
  }

  @override
  Future<ApiResult<AiConversation>> deleteConversation(int id) {
    conversation = const AiConversation(
      id: 1,
      status: AiConversationStatus.archived,
      language: AiLanguage.ar,
    );
    return Future.value(ApiSuccess(conversation));
  }

  @override
  Future<ApiResult<AiContextPreview>> getContextPreview() {
    return Future.value(
      const ApiSuccess(
        AiContextPreview(
          age: 30,
          latestVitals: [
            {'type': 'heart_rate', 'value': 80},
          ],
        ),
      ),
    );
  }

  @override
  Future<ApiResult<AiConversation>> getConversationDetails(int id) {
    return Future.value(ApiSuccess(conversation));
  }

  @override
  Future<ApiResult<List<AiConversation>>> getConversations() {
    return Future.value(ApiSuccess([conversation]));
  }

  @override
  Future<ApiResult<List<AiMessage>>> getMessages(int conversationId) {
    return Future.value(ApiSuccess(messages));
  }

  @override
  Future<ApiResult<AiQuickAskResult>> quickAsk(SendAiMessageRequest request) {
    return Future.value(
      ApiSuccess(
        AiQuickAskResult(conversation: conversation, message: messages.first),
      ),
    );
  }

  @override
  Future<ApiResult<AiMessage>> sendMessage(
    int conversationId,
    SendAiMessageRequest request,
  ) {
    if (sendError != null) return Future.value(ApiFailure(sendError!));

    final user = AiMessage(
      id: 2,
      conversationId: conversationId,
      role: AiMessageRole.user,
      content: request.content,
      safetyClassification: AiSafetyClassification.safe,
    );
    const assistant = AiMessage(
      id: 3,
      conversationId: 1,
      role: AiMessageRole.assistant,
      content: 'لا يمكن للمساعد تقديم تشخيص أو وصف علاج.',
      safetyClassification: AiSafetyClassification.diagnosisRequest,
      wasRefused: true,
    );
    messages = [...messages, user, assistant];
    return Future.value(const ApiSuccess(assistant));
  }

  @override
  Future<ApiResult<AiConversation>> toggleContext(
    int conversationId,
    ToggleAiContextRequest request,
  ) {
    conversation = AiConversation(
      id: conversationId,
      title: conversation.title,
      status: conversation.status,
      language: conversation.language,
      contextEnabled: request.contextEnabled,
    );
    return Future.value(ApiSuccess(conversation));
  }

  @override
  Future<ApiResult<AiConversation>> updateConversation(
    int id,
    UpdateAiConversationRequest request,
  ) {
    return Future.value(ApiSuccess(conversation));
  }
}
