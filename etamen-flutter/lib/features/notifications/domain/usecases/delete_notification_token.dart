import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/notifications/domain/repositories/notifications_repository.dart';

class DeleteNotificationToken {
  const DeleteNotificationToken(this._repository);

  final NotificationsRepository _repository;

  Future<ApiResult<void>> call(int id) {
    return _repository.deleteToken(id);
  }
}
