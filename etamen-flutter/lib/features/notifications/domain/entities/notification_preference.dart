import 'package:etamen_app/features/notifications/domain/entities/app_notification.dart';

class NotificationPreference {
  const NotificationPreference({
    this.id,
    required this.channel,
    required this.category,
    required this.isEnabled,
    this.quietHoursStart,
    this.quietHoursEnd,
    this.timezone,
    this.metadata = const {},
  });

  final int? id;
  final NotificationChannel channel;
  final NotificationCategory category;
  final bool isEnabled;
  final String? quietHoursStart;
  final String? quietHoursEnd;
  final String? timezone;
  final Map<String, dynamic> metadata;

  NotificationPreference copyWith({bool? isEnabled}) {
    return NotificationPreference(
      id: id,
      channel: channel,
      category: category,
      isEnabled: isEnabled ?? this.isEnabled,
      quietHoursStart: quietHoursStart,
      quietHoursEnd: quietHoursEnd,
      timezone: timezone,
      metadata: metadata,
    );
  }
}

enum NotificationChannel {
  inApp('in_app'),
  push('push'),
  email('email'),
  sms('sms'),
  whatsapp('whatsapp'),
  unknown('unknown');

  const NotificationChannel(this.wireValue);

  final String wireValue;

  static NotificationChannel fromWire(Object? value) {
    final normalized = value?.toString();
    return NotificationChannel.values.firstWhere(
      (item) => item.wireValue == normalized,
      orElse: () => NotificationChannel.unknown,
    );
  }
}
