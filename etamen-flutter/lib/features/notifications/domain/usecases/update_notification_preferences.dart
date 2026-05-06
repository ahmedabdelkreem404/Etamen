import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/notifications/data/models/update_notification_preferences_request.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_preference.dart';
import 'package:etamen_app/features/notifications/domain/repositories/notifications_repository.dart';

class UpdateNotificationPreferences {
  const UpdateNotificationPreferences(this._repository);

  final NotificationsRepository _repository;

  Future<ApiResult<List<NotificationPreference>>> call(
    UpdateNotificationPreferencesRequest request,
  ) {
    return _repository.updatePreferences(request);
  }
}
