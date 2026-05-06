import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_progress.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/adherence_bar.dart';
import 'package:flutter/material.dart';

class ProgressSummaryCard extends StatelessWidget {
  const ProgressSummaryCard({required this.progress, super.key});

  final CarePlanProgress progress;

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
              l10n.get('progress'),
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 12),
            AdherenceBar(value: progress.adherencePercentage),
            const SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                _Metric(
                  label: l10n.get('checkinsCount'),
                  value: '${progress.checkinsCount}',
                ),
                _Metric(
                  label: l10n.get('mealLogsCount'),
                  value: '${progress.mealLogsCount}',
                ),
                _Metric(
                  label: l10n.get('averageCommitment'),
                  value:
                      progress.averageCommitmentScore?.toStringAsFixed(0) ??
                      '--',
                ),
                _Metric(
                  label: l10n.get('followedMeals'),
                  value: '${progress.followedCount}',
                ),
                _Metric(
                  label: l10n.get('partialMeals'),
                  value: '${progress.partiallyFollowedCount}',
                ),
                _Metric(
                  label: l10n.get('skippedMeals'),
                  value: '${progress.skippedCount}',
                ),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              progress.disclaimer ?? l10n.get('carePlanProgressDisclaimer'),
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
      width: 130,
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
