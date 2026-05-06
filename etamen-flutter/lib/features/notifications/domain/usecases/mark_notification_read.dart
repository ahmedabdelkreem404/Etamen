import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/notifications/domain/entities/app_notification.dart';
import 'package:etamen_app/features/notifications/domain/repositories/notifications_repository.dart';

class MarkNotificationRead {
  const MarkNotificationRead(this._repository);

  final NotificationsRepository _repository;

  Future<ApiResult<AppNotification>> call(int id) {
    return _repository.markRead(id);
  }
}
