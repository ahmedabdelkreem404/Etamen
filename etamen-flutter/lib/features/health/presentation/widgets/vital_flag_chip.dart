import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/health/domain/entities/vital_record.dart';
import 'package:flutter/material.dart';

class VitalFlagChip extends StatelessWidget {
  const VitalFlagChip({required this.flag, super.key});

  final VitalFlag flag;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final color = switch (flag) {
      VitalFlag.normal => AppColors.success,
      VitalFlag.low || VitalFlag.veryLow => AppColors.info,
      VitalFlag.high || VitalFlag.veryHigh => AppColors.warning,
      VitalFlag.unknown => AppColors.muted,
    };
    final label = switch (flag) {
      VitalFlag.veryLow => l10n.get('veryLow'),
      VitalFlag.low => l10n.get('low'),
      VitalFlag.normal => l10n.get('followUpRange'),
      VitalFlag.high => l10n.get('high'),
      VitalFlag.veryHigh => l10n.get('veryHigh'),
      VitalFlag.unknown => l10n.get('unknownFlag'),
    };

    return Chip(
      visualDensity: VisualDensity.compact,
      side: BorderSide(color: color.withValues(alpha: 0.2)),
      backgroundColor: color.withValues(alpha: 0.08),
      label: Text(label, style: TextStyle(color: color)),
    );
  }
}
