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
          color: AppColors.cream,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: AppColors.softBorder),
        ),
        child: Column(
          children: [
            Container(
              width: 50,
              height: 50,
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
                fontWeight: FontWeight.w800,
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
          _DayHeader(date: DateTime.parse(entry.key)),
          const SizedBox(height: 10),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: entry.value.map((slot) {
              final selected = selectedSlot?.id == slot.id;
              return _TimeTile(
                label: DateFormat('HH:mm').format(slot.startsAt.toLocal()),
                selected: selected,
                onTap: () => onSelected(slot),
              );
            }).toList(),
          ),
          const SizedBox(height: 18),
        ],
      ],
    );
  }
}

class _DayHeader extends StatelessWidget {
  const _DayHeader({required this.date});

  final DateTime date;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: AppColors.medicalMint,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 32,
            height: 32,
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
            DateFormat('EEE, d MMM').format(date),
            style: Theme.of(context).textTheme.labelLarge?.copyWith(
              color: AppColors.primaryDark,
              fontWeight: FontWeight.w900,
            ),
          ),
        ],
      ),
    );
  }
}

class _TimeTile extends StatelessWidget {
  const _TimeTile({
    required this.label,
    required this.selected,
    required this.onTap,
  });

  final String label;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        borderRadius: BorderRadius.circular(14),
        onTap: onTap,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 160),
          width: 88,
          height: 44,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: selected ? AppColors.appointmentOrange : Colors.white,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: selected
                  ? AppColors.appointmentOrange
                  : AppColors.softBorder,
            ),
            boxShadow: selected
                ? [
                    BoxShadow(
                      color: AppColors.appointmentOrange.withValues(
                        alpha: 0.22,
                      ),
                      blurRadius: 14,
                      offset: const Offset(0, 8),
                    ),
                  ]
                : null,
          ),
          child: Text(
            label,
            style: Theme.of(context).textTheme.labelLarge?.copyWith(
              color: selected ? Colors.white : AppColors.text,
              fontWeight: FontWeight.w900,
            ),
          ),
        ),
      ),
    );
  }
}
