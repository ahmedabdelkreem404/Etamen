import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/notifications/data/datasources/notifications_remote_data_source.dart';
import 'package:etamen_app/features/notifications/data/models/register_notification_token_request.dart';
import 'package:etamen_app/features/notifications/data/models/update_notification_preferences_request.dart';
import 'package:etamen_app/features/notifications/domain/entities/app_notification.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_preference.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_token.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_unread_count.dart';
import 'package:etamen_app/features/notifications/domain/repositories/notifications_repository.dart';

class NotificationsRepositoryImpl implements NotificationsRepository {
  const NotificationsRepositoryImpl(this._remote);

  final NotificationsRemoteDataSource _remote;

  @override
  Future<ApiResult<List<AppNotification>>> getNotifications() {
    return _remote.getNotifications();
  }

  @override
  Future<ApiResult<NotificationUnreadCount>> getUnreadCount() {
    return _remote.getUnreadCount();
  }

  @override
  Future<ApiResult<AppNotification>> getNotificationDetails(int id) {
    return _remote.getNotificationDetails(id);
  }

  @override
  Future<ApiResult<AppNotification>> markRead(int id) {
    return _remote.markRead(id);
  }

  @override
  Future<ApiResult<int>> markAllRead() {
    return _remote.markAllRead();
  }

  @override
  Future<ApiResult<void>> deleteNotification(int id) {
    return _remote.deleteNotification(id);
  }

  @override
  Future<ApiResult<List<NotificationPreference>>> getPreferences() {
    return _remote.getPreferences();
  }

  @override
  Future<ApiResult<List<NotificationPreference>>> updatePreferences(
    UpdateNotificationPreferencesRequest request,
  ) {
    return _remote.updatePreferences(request);
  }

  @override
  Future<ApiResult<List<NotificationToken>>> getTokens() {
    return _remote.getTokens();
  }

  @override
  Future<ApiResult<NotificationToken>> registerToken(
    RegisterNotificationTokenRequest request,
  ) {
    return _remote.registerToken(request);
  }

  @override
  Future<ApiResult<void>> deleteToken(int id) {
    return _remote.deleteToken(id);
  }
}
