import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_reminder.dart';
import 'package:etamen_app/features/medications/presentation/providers/medications_providers.dart';
import 'package:etamen_app/features/medications/presentation/widgets/frequency_selector.dart';
import 'package:etamen_app/features/medications/presentation/widgets/medication_disclaimer_box.dart';
import 'package:etamen_app/features/medications/presentation/widgets/medication_time_chip.dart';
import 'package:etamen_app/features/medications/presentation/widgets/refill_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class MedicationReminderDetailsPage extends ConsumerWidget {
  const MedicationReminderDetailsPage({required this.reminderId, super.key});

  final int reminderId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(
      medicationReminderDetailsControllerProvider(reminderId),
    );
    final controller = ref.read(
      medicationReminderDetailsControllerProvider(reminderId).notifier,
    );
    final reminder = state.reminder;
    return AppScaffold(
      title: l10n.get('reminderDetails'),
      body: RefreshIndicator(
        onRefresh: controller.load,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            const MedicationDisclaimerBox(),
            const SizedBox(height: 16),
            if (state.isLoading)
              const LoadingView()
            else if (state.error != null)
              ErrorView(message: state.error!.message, onRetry: controller.load)
            else if (reminder != null)
              _DetailsContent(
                reminder: reminder,
                state: state,
                controller: controller,
              ),
          ],
        ),
      ),
    );
  }
}

class _DetailsContent extends StatelessWidget {
  const _DetailsContent({
    required this.reminder,
    required this.state,
    required this.controller,
  });

  final MedicationReminder reminder;
  final MedicationReminderDetailsState state;
  final MedicationReminderDetailsController controller;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  reminder.medicationName,
                  style: Theme.of(context).textTheme.headlineSmall,
                ),
                if (reminder.dosageText.isNotEmpty) ...[
                  const SizedBox(height: 6),
                  Text(reminder.dosageText),
                ],
                const SizedBox(height: 12),
                Text(medicationFrequencyLabel(context, reminder.frequencyType)),
                if (reminder.times.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 6,
                    runSpacing: 6,
                    children: reminder.times
                        .map((time) => MedicationTimeChip(time: time.timeOfDay))
                        .toList(growable: false),
                  ),
                ],
                const SizedBox(height: 8),
                Text('${l10n.get('startDate')}: ${reminder.startDate ?? '-'}'),
                Text('${l10n.get('endDate')}: ${reminder.endDate ?? '-'}'),
                if (reminder.instructions?.trim().isNotEmpty == true) ...[
                  const SizedBox(height: 8),
                  Text(reminder.instructions!),
                ],
              ],
            ),
          ),
        ),
        Wrap(
          spacing: 8,
          children: [
            if (reminder.canPause)
              OutlinedButton(
                onPressed: state.isSubmitting ? null : controller.pause,
                child: Text(l10n.get('pauseReminder')),
              ),
            if (reminder.canResume)
              OutlinedButton(
                onPressed: state.isSubmitting ? null : controller.resume,
                child: Text(l10n.get('resumeReminder')),
              ),
            if (reminder.canCancel)
              OutlinedButton(
                onPressed: state.isSubmitting ? null : controller.cancel,
                child: Text(l10n.get('cancelReminder')),
              ),
            if (reminder.frequencyType == MedicationFrequencyType.asNeeded)
              FilledButton(
                onPressed: state.isSubmitting
                    ? null
                    : () async {
                        final ok = await controller.markAsNeededTaken();
                        if (!context.mounted) return;
                        if (ok) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(content: Text(l10n.get('doseTakenSaved'))),
                          );
                        }
                      },
                child: Text(l10n.get('markTaken')),
              ),
          ],
        ),
        if (reminder.refillEnabled) ...[
          const SizedBox(height: 16),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    l10n.get('refill'),
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8,
                    children: [
                      FilledButton(
                        onPressed: state.isSubmitting
                            ? null
                            : () => controller.refillDone(),
                        child: Text(l10n.get('refillDoneAction')),
                      ),
                      OutlinedButton(
                        onPressed: state.isSubmitting
                            ? null
                            : () => controller.refillSkipped(),
                        child: Text(l10n.get('refillSkippedAction')),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
        if (state.refills.isNotEmpty) ...[
          const SizedBox(height: 8),
          ...state.refills.map((event) => RefillCard(event: event)),
        ],
        if (state.logs.isNotEmpty) ...[
          const SizedBox(height: 16),
          Text(
            l10n.get('recentLogs'),
            style: Theme.of(context).textTheme.titleMedium,
          ),
          ...state.logs
              .take(5)
              .map(
                (log) => Card(
                  child: ListTile(
                    leading: const Icon(Icons.history),
                    title: Text(log.action.wireValue),
                    subtitle: Text(
                      log.scheduledFor?.toLocal().toString() ?? '-',
                    ),
                  ),
                ),
              ),
        ],
      ],
    );
  }
}
