import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/notifications/domain/repositories/notifications_repository.dart';

class MarkAllNotificationsRead {
  const MarkAllNotificationsRead(this._repository);

  final NotificationsRepository _repository;

  Future<ApiResult<int>> call() {
    return _repository.markAllRead();
  }
}
