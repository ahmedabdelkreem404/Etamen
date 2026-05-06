import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/notifications/data/models/register_notification_token_request.dart';
import 'package:etamen_app/features/notifications/data/models/update_notification_preferences_request.dart';
import 'package:etamen_app/features/notifications/domain/entities/app_notification.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_preference.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_token.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_unread_count.dart';

abstract class NotificationsRepository {
  Future<ApiResult<List<AppNotification>>> getNotifications();

  Future<ApiResult<NotificationUnreadCount>> getUnreadCount();

  Future<ApiResult<AppNotification>> getNotificationDetails(int id);

  Future<ApiResult<AppNotification>> markRead(int id);

  Future<ApiResult<int>> markAllRead();

  Future<ApiResult<void>> deleteNotification(int id);

  Future<ApiResult<List<NotificationPreference>>> getPreferences();

  Future<ApiResult<List<NotificationPreference>>> updatePreferences(
    UpdateNotificationPreferencesRequest request,
  );

  Future<ApiResult<List<NotificationToken>>> getTokens();

  Future<ApiResult<NotificationToken>> registerToken(
    RegisterNotificationTokenRequest request,
  );

  Future<ApiResult<void>> deleteToken(int id);
}
