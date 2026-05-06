import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_reminder.dart';
import 'package:flutter/material.dart';

class FrequencySelector extends StatelessWidget {
  const FrequencySelector({
    required this.value,
    required this.onChanged,
    super.key,
  });

  final MedicationFrequencyType value;
  final ValueChanged<MedicationFrequencyType> onChanged;

  @override
  Widget build(BuildContext context) {
    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: creatableFrequencies
          .map((type) {
            return ChoiceChip(
              label: Text(medicationFrequencyLabel(context, type)),
              selected: value == type,
              onSelected: (_) => onChanged(type),
            );
          })
          .toList(growable: false),
    );
  }
}

const creatableFrequencies = [
  MedicationFrequencyType.onceDaily,
  MedicationFrequencyType.twiceDaily,
  MedicationFrequencyType.threeTimesDaily,
  MedicationFrequencyType.customTimes,
  MedicationFrequencyType.everyXHours,
  MedicationFrequencyType.asNeeded,
];

String medicationFrequencyLabel(
  BuildContext context,
  MedicationFrequencyType type,
) {
  final l10n = AppLocalizations.of(context);
  return switch (type) {
    MedicationFrequencyType.onceDaily => l10n.get('onceDaily'),
    MedicationFrequencyType.twiceDaily => l10n.get('twiceDaily'),
    MedicationFrequencyType.threeTimesDaily => l10n.get('threeTimesDaily'),
    MedicationFrequencyType.customTimes => l10n.get('customTimes'),
    MedicationFrequencyType.everyXHours => l10n.get('everyXHours'),
    MedicationFrequencyType.specificDays => l10n.get('specificDays'),
    MedicationFrequencyType.asNeeded => l10n.get('asNeeded'),
    MedicationFrequencyType.unknown => l10n.get('unknown'),
  };
}

String medicationStatusLabel(
  BuildContext context,
  MedicationReminderStatus status,
) {
  final l10n = AppLocalizations.of(context);
  return switch (status) {
    MedicationReminderStatus.active => l10n.get('active'),
    MedicationReminderStatus.paused => l10n.get('paused'),
    MedicationReminderStatus.completed => l10n.get('completed'),
    MedicationReminderStatus.cancelled => l10n.get('cancelled'),
    MedicationReminderStatus.unknown => l10n.get('unknown'),
  };
}
