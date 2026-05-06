import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/medications/presentation/providers/medications_providers.dart';
import 'package:etamen_app/features/medications/presentation/widgets/adherence_summary_card.dart';
import 'package:etamen_app/features/medications/presentation/widgets/medication_disclaimer_box.dart';
import 'package:etamen_app/features/medications/presentation/widgets/refill_card.dart';
import 'package:etamen_app/features/medications/presentation/widgets/today_medication_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class MedicationsDashboardPage extends ConsumerWidget {
  const MedicationsDashboardPage({this.showAppBar = true, super.key});

  final bool showAppBar;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(medicationsDashboardControllerProvider);
    final controller = ref.read(
      medicationsDashboardControllerProvider.notifier,
    );

    final body = RefreshIndicator(
      onRefresh: controller.load,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        children: [
          if (!showAppBar) ...[
            Text(
              l10n.get('medications'),
              style: Theme.of(
                context,
              ).textTheme.headlineMedium?.copyWith(fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 12),
          ],
          const MedicationDisclaimerBox(),
          const SizedBox(height: 16),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              FilledButton.icon(
                onPressed: () => context.push(RouteNames.medicationReminders),
                icon: const Icon(Icons.medication_outlined),
                label: Text(l10n.get('medicationReminders')),
              ),
              OutlinedButton.icon(
                onPressed: () => context.push(RouteNames.todayMedications),
                icon: const Icon(Icons.today_outlined),
                label: Text(l10n.get('todayDoses')),
              ),
              OutlinedButton.icon(
                onPressed: () =>
                    context.push(RouteNames.createMedicationReminder),
                icon: const Icon(Icons.add),
                label: Text(l10n.get('addMedicationReminder')),
              ),
              OutlinedButton.icon(
                onPressed: () => context.push(RouteNames.medicationAdherence),
                icon: const Icon(Icons.analytics_outlined),
                label: Text(l10n.get('adherence')),
              ),
            ],
          ),
          const SizedBox(height: 16),
          if (state.isLoading)
            const LoadingView()
          else if (state.error != null)
            ErrorView(message: state.error!.message, onRetry: controller.load)
          else if (state.isEmpty)
            EmptyView(
              message: l10n.get('addFirstMedicationReminder'),
              icon: Icons.medication_liquid_outlined,
            )
          else ...[
            Card(
              child: ListTile(
                leading: const Icon(Icons.medication_outlined),
                title: Text(l10n.get('activeReminders')),
                trailing: Text('${state.activeReminderCount}'),
              ),
            ),
            if (state.adherence != null)
              AdherenceSummaryCard(adherence: state.adherence!),
            if (state.today.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text(
                l10n.get('todayDoses'),
                style: Theme.of(context).textTheme.titleMedium,
              ),
              const SizedBox(height: 8),
              ...state.today
                  .take(3)
                  .map(
                    (item) => TodayMedicationCard(
                      item: item,
                      onTaken: () => context.push(RouteNames.todayMedications),
                      onSkipped: () =>
                          context.push(RouteNames.todayMedications),
                    ),
                  ),
            ],
            if (state.refills.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text(
                l10n.get('refill'),
                style: Theme.of(context).textTheme.titleMedium,
              ),
              ...state.refills.take(2).map((event) => RefillCard(event: event)),
            ],
          ],
        ],
      ),
    );

    if (!showAppBar) return body;
    return AppScaffold(
      title: l10n.get('medications'),
      actions: [
        IconButton(
          tooltip: l10n.get('addMedicationReminder'),
          onPressed: () => context.push(RouteNames.createMedicationReminder),
          icon: const Icon(Icons.add_circle_outline),
        ),
      ],
      body: body,
    );
  }
}
