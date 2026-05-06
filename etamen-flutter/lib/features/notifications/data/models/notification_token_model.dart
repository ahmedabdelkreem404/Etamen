import 'package:etamen_app/features/notifications/domain/entities/notification_token.dart';

class NotificationTokenModel extends NotificationToken {
  const NotificationTokenModel({
    required super.id,
    required super.provider,
    required super.deviceType,
    super.deviceName,
    super.appVersion,
    super.locale,
    super.timezone,
    super.isActive,
    super.lastSeenAt,
    super.createdAt,
  });

  factory NotificationTokenModel.fromJson(Map<String, dynamic> json) {
    return NotificationTokenModel(
      id: _toInt(json['id']) ?? 0,
      provider: NotificationTokenProvider.fromWire(json['provider']),
      deviceType: NotificationDeviceType.fromWire(json['device_type']),
      deviceName: _string(json['device_name']),
      appVersion: _string(json['app_version']),
      locale: _string(json['locale']),
      timezone: _string(json['timezone']),
      isActive: _toBool(json['is_active']) ?? true,
      lastSeenAt: _toDateTime(json['last_seen_at']),
      createdAt: _toDateTime(json['created_at']),
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
  return value.toString() == '1';
}

String? _string(Object? value) {
  if (value == null) return null;
  final text = value.toString();
  return text.isEmpty ? null : text;
}

DateTime? _toDateTime(Object? value) {
  final text = _string(value);
  return text == null ? null : DateTime.tryParse(text);
}
