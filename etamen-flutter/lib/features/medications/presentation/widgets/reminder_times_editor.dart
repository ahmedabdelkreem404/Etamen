import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/features/medications/data/models/create_medication_reminder_request.dart';
import 'package:flutter/material.dart';

class ReminderTimesEditor extends StatelessWidget {
  const ReminderTimesEditor({
    required this.times,
    required this.onChanged,
    super.key,
  });

  final List<ReminderTimeInput> times;
  final ValueChanged<List<ReminderTimeInput>> onChanged;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Expanded(
              child: Text(
                l10n.get('reminderTimes'),
                style: Theme.of(context).textTheme.titleMedium,
              ),
            ),
            TextButton.icon(
              onPressed: times.length >= 10
                  ? null
                  : () => onChanged([
                      ...times,
                      const ReminderTimeInput(timeOfDay: '08:00'),
                    ]),
              icon: const Icon(Icons.add),
              label: Text(l10n.get('add')),
            ),
          ],
        ),
        const SizedBox(height: 8),
        if (times.isEmpty)
          Text(l10n.get('asNeededNoTimes'))
        else
          ...times.asMap().entries.map((entry) {
            final index = entry.key;
            final time = entry.value;
            return Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: Row(
                children: [
                  Expanded(
                    child: TextFormField(
                      initialValue: time.timeOfDay,
                      decoration: InputDecoration(labelText: l10n.get('time')),
                      onChanged: (value) => _update(
                        index,
                        ReminderTimeInput(timeOfDay: value, label: time.label),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  IconButton(
                    tooltip: l10n.get('remove'),
                    onPressed: () {
                      final next = List<ReminderTimeInput>.of(times)
                        ..removeAt(index);
                      onChanged(next);
                    },
                    icon: const Icon(Icons.close),
                  ),
                ],
              ),
            );
          }),
      ],
    );
  }

  void _update(int index, ReminderTimeInput nextTime) {
    final next = List<ReminderTimeInput>.of(times);
    next[index] = nextTime;
    onChanged(next);
  }
}
