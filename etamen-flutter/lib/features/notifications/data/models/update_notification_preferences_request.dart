import 'package:etamen_app/features/notifications/domain/entities/notification_preference.dart';

class UpdateNotificationPreferencesRequest {
  const UpdateNotificationPreferencesRequest({required this.preferences});

  final List<NotificationPreference> preferences;

  Map<String, dynamic> toJson() {
    return {
      'preferences': preferences
          .map(
            (preference) => {
              'channel': preference.channel.wireValue,
              'category': preference.category.wireValue,
              'is_enabled': preference.isEnabled,
              if (preference.quietHoursStart?.trim().isNotEmpty == true)
                'quiet_hours_start': preference.quietHoursStart,
              if (preference.quietHoursEnd?.trim().isNotEmpty == true)
                'quiet_hours_end': preference.quietHoursEnd,
              if (preference.timezone?.trim().isNotEmpty == true)
                'timezone': preference.timezone,
            },
          )
          .toList(growable: false),
    };
  }
}
