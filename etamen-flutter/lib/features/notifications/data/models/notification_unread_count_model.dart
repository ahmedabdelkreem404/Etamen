import 'package:etamen_app/features/notifications/domain/entities/notification_unread_count.dart';

class NotificationUnreadCountModel extends NotificationUnreadCount {
  const NotificationUnreadCountModel({required super.unreadCount});

  factory NotificationUnreadCountModel.fromJson(Map<String, dynamic> json) {
    final value = json['unread_count'];
    return NotificationUnreadCountModel(
      unreadCount: value is num ? value.toInt() : int.tryParse('$value') ?? 0,
    );
  }
}
