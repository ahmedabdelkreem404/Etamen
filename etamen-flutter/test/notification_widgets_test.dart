import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_theme.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/notifications/data/models/register_notification_token_request.dart';
import 'package:etamen_app/features/notifications/data/models/update_notification_preferences_request.dart';
import 'package:etamen_app/features/notifications/domain/entities/app_notification.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_preference.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_token.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_unread_count.dart';
import 'package:etamen_app/features/notifications/domain/repositories/notifications_repository.dart';
import 'package:etamen_app/features/notifications/presentation/providers/notifications_providers.dart';
import 'package:etamen_app/features/notifications/presentation/widgets/notification_badge.dart';
import 'package:etamen_app/features/notifications/presentation/widgets/notification_card.dart';
import 'package:etamen_app/features/notifications/presentation/widgets/notification_empty_state.dart';
import 'package:etamen_app/features/notifications/presentation/widgets/notification_preference_tile.dart';
import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('NotificationEmptyState displays empty message', (tester) async {
    await tester.pumpWidget(
      _wrap(const NotificationEmptyState(message: 'No notifications yet')),
    );
    await tester.pumpAndSettle();

    expect(find.text('No notifications yet'), findsOneWidget);
    expect(find.byIcon(Icons.notifications_none), findsOneWidget);
  });

  testWidgets('NotificationCard displays unread indicator and priority', (
    tester,
  ) async {
    await tester.pumpWidget(
      _wrap(
        const NotificationCard(
          notification: AppNotification(
            id: 1,
            category: NotificationCategory.payments,
            type: 'payment_verified',
            title: 'Payment updated',
            body: 'Your payment was reviewed',
            priority: NotificationPriority.high,
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('Payment updated'), findsOneWidget);
    expect(find.text('Your payment was reviewed'), findsOneWidget);
    expect(find.text('Payments'), findsOneWidget);
    expect(find.text('Important'), findsOneWidget);
  });

  testWidgets('NotificationPreferenceTile shows toggle and channel label', (
    tester,
  ) async {
    var value = true;
    await tester.pumpWidget(
      _wrap(
        NotificationPreferenceTile(
          preference: const NotificationPreference(
            channel: NotificationChannel.push,
            category: NotificationCategory.system,
            isEnabled: true,
          ),
          onChanged: (enabled) => value = enabled,
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('Phone alert'), findsOneWidget);
    await tester.tap(find.byType(Switch));
    await tester.pump();
    expect(value, false);
  });

  testWidgets('NotificationBadge displays unread count', (tester) async {
    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          notificationsRepositoryProvider.overrideWithValue(
            BadgeNotificationsRepository(),
          ),
        ],
        child: _wrap(NotificationBadge(onTap: () {})),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('5'), findsOneWidget);
  });
}

Widget _wrap(Widget child) {
  return MaterialApp(
    locale: const Locale('en'),
    supportedLocales: AppLocalizations.supportedLocales,
    localizationsDelegates: const [
      AppLocalizations.delegate,
      GlobalMaterialLocalizations.delegate,
      GlobalWidgetsLocalizations.delegate,
      GlobalCupertinoLocalizations.delegate,
    ],
    theme: AppTheme.light,
    home: Scaffold(body: Center(child: child)),
  );
}

class BadgeNotificationsRepository implements NotificationsRepository {
  @override
  Future<ApiResult<void>> deleteNotification(int id) {
    return Future.value(const ApiSuccess<void>(null));
  }

  @override
  Future<ApiResult<void>> deleteToken(int id) {
    return Future.value(const ApiSuccess<void>(null));
  }

  @override
  Future<ApiResult<AppNotification>> getNotificationDetails(int id) {
    return Future.value(
      const ApiSuccess(
        AppNotification(
          id: 1,
          category: NotificationCategory.system,
          type: 'system_notice',
          title: 'System',
          body: 'Notice',
          priority: NotificationPriority.normal,
        ),
      ),
    );
  }

  @override
  Future<ApiResult<List<AppNotification>>> getNotifications() {
    return Future.value(const ApiSuccess([]));
  }

  @override
  Future<ApiResult<List<NotificationPreference>>> getPreferences() {
    return Future.value(const ApiSuccess([]));
  }

  @override
  Future<ApiResult<List<NotificationToken>>> getTokens() {
    return Future.value(const ApiSuccess([]));
  }

  @override
  Future<ApiResult<NotificationUnreadCount>> getUnreadCount() {
    return Future.value(
      const ApiSuccess(NotificationUnreadCount(unreadCount: 5)),
    );
  }

  @override
  Future<ApiResult<int>> markAllRead() {
    return Future.value(const ApiSuccess(0));
  }

  @override
  Future<ApiResult<AppNotification>> markRead(int id) {
    return getNotificationDetails(id);
  }

  @override
  Future<ApiResult<NotificationToken>> registerToken(
    RegisterNotificationTokenRequest request,
  ) {
    return Future.value(
      const ApiSuccess(
        NotificationToken(
          id: 1,
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
    return Future.value(ApiSuccess(request.preferences));
  }
}
