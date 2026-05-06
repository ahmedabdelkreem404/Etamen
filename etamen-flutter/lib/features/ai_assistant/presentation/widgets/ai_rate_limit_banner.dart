import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:flutter/material.dart';

class AiRateLimitBanner extends StatelessWidget {
  const AiRateLimitBanner({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return DecoratedBox(
      decoration: BoxDecoration(
        color: AppColors.warning.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Row(
          children: [
            const Icon(Icons.timer_outlined, color: AppColors.warning),
            const SizedBox(width: 10),
            Expanded(child: Text(l10n.get('aiRateLimited'))),
          ],
        ),
      ),
    );
  }
}
