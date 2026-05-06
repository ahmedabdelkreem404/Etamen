import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_refill_event.dart';
import 'package:flutter/material.dart';

class RefillCard extends StatelessWidget {
  const RefillCard({required this.event, super.key});

  final MedicationRefillEvent event;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      child: ListTile(
        leading: const Icon(Icons.inventory_2_outlined, color: AppColors.info),
        title: Text(_label(l10n, event.eventType)),
        subtitle: Text(event.eventDate ?? '-'),
      ),
    );
  }

  static String _label(AppLocalizations l10n, MedicationRefillEventType type) {
    return switch (type) {
      MedicationRefillEventType.refillDue => l10n.get('refillDue'),
      MedicationRefillEventType.refillDone => l10n.get('refillDone'),
      MedicationRefillEventType.refillSkipped => l10n.get('refillSkipped'),
      MedicationRefillEventType.unknown => l10n.get('unknown'),
    };
  }
}
