import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/network/api_client.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/notifications/data/models/notification_model.dart';
import 'package:etamen_app/features/notifications/data/models/notification_preference_model.dart';
import 'package:etamen_app/features/notifications/data/models/notification_token_model.dart';
import 'package:etamen_app/features/notifications/data/models/notification_unread_count_model.dart';
import 'package:etamen_app/features/notifications/data/models/register_notification_token_request.dart';
import 'package:etamen_app/features/notifications/data/models/update_notification_preferences_request.dart';

class NotificationsRemoteDataSource {
  const NotificationsRemoteDataSource(this._client);

  final ApiClient _client;

  Future<ApiResult<List<NotificationModel>>> getNotifications() {
    return _client.get<List<NotificationModel>>(
      ApiEndpoints.notifications,
      queryParameters: const {'per_page': 30},
      parser: (raw) => _parseList(
        raw,
      ).map(NotificationModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<NotificationUnreadCountModel>> getUnreadCount() {
    return _client.get<NotificationUnreadCountModel>(
      ApiEndpoints.notificationsUnreadCount,
      parser: (raw) => NotificationUnreadCountModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<NotificationModel>> getNotificationDetails(int id) {
    return _client.get<NotificationModel>(
      ApiEndpoints.notification(id),
      parser: (raw) => NotificationModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<NotificationModel>> markRead(int id) {
    return _client.post<NotificationModel>(
      ApiEndpoints.notificationRead(id),
      parser: (raw) => NotificationModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<int>> markAllRead() {
    return _client.post<int>(
      ApiEndpoints.notificationsReadAll,
      parser: (raw) => _toInt(_unwrapMap(raw)['updated_count']) ?? 0,
    );
  }

  Future<ApiResult<void>> deleteNotification(int id) {
    return _client.delete<void>(ApiEndpoints.notification(id), parser: (_) {});
  }

  Future<ApiResult<List<NotificationPreferenceModel>>> getPreferences() {
    return _client.get<List<NotificationPreferenceModel>>(
      ApiEndpoints.notificationPreferences,
      parser: (raw) => _parseList(
        raw,
      ).map(NotificationPreferenceModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<List<NotificationPreferenceModel>>> updatePreferences(
    UpdateNotificationPreferencesRequest request,
  ) {
    return _client.put<List<NotificationPreferenceModel>>(
      ApiEndpoints.notificationPreferences,
      data: request.toJson(),
      parser: (raw) => _parseList(
        raw,
      ).map(NotificationPreferenceModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<List<NotificationTokenModel>>> getTokens() {
    return _client.get<List<NotificationTokenModel>>(
      ApiEndpoints.notificationTokens,
      parser: (raw) => _parseList(
        raw,
      ).map(NotificationTokenModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<NotificationTokenModel>> registerToken(
    RegisterNotificationTokenRequest request,
  ) {
    return _client.post<NotificationTokenModel>(
      ApiEndpoints.notificationTokens,
      data: request.toJson(),
      parser: (raw) => NotificationTokenModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<void>> deleteToken(int id) {
    return _client.delete<void>(
      ApiEndpoints.notificationToken(id),
      parser: (_) {},
    );
  }

  static List<Map<String, dynamic>> _parseList(Object? raw) {
    final value = _unwrapCollection(raw);
    if (value is! List) return const [];
    return value
        .whereType<Map>()
        .map(
          (item) => item.map((key, value) => MapEntry(key.toString(), value)),
        )
        .toList(growable: false);
  }

  static Object? _unwrapCollection(Object? raw) {
    if (raw is Map) {
      return raw['data'] ??
          raw['items'] ??
          raw['notifications'] ??
          raw['preferences'] ??
          raw['tokens'];
    }
    return raw;
  }

  static Map<String, dynamic> _unwrapMap(Object? raw) {
    if (raw is Map<String, dynamic>) {
      final nested =
          raw['data'] ??
          raw['notification'] ??
          raw['preference'] ??
          raw['token'];
      if (nested is Map<String, dynamic>) return nested;
      if (nested is Map) {
        return nested.map((key, value) => MapEntry(key.toString(), value));
      }
      return raw;
    }
    if (raw is Map) {
      return raw.map((key, value) => MapEntry(key.toString(), value));
    }
    return const {};
  }

  static int? _toInt(Object? value) {
    if (value == null) return null;
    if (value is num) return value.toInt();
    return int.tryParse(value.toString());
  }
}
