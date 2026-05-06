import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:flutter/material.dart';

class MedicationLogActionButtons extends StatelessWidget {
  const MedicationLogActionButtons({
    required this.onTaken,
    required this.onSkipped,
    this.isBusy = false,
    super.key,
  });

  final VoidCallback? onTaken;
  final VoidCallback? onSkipped;
  final bool isBusy;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Row(
      children: [
        Expanded(
          child: FilledButton.icon(
            onPressed: isBusy ? null : onTaken,
            icon: const Icon(Icons.check_circle_outline),
            label: Text(l10n.get('markTaken')),
          ),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: OutlinedButton.icon(
            onPressed: isBusy ? null : onSkipped,
            icon: const Icon(Icons.remove_circle_outline),
            label: Text(l10n.get('markSkipped')),
          ),
        ),
      ],
    );
  }
}
