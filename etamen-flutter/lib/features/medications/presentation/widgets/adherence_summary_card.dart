import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_adherence.dart';
import 'package:flutter/material.dart';

class AdherenceSummaryCard extends StatelessWidget {
  const AdherenceSummaryCard({required this.adherence, super.key});

  final MedicationAdherence adherence;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              l10n.get('adherence'),
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 12),
            LinearProgressIndicator(
              value: (adherence.adherencePercentage.clamp(0, 100)) / 100,
              minHeight: 8,
              borderRadius: BorderRadius.circular(8),
            ),
            const SizedBox(height: 8),
            Text('${adherence.adherencePercentage.toStringAsFixed(0)}%'),
            const SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                _Metric(
                  label: l10n.get('totalDoses'),
                  value: '${adherence.totalScheduled}',
                ),
                _Metric(
                  label: l10n.get('takenDoses'),
                  value: '${adherence.takenCount}',
                ),
                _Metric(
                  label: l10n.get('skippedDoses'),
                  value: '${adherence.skippedCount}',
                ),
                _Metric(
                  label: l10n.get('missedDoses'),
                  value: '${adherence.missedCount}',
                ),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              l10n.get('adherenceDisclaimer'),
              style: const TextStyle(color: AppColors.muted),
            ),
          ],
        ),
      ),
    );
  }
}

class _Metric extends StatelessWidget {
  const _Metric({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 118,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.primary.withValues(alpha: 0.07),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: const TextStyle(color: AppColors.muted)),
          const SizedBox(height: 6),
          Text(value, style: Theme.of(context).textTheme.titleMedium),
        ],
      ),
    );
  }
}
