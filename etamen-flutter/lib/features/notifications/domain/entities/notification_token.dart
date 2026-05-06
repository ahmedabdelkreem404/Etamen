class NotificationToken {
  const NotificationToken({
    required this.id,
    required this.provider,
    required this.deviceType,
    this.deviceName,
    this.appVersion,
    this.locale,
    this.timezone,
    this.isActive = true,
    this.lastSeenAt,
    this.createdAt,
  });

  final int id;
  final NotificationTokenProvider provider;
  final NotificationDeviceType deviceType;
  final String? deviceName;
  final String? appVersion;
  final String? locale;
  final String? timezone;
  final bool isActive;
  final DateTime? lastSeenAt;
  final DateTime? createdAt;
}

enum NotificationTokenProvider {
  fcm('fcm'),
  apns('apns'),
  webPush('web_push'),
  local('local'),
  unknown('unknown');

  const NotificationTokenProvider(this.wireValue);

  final String wireValue;

  static NotificationTokenProvider fromWire(Object? value) {
    final normalized = value?.toString();
    return NotificationTokenProvider.values.firstWhere(
      (item) => item.wireValue == normalized,
      orElse: () => NotificationTokenProvider.unknown,
    );
  }
}

enum NotificationDeviceType {
  android('android'),
  ios('ios'),
  web('web'),
  unknown('unknown');

  const NotificationDeviceType(this.wireValue);

  final String wireValue;

  static NotificationDeviceType fromWire(Object? value) {
    final normalized = value?.toString();
    return NotificationDeviceType.values.firstWhere(
      (item) => item.wireValue == normalized,
      orElse: () => NotificationDeviceType.unknown,
    );
  }
}
