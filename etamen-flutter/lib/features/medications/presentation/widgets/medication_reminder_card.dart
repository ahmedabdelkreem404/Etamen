import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_reminder.dart';
import 'package:etamen_app/features/medications/presentation/widgets/frequency_selector.dart';
import 'package:etamen_app/features/medications/presentation/widgets/medication_time_chip.dart';
import 'package:flutter/material.dart';

class MedicationReminderCard extends StatelessWidget {
  const MedicationReminderCard({
    required this.reminder,
    required this.onTap,
    this.onPause,
    this.onResume,
    this.onCancel,
    super.key,
  });

  final MedicationReminder reminder;
  final VoidCallback onTap;
  final VoidCallback? onPause;
  final VoidCallback? onResume;
  final VoidCallback? onCancel;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  CircleAvatar(
                    backgroundColor: AppColors.primary.withValues(alpha: 0.1),
                    child: const Icon(Icons.medication_outlined),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          reminder.medicationName,
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                        if (reminder.dosageText.isNotEmpty)
                          Text(
                            reminder.dosageText,
                            style: const TextStyle(color: AppColors.muted),
                          ),
                      ],
                    ),
                  ),
                  _StatusChip(status: reminder.status),
                ],
              ),
              const SizedBox(height: 10),
              Text(medicationFrequencyLabel(context, reminder.frequencyType)),
              if (reminder.times.isNotEmpty) ...[
                const SizedBox(height: 8),
                Wrap(
                  spacing: 6,
                  runSpacing: 6,
                  children: reminder.times
                      .map(
                        (time) => MedicationTimeChip(
                          time: time.timeOfDay,
                          label: time.label,
                        ),
                      )
                      .toList(growable: false),
                ),
              ],
              if (reminder.refillEnabled) ...[
                const SizedBox(height: 8),
                Text(
                  l10n.get('refillEnabled'),
                  style: const TextStyle(color: AppColors.info),
                ),
              ],
              if (onPause != null || onResume != null || onCancel != null) ...[
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  children: [
                    if (onPause != null)
                      TextButton(
                        onPressed: onPause,
                        child: Text(l10n.get('pauseReminder')),
                      ),
                    if (onResume != null)
                      TextButton(
                        onPressed: onResume,
                        child: Text(l10n.get('resumeReminder')),
                      ),
                    if (onCancel != null)
                      TextButton(
                        onPressed: onCancel,
                        child: Text(l10n.get('cancelReminder')),
                      ),
                  ],
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

class _StatusChip extends StatelessWidget {
  const _StatusChip({required this.status});

  final MedicationReminderStatus status;

  @override
  Widget build(BuildContext context) {
    final color = switch (status) {
      MedicationReminderStatus.active => AppColors.success,
      MedicationReminderStatus.paused => AppColors.warning,
      MedicationReminderStatus.completed => AppColors.info,
      MedicationReminderStatus.cancelled => AppColors.muted,
      MedicationReminderStatus.unknown => AppColors.muted,
    };
    return Chip(
      visualDensity: VisualDensity.compact,
      backgroundColor: color.withValues(alpha: 0.08),
      side: BorderSide(color: color.withValues(alpha: 0.2)),
      label: Text(
        medicationStatusLabel(context, status),
        style: TextStyle(color: color),
      ),
    );
  }
}
