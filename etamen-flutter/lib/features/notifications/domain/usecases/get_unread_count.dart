import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_unread_count.dart';
import 'package:etamen_app/features/notifications/domain/repositories/notifications_repository.dart';

class GetUnreadCount {
  const GetUnreadCount(this._repository);

  final NotificationsRepository _repository;

  Future<ApiResult<NotificationUnreadCount>> call() {
    return _repository.getUnreadCount();
  }
}
