import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/ai_assistant/presentation/providers/ai_providers.dart';
import 'package:etamen_app/features/ai_assistant/presentation/widgets/ai_conversation_card.dart';
import 'package:etamen_app/features/ai_assistant/presentation/widgets/ai_disclaimer_box.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class AiConversationsPage extends ConsumerWidget {
  const AiConversationsPage({this.showAppBar = true, super.key});

  final bool showAppBar;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(aiConversationsControllerProvider);
    final controller = ref.read(aiConversationsControllerProvider.notifier);
    final body = state.isLoading && state.items.isEmpty
        ? const LoadingView()
        : state.error != null && state.items.isEmpty
        ? ErrorView(
            message: state.error!.message,
            onRetry: () => controller.load(),
          )
        : RefreshIndicator(
            onRefresh: controller.load,
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                const AiDisclaimerBox(),
                const SizedBox(height: 12),
                AppButton(
                  label: l10n.get('newAiConversation'),
                  isLoading: state.isSubmitting,
                  onPressed: () async {
                    final conversation = await controller.createNew();
                    if (conversation != null && context.mounted) {
                      context.push(RouteNames.aiConversation(conversation.id));
                    }
                  },
                ),
                const SizedBox(height: 12),
                if (state.isEmpty)
                  EmptyView(
                    message: l10n.get('noAiConversationsYet'),
                    icon: Icons.smart_toy_outlined,
                  )
                else
                  ...state.items.map(
                    (conversation) => Padding(
                      padding: const EdgeInsets.only(bottom: 8),
                      child: AiConversationCard(
                        conversation: conversation,
                        onTap: () => context.push(
                          RouteNames.aiConversation(conversation.id),
                        ),
                        onArchive: conversation.isActive
                            ? () => controller.archive(conversation.id)
                            : null,
                      ),
                    ),
                  ),
              ],
            ),
          );

    if (!showAppBar) return body;

    return AppScaffold(
      title: l10n.get('aiAssistant'),
      actions: [
        IconButton(
          tooltip: l10n.get('viewContextPreview'),
          onPressed: () => context.push(RouteNames.aiContextPreview),
          icon: const Icon(Icons.health_and_safety_outlined),
        ),
      ],
      body: body,
    );
  }
}
