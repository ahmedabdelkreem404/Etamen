import 'dart:math';

import 'package:etamen_app/core/notifications/notification_token_provider.dart';
import 'package:etamen_app/features/notifications/data/models/register_notification_token_request.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_token.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

abstract class LocalNotificationTokenStore {
  Future<String?> read(String key);

  Future<void> write(String key, String value);
}

class SecureLocalNotificationTokenStore implements LocalNotificationTokenStore {
  const SecureLocalNotificationTokenStore({
    FlutterSecureStorage storage = const FlutterSecureStorage(),
  }) : _storage = storage;

  final FlutterSecureStorage _storage;

  @override
  Future<String?> read(String key) {
    return _storage.read(key: key);
  }

  @override
  Future<void> write(String key, String value) {
    return _storage.write(key: key, value: value);
  }
}

class LocalNotificationTokenProvider
    implements NotificationTokenProviderSource {
  LocalNotificationTokenProvider({LocalNotificationTokenStore? store})
    : _store = store ?? const SecureLocalNotificationTokenStore();

  static const _tokenKey = 'etamen.local_notification_token';

  final LocalNotificationTokenStore _store;

  @override
  Future<RegisterNotificationTokenRequest> getTokenRequest({
    String? locale,
  }) async {
    final token = await _readOrCreateToken();
    return RegisterNotificationTokenRequest(
      token: token,
      provider: NotificationTokenProvider.local,
      deviceType: _deviceType(),
      deviceName: _deviceName(),
      appVersion: 'flutter-local',
      locale: locale ?? 'ar',
      timezone: 'Africa/Cairo',
      metadata: const {'environment': 'local_foundation'},
    );
  }

  Future<String> _readOrCreateToken() async {
    final existing = await _store.read(_tokenKey);
    if (existing?.isNotEmpty == true) return existing!;
    final random = Random.secure();
    final bytes = List<int>.generate(24, (_) => random.nextInt(256));
    final token =
        'local-${DateTime.now().millisecondsSinceEpoch}-${bytes.map((byte) => byte.toRadixString(16).padLeft(2, '0')).join()}';
    await _store.write(_tokenKey, token);
    return token;
  }

  NotificationDeviceType _deviceType() {
    if (kIsWeb) return NotificationDeviceType.web;
    return switch (defaultTargetPlatform) {
      TargetPlatform.android => NotificationDeviceType.android,
      TargetPlatform.iOS => NotificationDeviceType.ios,
      _ => NotificationDeviceType.unknown,
    };
  }

  String _deviceName() {
    if (kIsWeb) return 'Web';
    return switch (defaultTargetPlatform) {
      TargetPlatform.android => 'Android',
      TargetPlatform.iOS => 'iOS',
      TargetPlatform.macOS => 'macOS',
      TargetPlatform.windows => 'Windows',
      TargetPlatform.linux => 'Linux',
      TargetPlatform.fuchsia => 'Fuchsia',
    };
  }
}
