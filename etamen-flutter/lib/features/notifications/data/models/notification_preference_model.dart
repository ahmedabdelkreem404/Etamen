import 'package:etamen_app/features/notifications/domain/entities/app_notification.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_preference.dart';

class NotificationPreferenceModel extends NotificationPreference {
  const NotificationPreferenceModel({
    super.id,
    required super.channel,
    required super.category,
    required super.isEnabled,
    super.quietHoursStart,
    super.quietHoursEnd,
    super.timezone,
    super.metadata,
  });

  factory NotificationPreferenceModel.fromJson(Map<String, dynamic> json) {
    return NotificationPreferenceModel(
      id: _toInt(json['id']),
      channel: NotificationChannel.fromWire(json['channel']),
      category: NotificationCategory.fromWire(json['category']),
      isEnabled: _toBool(json['is_enabled']) ?? true,
      quietHoursStart: _string(json['quiet_hours_start']),
      quietHoursEnd: _string(json['quiet_hours_end']),
      timezone: _string(json['timezone']),
      metadata: _asMap(json['metadata']) ?? const {},
    );
  }
}

int? _toInt(Object? value) {
  if (value == null) return null;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString());
}

bool? _toBool(Object? value) {
  if (value == null) return null;
  if (value is bool) return value;
  if (value is num) return value != 0;
  final text = value.toString().toLowerCase();
  if (text == 'true' || text == '1') return true;
  if (text == 'false' || text == '0') return false;
  return null;
}

String? _string(Object? value) {
  if (value == null) return null;
  final text = value.toString();
  return text.isEmpty ? null : text;
}

Map<String, dynamic>? _asMap(Object? value) {
  if (value is Map<String, dynamic>) return value;
  if (value is Map) {
    return value.map((key, item) => MapEntry(key.toString(), item));
  }
  return null;
}
