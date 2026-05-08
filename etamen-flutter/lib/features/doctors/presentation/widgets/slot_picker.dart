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
    final sortedEntries = groups.entries.toList()
      ..sort((a, b) => a.key.compareTo(b.key));
    for (final entry in sortedEntries) {
      entry.value.sort((a, b) => a.startsAt.compareTo(b.startsAt));
    }
    final selectedDay = selectedSlot == null
        ? sortedEntries.first.key
        : DateFormat('yyyy-MM-dd').format(selectedSlot!.startsAt.toLocal());
    final activeEntry = sortedEntries.firstWhere(
      (entry) => entry.key == selectedDay,
      orElse: () => sortedEntries.first,
    );

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
        SizedBox(
          height: 88,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: sortedEntries.length,
            separatorBuilder: (_, _) => const SizedBox(width: 10),
            itemBuilder: (context, index) {
              final entry = sortedEntries[index];
              return _DayHeader(
                date: DateTime.parse(entry.key),
                selected: entry.key == activeEntry.key,
                onTap: () => onSelected(entry.value.first),
              );
            },
          ),
        ),
        const SizedBox(height: 12),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: activeEntry.value.map((slot) {
            final selected = selectedSlot?.id == slot.id;
            return _TimeTile(
              label: DateFormat('HH:mm').format(slot.startsAt.toLocal()),
              selected: selected,
              onTap: () => onSelected(slot),
            );
          }).toList(),
        ),
      ],
    );
  }
}

class _DayHeader extends StatelessWidget {
  const _DayHeader({
    required this.date,
    required this.selected,
    required this.onTap,
  });

  final DateTime date;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final color = selected ? AppColors.medicalAccent : Colors.white;
    final textColor = selected ? Colors.white : AppColors.text;
    return Material(
      color: Colors.transparent,
      child: InkWell(
        borderRadius: BorderRadius.circular(10),
        onTap: onTap,
        child: Container(
          width: 76,
          height: 82,
          padding: const EdgeInsets.symmetric(vertical: 8),
          decoration: BoxDecoration(
            color: color,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(
              color: selected ? AppColors.medicalAccent : AppColors.softBorder,
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: selected ? 0.10 : 0.04),
                blurRadius: 14,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                DateFormat('EEE').format(date),
                style: Theme.of(context).textTheme.labelSmall?.copyWith(
                  color: textColor,
                  fontWeight: FontWeight.w900,
                ),
              ),
              const SizedBox(height: 3),
              Text(
                DateFormat('d').format(date),
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  color: textColor,
                  fontWeight: FontWeight.w900,
                ),
              ),
              const SizedBox(height: 3),
              Text(
                DateFormat('MMM').format(date),
                style: Theme.of(context).textTheme.labelSmall?.copyWith(
                  color: textColor.withValues(alpha: 0.82),
                  fontWeight: FontWeight.w700,
                  fontSize: 9,
                ),
              ),
            ],
          ),
        ),
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
          width: 92,
          height: 48,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: selected ? AppColors.medicalAccent : Colors.white,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(
              color: selected ? AppColors.medicalAccent : AppColors.softBorder,
            ),
            boxShadow: selected
                ? [
                    BoxShadow(
                      color: AppColors.medicalAccent.withValues(alpha: 0.22),
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
