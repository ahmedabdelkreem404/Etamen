import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_preference.dart';
import 'package:etamen_app/features/notifications/domain/repositories/notifications_repository.dart';

class GetNotificationPreferences {
  const GetNotificationPreferences(this._repository);

  final NotificationsRepository _repository;

  Future<ApiResult<List<NotificationPreference>>> call() {
    return _repository.getPreferences();
  }
}
