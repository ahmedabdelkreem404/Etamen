import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/features/health/domain/entities/vital_record.dart';
import 'package:flutter/material.dart';

class VitalTypeSelector extends StatelessWidget {
  const VitalTypeSelector({
    required this.value,
    required this.onChanged,
    super.key,
  });

  final VitalType value;
  final ValueChanged<VitalType> onChanged;

  @override
  Widget build(BuildContext context) {
    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: supportedVitalTypes
          .map((type) {
            return ChoiceChip(
              label: Text(vitalTypeLabel(context, type)),
              selected: value == type,
              onSelected: (_) => onChanged(type),
            );
          })
          .toList(growable: false),
    );
  }
}

const supportedVitalTypes = [
  VitalType.bloodPressure,
  VitalType.bloodSugar,
  VitalType.heartRate,
  VitalType.oxygen,
  VitalType.temperature,
  VitalType.weight,
  VitalType.sleep,
  VitalType.mood,
  VitalType.symptoms,
];

String vitalTypeLabel(BuildContext context, VitalType type) {
  final l10n = AppLocalizations.of(context);
  return switch (type) {
    VitalType.bloodPressure => l10n.get('bloodPressure'),
    VitalType.bloodSugar => l10n.get('bloodSugar'),
    VitalType.heartRate => l10n.get('heartRate'),
    VitalType.oxygen => l10n.get('oxygen'),
    VitalType.temperature => l10n.get('temperature'),
    VitalType.weight => l10n.get('weight'),
    VitalType.sleep => l10n.get('sleep'),
    VitalType.mood => l10n.get('mood'),
    VitalType.symptoms => l10n.get('symptoms'),
    VitalType.unknown => l10n.get('unknown'),
  };
}
