import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/care_plans/presentation/providers/care_plans_providers.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_disclaimer_box.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_labels.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/progress_summary_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class CarePlanProgressPage extends ConsumerWidget {
  const CarePlanProgressPage({required this.planId, super.key});

  final int planId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(carePlanProgressControllerProvider(planId));
    final controller = ref.read(
      carePlanProgressControllerProvider(planId).notifier,
    );

    return AppScaffold(
      title: l10n.get('progress'),
      body: RefreshIndicator(
        onRefresh: controller.load,
        child: state.isLoading
            ? const LoadingView()
            : state.error != null
            ? ErrorView(message: state.error!.message, onRetry: controller.load)
            : state.progress == null
            ? EmptyView(message: l10n.get('noAdditionalDetails'))
            : ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                children: [
                  CarePlanDisclaimerBox(
                    text:
                        state.progress!.disclaimer ??
                        l10n.get('carePlanProgressDisclaimer'),
                  ),
                  const SizedBox(height: 16),
                  ProgressSummaryCard(progress: state.progress!),
                  if (state.progress!.latestCheckin != null) ...[
                    const SizedBox(height: 16),
                    Text(
                      l10n.get('latestCheckin'),
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                    Card(
                      child: ListTile(
                        title: Text(
                          '${l10n.get('commitmentScore')}: ${state.progress!.latestCheckin!.commitmentScore ?? '--'}',
                        ),
                        subtitle: Text(
                          state.progress!.latestCheckin!.checkinDate ?? '',
                        ),
                      ),
                    ),
                  ],
                  if (state.progress!.latestMealLogs.isNotEmpty) ...[
                    const SizedBox(height: 16),
                    Text(
                      l10n.get('latestMealLogs'),
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                    ...state.progress!.latestMealLogs.map(
                      (log) => Card(
                        child: ListTile(
                          title: Text(mealLogStatusLabel(context, log.status)),
                          subtitle: Text(log.loggedAt?.toString() ?? ''),
                        ),
                      ),
                    ),
                  ],
                ],
              ),
      ),
    );
  }
}
