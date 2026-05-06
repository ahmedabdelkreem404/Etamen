import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_checkin.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_labels.dart';
import 'package:flutter/material.dart';

class CheckinForm extends StatelessWidget {
  const CheckinForm({
    required this.commitmentScore,
    required this.energyLevel,
    required this.hungerLevel,
    required this.sleepQuality,
    required this.mood,
    required this.symptomsController,
    required this.generalController,
    required this.onCommitmentChanged,
    required this.onEnergyChanged,
    required this.onHungerChanged,
    required this.onSleepChanged,
    required this.onMoodChanged,
    super.key,
  });

  final int commitmentScore;
  final int energyLevel;
  final int hungerLevel;
  final int sleepQuality;
  final CheckinMood mood;
  final TextEditingController symptomsController;
  final TextEditingController generalController;
  final ValueChanged<int> onCommitmentChanged;
  final ValueChanged<int> onEnergyChanged;
  final ValueChanged<int> onHungerChanged;
  final ValueChanged<int> onSleepChanged;
  final ValueChanged<CheckinMood> onMoodChanged;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _SliderField(
          label: l10n.get('commitmentScore'),
          value: commitmentScore,
          min: 0,
          max: 100,
          onChanged: onCommitmentChanged,
        ),
        _SliderField(
          label: l10n.get('energyLevel'),
          value: energyLevel,
          min: 1,
          max: 5,
          onChanged: onEnergyChanged,
        ),
        _SliderField(
          label: l10n.get('hungerLevel'),
          value: hungerLevel,
          min: 1,
          max: 5,
          onChanged: onHungerChanged,
        ),
        _SliderField(
          label: l10n.get('sleepQuality'),
          value: sleepQuality,
          min: 1,
          max: 5,
          onChanged: onSleepChanged,
        ),
        const SizedBox(height: 8),
        DropdownButtonFormField<CheckinMood>(
          value: mood,
          decoration: InputDecoration(labelText: l10n.get('mood')),
          items: CheckinMood.values
              .where((item) => item != CheckinMood.unknown)
              .map(
                (item) => DropdownMenuItem(
                  value: item,
                  child: Text(checkinMoodLabel(context, item)),
                ),
              )
              .toList(growable: false),
          onChanged: (value) {
            if (value != null) onMoodChanged(value);
          },
        ),
        const SizedBox(height: 12),
        TextField(
          controller: symptomsController,
          maxLines: 3,
          decoration: InputDecoration(labelText: l10n.get('symptomsNotes')),
        ),
        const SizedBox(height: 12),
        TextField(
          controller: generalController,
          maxLines: 3,
          decoration: InputDecoration(labelText: l10n.get('generalNotes')),
        ),
      ],
    );
  }
}

class _SliderField extends StatelessWidget {
  const _SliderField({
    required this.label,
    required this.value,
    required this.min,
    required this.max,
    required this.onChanged,
  });

  final String label;
  final int value;
  final int min;
  final int max;
  final ValueChanged<int> onChanged;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('$label: $value'),
        Slider(
          value: value.toDouble(),
          min: min.toDouble(),
          max: max.toDouble(),
          divisions: max - min,
          label: '$value',
          onChanged: (value) => onChanged(value.round()),
        ),
      ],
    );
  }
}
