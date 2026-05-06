import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_preference.dart';
import 'package:etamen_app/features/notifications/presentation/widgets/notification_labels.dart';
import 'package:flutter/material.dart';

class NotificationPreferenceTile extends StatelessWidget {
  const NotificationPreferenceTile({
    required this.preference,
    required this.onChanged,
    super.key,
  });

  final NotificationPreference preference;
  final ValueChanged<bool> onChanged;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final quietHours = [
      preference.quietHoursStart,
      preference.quietHoursEnd,
    ].where((value) => value?.isNotEmpty == true).join(' - ');

    return SwitchListTile(
      value: preference.isEnabled,
      onChanged: onChanged,
      title: Text(notificationChannelLabel(context, preference.channel)),
      subtitle: quietHours.isEmpty
          ? Text(l10n.get('notificationChannel'))
          : Text('${l10n.get('quietHours')}: $quietHours'),
    );
  }
}
