import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/medications/presentation/providers/medications_providers.dart';
import 'package:etamen_app/features/medications/presentation/widgets/medication_disclaimer_box.dart';
import 'package:etamen_app/features/medications/presentation/widgets/medication_reminder_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class MedicationRemindersPage extends ConsumerWidget {
  const MedicationRemindersPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(medicationRemindersControllerProvider);
    final controller = ref.read(medicationRemindersControllerProvider.notifier);

    return AppScaffold(
      title: l10n.get('medicationReminders'),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push(RouteNames.createMedicationReminder),
        icon: const Icon(Icons.add),
        label: Text(l10n.get('addMedicationReminder')),
      ),
      body: RefreshIndicator(
        onRefresh: controller.load,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            const MedicationDisclaimerBox(),
            const SizedBox(height: 16),
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: ReminderFilter.values
                    .map((filter) {
                      return Padding(
                        padding: const EdgeInsetsDirectional.only(end: 8),
                        child: ChoiceChip(
                          label: Text(_filterLabel(l10n, filter)),
                          selected: state.filter == filter,
                          onSelected: (_) => controller.selectFilter(filter),
                        ),
                      );
                    })
                    .toList(growable: false),
              ),
            ),
            const SizedBox(height: 16),
            if (state.isLoading)
              const LoadingView()
            else if (state.error != null)
              ErrorView(message: state.error!.message, onRetry: controller.load)
            else if (state.isEmpty)
              EmptyView(
                message: l10n.get('noMedicationReminders'),
                icon: Icons.medication_outlined,
              )
            else
              ...state.filteredItems.map(
                (reminder) => MedicationReminderCard(
                  reminder: reminder,
                  onTap: () => context.push(
                    RouteNames.medicationReminderDetails(reminder.id),
                  ),
                  onPause: reminder.canPause
                      ? () => controller.pause(reminder.id)
                      : null,
                  onResume: reminder.canResume
                      ? () => controller.resume(reminder.id)
                      : null,
                  onCancel: reminder.canCancel
                      ? () => controller.cancel(reminder.id)
                      : null,
                ),
              ),
          ],
        ),
      ),
    );
  }

  static String _filterLabel(AppLocalizations l10n, ReminderFilter filter) {
    return switch (filter) {
      ReminderFilter.all => l10n.get('all'),
      ReminderFilter.active => l10n.get('active'),
      ReminderFilter.paused => l10n.get('paused'),
      ReminderFilter.ended => l10n.get('ended'),
    };
  }
}
