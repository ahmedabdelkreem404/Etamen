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
      return Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 22),
        decoration: BoxDecoration(
          color: AppColors.pageBackground,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: AppColors.softBorder),
        ),
        child: Column(
          children: [
            Container(
              width: 46,
              height: 46,
              decoration: BoxDecoration(
                color: AppColors.medicalMint,
                borderRadius: BorderRadius.circular(16),
              ),
              child: const Icon(
                Icons.event_busy_outlined,
                color: AppColors.primary,
              ),
            ),
            const SizedBox(height: 10),
            Text(
              l10n.get('emptySlots'),
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                color: AppColors.softText,
                fontWeight: FontWeight.w700,
                height: 1.35,
              ),
            ),
          ],
        ),
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
          style: Theme.of(
            context,
          ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
        ),
        const SizedBox(height: 12),
        for (final entry in groups.entries) ...[
          Row(
            children: [
              Container(
                width: 30,
                height: 30,
                decoration: BoxDecoration(
                  color: AppColors.appointmentOrange.withValues(alpha: 0.14),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(
                  Icons.calendar_today_outlined,
                  size: 16,
                  color: AppColors.appointmentOrange,
                ),
              ),
              const SizedBox(width: 8),
              Text(
                DateFormat('EEE, d MMM').format(DateTime.parse(entry.key)),
                style: Theme.of(context).textTheme.labelLarge?.copyWith(
                  color: AppColors.text,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ],
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
                backgroundColor: Colors.white,
                side: BorderSide(
                  color: selected ? AppColors.primary : AppColors.softBorder,
                ),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(999),
                ),
                labelStyle: TextStyle(
                  color: selected ? Colors.white : AppColors.text,
                  fontWeight: FontWeight.w800,
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
