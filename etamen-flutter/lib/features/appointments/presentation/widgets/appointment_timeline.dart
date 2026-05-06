import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment_status_history.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class AppointmentTimeline extends StatelessWidget {
  const AppointmentTimeline({required this.items, super.key});

  final List<AppointmentStatusHistory> items;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    if (items.isEmpty) {
      return Card(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Text(l10n.get('noAdditionalDetails')),
        ),
      );
    }

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              l10n.get('appointmentTimeline'),
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 12),
            ...items.map((item) => _TimelineItem(item: item)),
          ],
        ),
      ),
    );
  }
}

class _TimelineItem extends StatelessWidget {
  const _TimelineItem({required this.item});

  final AppointmentStatusHistory item;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final date = item.createdAt == null
        ? null
        : DateFormat(
            l10n.isArabic ? 'd MMM yyyy، h:mm a' : 'MMM d, yyyy h:mm a',
          ).format(item.createdAt!.toLocal());

    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.circle, size: 12, color: AppColors.primary),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.toStatus,
                  style: const TextStyle(fontWeight: FontWeight.w800),
                ),
                if (date != null) Text(date),
                if (item.reason?.isNotEmpty == true)
                  Text(
                    item.reason!,
                    style: const TextStyle(color: AppColors.muted),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
