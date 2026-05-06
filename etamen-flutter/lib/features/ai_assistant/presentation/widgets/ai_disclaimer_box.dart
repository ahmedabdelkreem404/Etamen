import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:flutter/material.dart';

class AiDisclaimerBox extends StatelessWidget {
  const AiDisclaimerBox({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return DecoratedBox(
      decoration: BoxDecoration(
        color: AppColors.info.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: AppColors.info.withValues(alpha: 0.18)),
      ),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.info_outline, color: AppColors.info),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    l10n.get('aiAssistantNotDoctor'),
                    style: const TextStyle(
                      color: AppColors.info,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(l10n.get('aiDisclaimerFull')),
            const SizedBox(height: 8),
            Text(l10n.get('aiEmergencyText')),
          ],
        ),
      ),
    );
  }
}
