import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/ai_assistant/presentation/providers/ai_providers.dart';
import 'package:etamen_app/features/ai_assistant/presentation/widgets/ai_context_toggle.dart';
import 'package:etamen_app/features/ai_assistant/presentation/widgets/ai_disclaimer_box.dart';
import 'package:etamen_app/features/ai_assistant/presentation/widgets/ai_input_bar.dart';
import 'package:etamen_app/features/ai_assistant/presentation/widgets/ai_message_bubble.dart';
import 'package:etamen_app/features/ai_assistant/presentation/widgets/ai_provider_unavailable_view.dart';
import 'package:etamen_app/features/ai_assistant/presentation/widgets/ai_rate_limit_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class AiChatPage extends ConsumerStatefulWidget {
  const AiChatPage({required this.conversationId, super.key});

  final int conversationId;

  @override
  ConsumerState<AiChatPage> createState() => _AiChatPageState();
}

class _AiChatPageState extends ConsumerState<AiChatPage> {
  final _inputController = TextEditingController();
  final _scrollController = ScrollController();

  @override
  void dispose() {
    _inputController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(aiChatControllerProvider(widget.conversationId));
    final controller = ref.read(
      aiChatControllerProvider(widget.conversationId).notifier,
    );

    return AppScaffold(
      title: state.conversation?.title?.isNotEmpty == true
          ? state.conversation!.title!
          : l10n.get('aiAssistant'),
      actions: [
        IconButton(
          tooltip: l10n.get('viewContextPreview'),
          onPressed: () => context.push(RouteNames.aiContextPreview),
          icon: const Icon(Icons.health_and_safety_outlined),
        ),
      ],
      body: Column(
        children: [
          Expanded(
            child: state.isLoading && state.messages.isEmpty
                ? const LoadingView()
                : state.providerUnavailable && state.messages.isEmpty
                ? AiProviderUnavailableView(onRetry: controller.load)
                : state.error != null && state.messages.isEmpty
                ? ErrorView(
                    message: state.error!.message,
                    onRetry: () => controller.load(),
                  )
                : RefreshIndicator(
                    onRefresh: controller.load,
                    child: ListView(
                      controller: _scrollController,
                      padding: const EdgeInsets.all(16),
                      children: [
                        const AiDisclaimerBox(),
                        const SizedBox(height: 12),
                        if (state.conversation != null)
                          AiContextToggle(
                            value: state.conversation!.contextEnabled,
                            onChanged: (enabled) =>
                                controller.toggleContext(enabled),
                          ),
                        if (state.rateLimited) ...[
                          const SizedBox(height: 8),
                          const AiRateLimitBanner(),
                        ],
                        if (state.providerUnavailable) ...[
                          const SizedBox(height: 8),
                          Text(
                            l10n.get('aiProviderUnavailable'),
                            style: const TextStyle(color: AppColors.warning),
                          ),
                        ],
                        const SizedBox(height: 12),
                        if (state.isEmpty)
                          EmptyView(
                            message: l10n.get('safeAiConversationStart'),
                            icon: Icons.chat_bubble_outline,
                          )
                        else
                          ...state.messages.map(
                            (message) => Padding(
                              padding: const EdgeInsets.only(bottom: 10),
                              child: AiMessageBubble(message: message),
                            ),
                          ),
                        if (state.isSending) ...[
                          const SizedBox(height: 8),
                          Text(l10n.get('aiThinking')),
                        ],
                      ],
                    ),
                  ),
          ),
          AiInputBar(
            controller: _inputController,
            isSending: state.isSending,
            onSend: () async {
              final text = _inputController.text;
              final ok = await controller.send(text);
              if (ok) {
                _inputController.clear();
                await Future<void>.delayed(const Duration(milliseconds: 100));
                _scrollToBottom();
              }
            },
          ),
        ],
      ),
    );
  }

  void _scrollToBottom() {
    if (!_scrollController.hasClients) return;
    _scrollController.animateTo(
      _scrollController.position.maxScrollExtent,
      duration: const Duration(milliseconds: 220),
      curve: Curves.easeOut,
    );
  }
}
