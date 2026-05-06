import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_conversation.dart';
import 'package:flutter/material.dart';

class AiConversationCard extends StatelessWidget {
  const AiConversationCard({
    required this.conversation,
    required this.onTap,
    this.onArchive,
    super.key,
  });

  final AiConversation conversation;
  final VoidCallback onTap;
  final VoidCallback? onArchive;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final title = conversation.title?.isNotEmpty == true
        ? conversation.title!
        : l10n.get('newAiConversation');

    return Card(
      child: ListTile(
        onTap: onTap,
        leading: const CircleAvatar(
          backgroundColor: AppColors.primary,
          child: Icon(Icons.smart_toy_outlined, color: Colors.white),
        ),
        title: Text(title, maxLines: 1, overflow: TextOverflow.ellipsis),
        subtitle: Text(
          [
            _statusLabel(context, conversation.status),
            conversation.contextEnabled
                ? l10n.get('contextEnabled')
                : l10n.get('contextDisabled'),
            if (conversation.lastMessageAt != null)
              conversation.lastMessageAt!.toLocal().toString(),
          ].join(' • '),
          maxLines: 2,
          overflow: TextOverflow.ellipsis,
        ),
        trailing: onArchive == null
            ? const Icon(Icons.chevron_right)
            : IconButton(
                tooltip: l10n.get('archiveConversation'),
                onPressed: onArchive,
                icon: const Icon(Icons.archive_outlined),
              ),
      ),
    );
  }

  String _statusLabel(BuildContext context, AiConversationStatus status) {
    final l10n = AppLocalizations.of(context);
    return switch (status) {
      AiConversationStatus.active => l10n.get('active'),
      AiConversationStatus.archived => l10n.get('archived'),
      AiConversationStatus.blocked => l10n.get('blocked'),
      AiConversationStatus.unknown => l10n.get('unknownStatus'),
    };
  }
}
