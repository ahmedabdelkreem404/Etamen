import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_log.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_schedule_item.dart';
import 'package:etamen_app/features/medications/presentation/widgets/medication_log_action_buttons.dart';
import 'package:flutter/material.dart';

class TodayMedicationCard extends StatelessWidget {
  const TodayMedicationCard({
    required this.item,
    required this.onTaken,
    required this.onSkipped,
    this.isBusy = false,
    super.key,
  });

  final MedicationScheduleItem item;
  final VoidCallback onTaken;
  final VoidCallback onSkipped;
  final bool isBusy;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                CircleAvatar(
                  backgroundColor: AppColors.primary.withValues(alpha: 0.1),
                  child: const Icon(Icons.alarm_outlined),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        item.medicationName,
                        style: Theme.of(context).textTheme.titleMedium,
                      ),
                      Text(
                        item.timeOfDay ?? _formatTime(item.scheduledFor),
                        style: const TextStyle(color: AppColors.muted),
                      ),
                    ],
                  ),
                ),
                if (item.logAction != MedicationLogAction.unknown)
                  Chip(label: Text(_logLabel(context, item.logAction))),
              ],
            ),
            if (item.dosage?.trim().isNotEmpty == true) ...[
              const SizedBox(height: 8),
              Text(item.dosage!),
            ],
            const SizedBox(height: 12),
            if (item.isLogged)
              Text(
                l10n.get('doseAlreadyLogged'),
                style: const TextStyle(color: AppColors.muted),
              )
            else
              MedicationLogActionButtons(
                isBusy: isBusy,
                onTaken: item.canMarkTaken ? onTaken : null,
                onSkipped: item.canMarkSkipped ? onSkipped : null,
              ),
          ],
        ),
      ),
    );
  }

  static String _formatTime(DateTime? value) {
    if (value == null) return '--:--';
    final local = value.toLocal();
    String two(int number) => number.toString().padLeft(2, '0');
    return '${two(local.hour)}:${two(local.minute)}';
  }

  static String _logLabel(BuildContext context, MedicationLogAction action) {
    final l10n = AppLocalizations.of(context);
    return switch (action) {
      MedicationLogAction.taken => l10n.get('taken'),
      MedicationLogAction.skipped => l10n.get('skipped'),
      MedicationLogAction.missed => l10n.get('missed'),
      MedicationLogAction.unknown => l10n.get('unknown'),
    };
  }
}
