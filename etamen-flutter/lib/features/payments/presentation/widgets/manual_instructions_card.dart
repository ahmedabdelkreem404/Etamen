import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:flutter/material.dart';

class ManualInstructionsCard extends StatelessWidget {
  const ManualInstructionsCard({required this.instructions, super.key});

  final String? instructions;

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
                const Icon(Icons.info_outline),
                const SizedBox(width: 8),
                Text(
                  l10n.get('manualInstructions'),
                  style: Theme.of(context).textTheme.titleMedium,
                ),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              instructions?.isNotEmpty == true
                  ? instructions!
                  : l10n.get('instructionsUnavailable'),
            ),
          ],
        ),
      ),
    );
  }
}
