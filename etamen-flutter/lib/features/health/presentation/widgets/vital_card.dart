import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/health/domain/entities/vital_record.dart';
import 'package:etamen_app/features/health/presentation/widgets/vital_flag_chip.dart';
import 'package:etamen_app/features/health/presentation/widgets/vital_type_selector.dart';
import 'package:flutter/material.dart';

class VitalCard extends StatelessWidget {
  const VitalCard({required this.record, super.key});

  final VitalRecord record;

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
                  child: Icon(_iconFor(record.vitalType), color: AppColors.primary),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        vitalTypeLabel(context, record.vitalType),
                        style: Theme.of(context).textTheme.titleMedium,
                      ),
                      if (record.measuredAt != null)
                        Text(
                          _formatDate(record.measuredAt!),
                          style: const TextStyle(color: AppColors.muted),
                        ),
                    ],
                  ),
                ),
                if (record.flag != VitalFlag.unknown)
                  VitalFlagChip(flag: record.flag),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              record.formattedValue,
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                fontWeight: FontWeight.w800,
              ),
            ),
            if (record.notes?.trim().isNotEmpty == true) ...[
              const SizedBox(height: 8),
              Text(record.notes!),
            ],
            if (record.safeMessage?.trim().isNotEmpty == true) ...[
              const SizedBox(height: 8),
              Text(
                l10n.get('repeatedReadingDoctor'),
                style: const TextStyle(color: AppColors.warning),
              ),
            ],
          ],
        ),
      ),
    );
  }

  static IconData _iconFor(VitalType type) {
    return switch (type) {
      VitalType.bloodPressure => Icons.monitor_heart_outlined,
      VitalType.bloodSugar => Icons.bloodtype_outlined,
      VitalType.heartRate => Icons.favorite_border,
      VitalType.oxygen => Icons.air_outlined,
      VitalType.temperature => Icons.thermostat_outlined,
      VitalType.weight => Icons.scale_outlined,
      VitalType.sleep => Icons.bedtime_outlined,
      VitalType.mood => Icons.mood_outlined,
      VitalType.symptoms => Icons.note_alt_outlined,
      VitalType.unknown => Icons.health_and_safety_outlined,
    };
  }

  static String _formatDate(DateTime value) {
    final local = value.toLocal();
    String two(int number) => number.toString().padLeft(2, '0');
    return '${local.year}-${two(local.month)}-${two(local.day)} ${two(local.hour)}:${two(local.minute)}';
  }
}
