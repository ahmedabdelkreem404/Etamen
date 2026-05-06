import 'package:etamen_app/features/notifications/data/models/register_notification_token_request.dart';

abstract class NotificationTokenProviderSource {
  Future<RegisterNotificationTokenRequest> getTokenRequest({String? locale});
}
