import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/core/notifications/local_notification_token_provider.dart';
import 'package:etamen_app/core/notifications/notification_token_provider.dart';
import 'package:etamen_app/features/notifications/data/models/notification_data_sanitizer.dart';
import 'package:etamen_app/features/notifications/data/models/notification_model.dart';
import 'package:etamen_app/features/notifications/data/models/notification_preference_model.dart';
import 'package:etamen_app/features/notifications/data/models/notification_token_model.dart';
import 'package:etamen_app/features/notifications/data/models/notification_unread_count_model.dart';
import 'package:etamen_app/features/notifications/data/models/register_notification_token_request.dart';
import 'package:etamen_app/features/notifications/data/models/update_notification_preferences_request.dart';
import 'package:etamen_app/features/notifications/domain/entities/app_notification.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_preference.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_token.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_unread_count.dart';
import 'package:etamen_app/features/notifications/domain/repositories/notifications_repository.dart';
import 'package:etamen_app/features/notifications/presentation/providers/notifications_providers.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('NotificationModel parses and sanitizes unsafe data', () {
    final notification = NotificationModel.fromJson({
      'id': 1,
      'category': 'appointments',
      'type': 'appointment_reminder',
      'title': 'Reminder',
      'body': 'Your appointment is soon',
      'priority': 'high',
      'read_at': null,
      'data': {
        'appointment_id': 12,
        'private_path': '/storage/private/file.pdf',
        'raw_prompt': 'full patient text',
        'provider_response': 'unsafe raw provider response',
        'nested': {'safe_id': 9, 'api_key': 'secret'},
      },
    });

    expect(notification.id, 1);
    expect(notification.category, NotificationCategory.appointments);
    expect(notification.priority, NotificationPriority.high);
    expect(notification.isRead, false);
    expect(notification.data['appointment_id'], 12);
    expect(notification.data.containsKey('private_path'), false);
    expect(notification.data.containsKey('raw_prompt'), false);
    expect(notification.data.containsKey('provider_response'), false);
    expect(notification.data['nested'], {'safe_id': 9});
  });

  test('NotificationPreferenceModel parses channel and category', () {
    final preference = NotificationPreferenceModel.fromJson({
      'id': 3,
      'channel': 'push',
      'category': 'labs',
      'is_enabled': false,
      'quiet_hours_start': '22:00',
      'quiet_hours_end': '07:00',
      'timezone': 'Africa/Cairo',
    });

    expect(preference.channel, NotificationChannel.push);
    expect(preference.category, NotificationCategory.labs);
    expect(preference.isEnabled, false);
    expect(preference.quietHoursStart, '22:00');
  });

  test('NotificationTokenModel parses local token safely', () {
    final token = NotificationTokenModel.fromJson({
      'id': 5,
      'provider': 'local',
      'device_type': 'android',
      'device_name': 'Android',
      'is_active': true,
    });

    expect(token.provider, NotificationTokenProvider.local);
    expect(token.deviceType, NotificationDeviceType.android);
    expect(token.isActive, true);
  });

  test('Notification enum mappings tolerate unknown values', () {
    expect(
      NotificationCategory.fromWire('care_plans'),
      NotificationCategory.carePlans,
    );
    expect(NotificationCategory.fromWire('bad'), NotificationCategory.unknown);
    expect(
      NotificationPriority.fromWire('urgent'),
      NotificationPriority.urgent,
    );
    expect(NotificationChannel.fromWire('in_app'), NotificationChannel.inApp);
    expect(
      NotificationTokenProvider.fromWire('web_push'),
      NotificationTokenProvider.webPush,
    );
    expect(NotificationDeviceType.fromWire('ios'), NotificationDeviceType.ios);
  });

  test('NotificationUnreadCountModel parses unread count', () {
    final count = NotificationUnreadCountModel.fromJson({'unread_count': '7'});

    expect(count.unreadCount, 7);
  });

  test(
    'RegisterNotificationTokenRequest excludes user and server key fields',
    () {
      final json = const RegisterNotificationTokenRequest(
        token: 'local-token',
        provider: NotificationTokenProvider.local,
        deviceType: NotificationDeviceType.android,
        deviceName: 'Android',
        appVersion: 'debug',
        locale: 'ar',
        timezone: 'Africa/Cairo',
        metadata: {'environment': 'local_foundation'},
      ).toJson();

      expect(json['token'], 'local-token');
      expect(json['provider'], 'local');
      expect(json['device_type'], 'android');
      expect(json.containsKey('user_id'), false);
      expect(json.containsKey('patient_user_id'), false);
      expect(json.containsKey('admin'), false);
      expect(json.containsKey('server_key'), false);
      expect(json.containsKey('fcm_server_key'), false);
      expect(json.containsKey('api_key'), false);
    },
  );

  test('UpdateNotificationPreferencesRequest excludes ownership fields', () {
    final json = const UpdateNotificationPreferencesRequest(
      preferences: [
        NotificationPreference(
          channel: NotificationChannel.inApp,
          category: NotificationCategory.system,
          isEnabled: true,
          quietHoursStart: '22:00',
          quietHoursEnd: '07:00',
          timezone: 'Africa/Cairo',
        ),
      ],
    ).toJson();

    final first = (json['preferences'] as List).first as Map<String, dynamic>;
    expect(first['channel'], 'in_app');
    expect(first['category'], 'system');
    expect(first['is_enabled'], true);
    expect(first.containsKey('user_id'), false);
    expect(first.containsKey('admin'), false);
    expect(json.containsKey('user_id'), false);
  });

  test('NotificationDataSanitizer removes unsafe keys and keeps safe ids', () {
    final data = const NotificationDataSanitizer().sanitize({
      'payment_id': 11,
      'lab_order_id': 4,
      'file_path': '/private/result.pdf',
      'hmac_secret': 'secret',
      'commission': '12.00',
      'provider_net': '55.00',
      'raw_provider_response': {'body': 'unsafe'},
    });

    expect(data['payment_id'], 11);
    expect(data['lab_order_id'], 4);
    expect(data.containsKey('file_path'), false);
    expect(data.containsKey('hmac_secret'), false);
    expect(data.containsKey('commission'), false);
    expect(data.containsKey('provider_net'), false);
    expect(data.containsKey('raw_provider_response'), false);
  });

  test('local token provider returns stable local token', () async {
    final store = MemoryNotificationTokenStore();
    final provider = LocalNotificationTokenProvider(store: store);

    final first = await provider.getTokenRequest();
    final second = await provider.getTokenRequest();

    expect(first.token, second.token);
    expect(first.provider, NotificationTokenProvider.local);
    expect(first.toJson().containsKey('server_key'), false);
  });

  test(
    'notifications controller loads filters marks read read-all and deletes',
    () async {
      var badgeRefreshes = 0;
      final repository = FakeNotificationsRepository();
      final controller = NotificationsController(
        repository,
        onBadgeChanged: () async => badgeRefreshes++,
      );

      await controller.load();
      expect(controller.state.items, hasLength(2));

      controller.selectFilter(NotificationFilter.unread);
      expect(controller.state.filteredItems, hasLength(1));

      final read = await controller.markRead(1);
      expect(read, true);
      expect(badgeRefreshes, 1);
      expect(controller.state.items.first.isRead, true);

      final allRead = await controller.markAllRead();
      expect(allRead, true);
      expect(badgeRefreshes, 2);

      final deleted = await controller.delete(2);
      expect(deleted, true);
      expect(controller.state.items, hasLength(1));
    },
  );

  test('preferences controller loads toggles and saves', () async {
    final repository = FakeNotificationsRepository();
    final controller = NotificationPreferencesController(repository);

    await controller.load();
    expect(controller.state.preferences, hasLength(2));

    final preference = controller.state.preferences.first;
    controller.toggle(preference, false);
    expect(controller.state.preferences.first.isEnabled, false);

    final saved = await controller.save();
    expect(saved, true);
    expect(repository.savedPreferences.first.isEnabled, false);
  });

  test('token controller registers and deletes local token safely', () async {
    final repository = FakeNotificationsRepository();
    final controller = NotificationTokenController(
      repository,
      const StubNotificationTokenProvider(),
    );

    final registered = await controller.registerLocalToken();
    expect(registered, true);
    expect(
      controller.state.tokens.first.provider,
      NotificationTokenProvider.local,
    );

    final deleted = await controller.deleteLocalTokens();
    expect(deleted, true);
    expect(repository.deletedTokenIds, contains(10));
  });
}

class MemoryNotificationTokenStore implements LocalNotificationTokenStore {
  final Map<String, String> values = {};

  @override
  Future<String?> read(String key) async => values[key];

  @override
  Future<void> write(String key, String value) async {
    values[key] = value;
  }
}

class StubNotificationTokenProvider implements NotificationTokenProviderSource {
  const StubNotificationTokenProvider();

  @override
  Future<RegisterNotificationTokenRequest> getTokenRequest({String? locale}) {
    return Future.value(
      const RegisterNotificationTokenRequest(
        token: 'local-test-token',
        provider: NotificationTokenProvider.local,
        deviceType: NotificationDeviceType.android,
      ),
    );
  }
}

class FakeNotificationsRepository implements NotificationsRepository {
  List<NotificationPreference> savedPreferences = const [];
  final deletedTokenIds = <int>[];

  List<AppNotification> notifications = [
    const AppNotification(
      id: 1,
      category: NotificationCategory.appointments,
      type: 'appointment_reminder',
      title: 'Appointment',
      body: 'Reminder',
      priority: NotificationPriority.normal,
    ),
    AppNotification(
      id: 2,
      category: NotificationCategory.system,
      type: 'system_notice',
      title: 'System',
      body: 'Notice',
      priority: NotificationPriority.low,
      readAt: DateTime.utc(2026, 5, 6),
    ),
  ];

  @override
  Future<ApiResult<void>> deleteNotification(int id) {
    notifications = notifications.where((item) => item.id != id).toList();
    return Future.value(const ApiSuccess<void>(null));
  }

  @override
  Future<ApiResult<void>> deleteToken(int id) {
    deletedTokenIds.add(id);
    return Future.value(const ApiSuccess<void>(null));
  }

  @override
  Future<ApiResult<AppNotification>> getNotificationDetails(int id) {
    return Future.value(
      ApiSuccess(notifications.firstWhere((n) => n.id == id)),
    );
  }

  @override
  Future<ApiResult<List<AppNotification>>> getNotifications() {
    return Future.value(ApiSuccess(notifications));
  }

  @override
  Future<ApiResult<List<NotificationPreference>>> getPreferences() {
    return Future.value(
      const ApiSuccess([
        NotificationPreference(
          channel: NotificationChannel.inApp,
          category: NotificationCategory.system,
          isEnabled: true,
        ),
        NotificationPreference(
          channel: NotificationChannel.push,
          category: NotificationCategory.system,
          isEnabled: true,
        ),
      ]),
    );
  }

  @override
  Future<ApiResult<List<NotificationToken>>> getTokens() {
    return Future.value(
      const ApiSuccess([
        NotificationToken(
          id: 10,
          provider: NotificationTokenProvider.local,
          deviceType: NotificationDeviceType.android,
        ),
      ]),
    );
  }

  @override
  Future<ApiResult<NotificationUnreadCount>> getUnreadCount() {
    final count = notifications.where((item) => !item.isRead).length;
    return Future.value(
      ApiSuccess(NotificationUnreadCount(unreadCount: count)),
    );
  }

  @override
  Future<ApiResult<int>> markAllRead() {
    notifications = notifications
        .map(
          (item) => AppNotification(
            id: item.id,
            category: item.category,
            type: item.type,
            title: item.title,
            body: item.body,
            priority: item.priority,
            data: item.data,
            readAt: DateTime.utc(2026, 5, 6),
          ),
        )
        .toList(growable: false);
    return Future.value(const ApiSuccess(2));
  }

  @override
  Future<ApiResult<AppNotification>> markRead(int id) {
    final current = notifications.firstWhere((item) => item.id == id);
    final updated = AppNotification(
      id: current.id,
      category: current.category,
      type: current.type,
      title: current.title,
      body: current.body,
      priority: current.priority,
      data: current.data,
      readAt: DateTime.utc(2026, 5, 6),
    );
    notifications = notifications
        .map((item) => item.id == id ? updated : item)
        .toList(growable: false);
    return Future.value(ApiSuccess(updated));
  }

  @override
  Future<ApiResult<NotificationToken>> registerToken(
    RegisterNotificationTokenRequest request,
  ) {
    return Future.value(
      const ApiSuccess(
        NotificationToken(
          id: 10,
          provider: NotificationTokenProvider.local,
          deviceType: NotificationDeviceType.android,
        ),
      ),
    );
  }

  @override
  Future<ApiResult<List<NotificationPreference>>> updatePreferences(
    UpdateNotificationPreferencesRequest request,
  ) {
    savedPreferences = request.preferences;
    return Future.value(ApiSuccess(savedPreferences));
  }
}

class FailingNotificationsRepository extends FakeNotificationsRepository {
  @override
  Future<ApiResult<List<AppNotification>>> getNotifications() {
    return Future.value(
      const ApiFailure(ApiError(message: 'failed', type: ApiErrorType.network)),
    );
  }
}
