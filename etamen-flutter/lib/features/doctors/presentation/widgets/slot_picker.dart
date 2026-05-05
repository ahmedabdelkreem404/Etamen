import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor_slot.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class SlotPicker extends StatelessWidget {
  const SlotPicker({
    required this.slots,
    required this.selectedSlot,
    required this.onSelected,
    super.key,
  });

  final List<DoctorSlot> slots;
  final DoctorSlot? selectedSlot;
  final ValueChanged<DoctorSlot> onSelected;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    if (slots.isEmpty) {
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 24),
        child: Center(child: Text(l10n.get('emptySlots'))),
      );
    }

    final groups = <String, List<DoctorSlot>>{};
    for (final slot in slots) {
      final day = DateFormat('yyyy-MM-dd').format(slot.startsAt.toLocal());
      groups.putIfAbsent(day, () => []).add(slot);
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          l10n.get('availableSlots'),
          style: Theme.of(context).textTheme.titleMedium,
        ),
        const SizedBox(height: 12),
        for (final entry in groups.entries) ...[
          Text(
            DateFormat('EEE, d MMM').format(DateTime.parse(entry.key)),
            style: Theme.of(context).textTheme.labelLarge,
          ),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: entry.value.map((slot) {
              final selected = selectedSlot?.id == slot.id;
              return ChoiceChip(
                selected: selected,
                onSelected: (_) => onSelected(slot),
                selectedColor: AppColors.primary,
                labelStyle: TextStyle(
                  color: selected ? Colors.white : AppColors.text,
                ),
                label: Text(
                  DateFormat('HH:mm').format(slot.startsAt.toLocal()),
                ),
              );
            }).toList(),
          ),
          const SizedBox(height: 16),
        ],
      ],
    );
  }
}
