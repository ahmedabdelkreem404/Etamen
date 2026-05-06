import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/ai_assistant/presentation/providers/ai_providers.dart';
import 'package:etamen_app/features/ai_assistant/presentation/widgets/ai_context_preview_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class AiContextPreviewPage extends ConsumerWidget {
  const AiContextPreviewPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(aiContextControllerProvider);
    final controller = ref.read(aiContextControllerProvider.notifier);

    return AppScaffold(
      title: l10n.get('healthContext'),
      body: state.isLoading && state.preview == null
          ? const LoadingView()
          : state.error != null && state.preview == null
          ? ErrorView(
              message: state.error!.message,
              onRetry: () => controller.load(),
            )
          : RefreshIndicator(
              onRefresh: controller.load,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  DecoratedBox(
                    decoration: BoxDecoration(
                      color: AppColors.info.withValues(alpha: 0.08),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Padding(
                      padding: const EdgeInsets.all(14),
                      child: Text(l10n.get('aiContextPrivacyDisclaimer')),
                    ),
                  ),
                  const SizedBox(height: 12),
                  if (state.preview != null)
                    AiContextPreviewCard(preview: state.preview!),
                ],
              ),
            ),
    );
  }
}
