import 'package:etamen_app/features/notifications/data/models/notification_data_sanitizer.dart';
import 'package:etamen_app/features/notifications/domain/entities/app_notification.dart';

class NotificationModel extends AppNotification {
  NotificationModel({
    required super.id,
    required super.category,
    required super.type,
    required super.title,
    required super.body,
    required super.priority,
    super.data,
    super.readAt,
    super.actionUrl,
    super.createdAt,
    super.updatedAt,
  });

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    final data = _asMap(json['data']) ?? const <String, dynamic>{};
    return NotificationModel(
      id: _toInt(json['id']) ?? 0,
      category: NotificationCategory.fromWire(json['category']),
      type: _string(json['type']) ?? '',
      title: _string(json['title']) ?? '',
      body: _string(json['body']) ?? '',
      data: const NotificationDataSanitizer().sanitize(data),
      priority: NotificationPriority.fromWire(json['priority']),
      readAt: _toDateTime(json['read_at']),
      actionUrl: _string(json['action_url']),
      createdAt: _toDateTime(json['created_at']),
      updatedAt: _toDateTime(json['updated_at']),
    );
  }
}

int? _toInt(Object? value) {
  if (value == null) return null;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString());
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

Map<String, dynamic>? _asMap(Object? value) {
  if (value is Map<String, dynamic>) return value;
  if (value is Map) {
    return value.map((key, item) => MapEntry(key.toString(), item));
  }
  return null;
}
