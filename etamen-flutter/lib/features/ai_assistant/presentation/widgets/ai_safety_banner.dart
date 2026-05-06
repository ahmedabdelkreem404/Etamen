import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:flutter/material.dart';

class AiSafetyBanner extends StatelessWidget {
  const AiSafetyBanner({required this.isEmergency, this.message, super.key});

  final bool isEmergency;
  final String? message;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final color = isEmergency ? AppColors.danger : AppColors.warning;
    final icon = isEmergency ? Icons.emergency_outlined : Icons.shield_outlined;
    final text = message?.isNotEmpty == true
        ? message!
        : isEmergency
        ? l10n.get('contactEmergencyNow')
        : l10n.get('aiRefusalText');

    return DecoratedBox(
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.09),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withValues(alpha: 0.22)),
      ),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icon, color: color),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                text,
                style: TextStyle(color: color, fontWeight: FontWeight.w700),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
