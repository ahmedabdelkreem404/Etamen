import 'package:etamen_app/features/notifications/domain/entities/app_notification.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_preference.dart';
import 'package:etamen_app/features/notifications/presentation/widgets/notification_labels.dart';
import 'package:etamen_app/features/notifications/presentation/widgets/notification_preference_tile.dart';
import 'package:flutter/material.dart';

class NotificationPreferencesSection extends StatelessWidget {
  const NotificationPreferencesSection({
    required this.category,
    required this.preferences,
    required this.onToggle,
    super.key,
  });

  final NotificationCategory category;
  final List<NotificationPreference> preferences;
  final void Function(NotificationPreference preference, bool enabled) onToggle;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Text(
                notificationCategoryLabel(context, category),
                style: Theme.of(context).textTheme.titleMedium,
              ),
            ),
            ...preferences.map(
              (preference) => NotificationPreferenceTile(
                preference: preference,
                onChanged: (enabled) => onToggle(preference, enabled),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
