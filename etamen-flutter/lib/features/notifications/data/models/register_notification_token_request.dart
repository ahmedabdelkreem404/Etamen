import 'package:etamen_app/features/notifications/domain/entities/notification_token.dart';

class RegisterNotificationTokenRequest {
  const RegisterNotificationTokenRequest({
    required this.token,
    required this.provider,
    required this.deviceType,
    this.deviceName,
    this.appVersion,
    this.locale,
    this.timezone,
    this.metadata = const {},
  });

  final String token;
  final NotificationTokenProvider provider;
  final NotificationDeviceType deviceType;
  final String? deviceName;
  final String? appVersion;
  final String? locale;
  final String? timezone;
  final Map<String, dynamic> metadata;

  Map<String, dynamic> toJson() {
    return {
      'token': token,
      'provider': provider.wireValue,
      'device_type': deviceType.wireValue,
      if (deviceName?.trim().isNotEmpty == true)
        'device_name': deviceName!.trim(),
      if (appVersion?.trim().isNotEmpty == true)
        'app_version': appVersion!.trim(),
      if (locale?.trim().isNotEmpty == true) 'locale': locale!.trim(),
      if (timezone?.trim().isNotEmpty == true) 'timezone': timezone!.trim(),
      if (metadata.isNotEmpty) 'metadata': metadata,
    };
  }
}
