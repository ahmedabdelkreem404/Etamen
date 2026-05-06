import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_token.dart';
import 'package:etamen_app/features/notifications/domain/repositories/notifications_repository.dart';

class GetNotificationTokens {
  const GetNotificationTokens(this._repository);

  final NotificationsRepository _repository;

  Future<ApiResult<List<NotificationToken>>> call() {
    return _repository.getTokens();
  }
}
