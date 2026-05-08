import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:flutter/material.dart';

class PaymentPollingBanner extends StatelessWidget {
  const PaymentPollingBanner({required this.isPolling, super.key});

  final bool isPolling;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return DecoratedBox(
      decoration: BoxDecoration(
        color: AppColors.medicalAccentSoft,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Row(
          children: [
            if (isPolling)
              const SizedBox(
                width: 18,
                height: 18,
                child: CircularProgressIndicator(strokeWidth: 2),
              )
            else
              const Icon(Icons.hourglass_bottom),
            const SizedBox(width: 10),
            Expanded(child: Text(l10n.get('paymentPollingMessage'))),
          ],
        ),
      ),
    );
  }
}
