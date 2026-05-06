import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/health/domain/entities/vital_trend.dart';
import 'package:flutter/material.dart';

class TrendPreviewCard extends StatelessWidget {
  const TrendPreviewCard({required this.trend, super.key});

  final VitalTrend trend;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    if (trend.points.isEmpty) return const SizedBox.shrink();
    final visiblePoints = trend.points.take(4).toList(growable: false);
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              l10n.get('trendPreview'),
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 6),
            Text(
              l10n.get('trendDisclaimer'),
              style: const TextStyle(color: AppColors.muted),
            ),
            const SizedBox(height: 12),
            ...visiblePoints.map(
              (point) => ListTile(
                dense: true,
                contentPadding: EdgeInsets.zero,
                leading: const Icon(Icons.show_chart),
                title: Text(
                  '${point.value ?? '-'}${point.secondaryValue == null ? '' : '/${point.secondaryValue}'} ${trend.unit ?? ''}'
                      .trim(),
                ),
                subtitle: point.measuredAt == null
                    ? null
                    : Text(_formatDate(point.measuredAt!)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  static String _formatDate(DateTime value) {
    final local = value.toLocal();
    String two(int number) => number.toString().padLeft(2, '0');
    return '${local.year}-${two(local.month)}-${two(local.day)}';
  }
}
