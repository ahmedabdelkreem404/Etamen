import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/notifications/data/models/register_notification_token_request.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_token.dart';
import 'package:etamen_app/features/notifications/domain/repositories/notifications_repository.dart';

class RegisterNotificationToken {
  const RegisterNotificationToken(this._repository);

  final NotificationsRepository _repository;

  Future<ApiResult<NotificationToken>> call(
    RegisterNotificationTokenRequest request,
  ) {
    return _repository.registerToken(request);
  }
}
