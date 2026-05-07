import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_theme.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/features/auth/domain/entities/auth_user.dart';
import 'package:etamen_app/features/auth/presentation/providers/auth_controller.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor.dart';
import 'package:etamen_app/features/doctors/presentation/widgets/doctor_card.dart';
import 'package:etamen_app/features/home/presentation/pages/home_page.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:etamen_app/features/notifications/data/models/register_notification_token_request.dart';
import 'package:etamen_app/features/notifications/data/models/update_notification_preferences_request.dart';
import 'package:etamen_app/features/notifications/domain/entities/app_notification.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_preference.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_token.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_unread_count.dart';
import 'package:etamen_app/features/notifications/domain/repositories/notifications_repository.dart';
import 'package:etamen_app/features/notifications/presentation/providers/notifications_providers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('Home dashboard renders patient journey sections', (
    tester,
  ) async {
    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          authControllerProvider.overrideWith(TestAuthController.new),
        ],
        child: _wrap(HomeDashboardTab(onOpenTab: (_) {})),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.textContaining('Welcome, Patient'), findsOneWidget);
    expect(find.text('Search doctor by name'), findsOneWidget);
    expect(find.textContaining('Search Doctors'), findsOneWidget);
    expect(find.text('Speciality'), findsOneWidget);
    expect(find.text('Nearby Doctors'), findsOneWidget);
    await tester.drag(find.byType(ListView).first, const Offset(0, -360));
    await tester.pumpAndSettle();
    expect(find.text('More services'), findsOneWidget);
    expect(find.text('Health follow-up'), findsOneWidget);
    await tester.drag(find.byType(ListView).first, const Offset(0, -700));
    await tester.pumpAndSettle();
    expect(find.text('AI'), findsOneWidget);
  });

  testWidgets('Services tab groups doctors pharmacy and labs', (tester) async {
    await tester.pumpWidget(_wrap(const ServicesTab()));
    await tester.pumpAndSettle();

    expect(find.text('Medical services'), findsOneWidget);
    expect(find.text('Book a doctor'), findsOneWidget);
    expect(find.text('Pharmacy orders'), findsOneWidget);
    expect(find.text('Lab orders'), findsWidgets);
  });

  testWidgets('Health tab groups vitals medications care plans and AI', (
    tester,
  ) async {
    await tester.pumpWidget(_wrap(const HealthHubTab()));
    await tester.pumpAndSettle();

    expect(find.text('Health follow-up'), findsOneWidget);
    expect(find.text('Vitals'), findsOneWidget);
    expect(find.text('Medications'), findsOneWidget);
    expect(find.text('Care plans'), findsWidgets);
    expect(find.text('AI assistant'), findsOneWidget);
  });

  testWidgets('DoctorCard renders clear booking CTA', (tester) async {
    const doctor = Doctor(
      id: 7,
      name: 'Dr. Mona',
      isActive: true,
      consultationFee: '300',
      yearsOfExperience: 9,
      specialties: ['Cardiology'],
      branches: ['Nasr City'],
    );

    await tester.pumpWidget(_wrap(DoctorCard(doctor: doctor, onTap: () {})));
    await tester.pumpAndSettle();

    expect(find.text('Dr. Mona'), findsOneWidget);
    expect(find.text('DM'), findsOneWidget);
    expect(find.text('Book now'), findsOneWidget);
    expect(find.textContaining('Open profile'), findsOneWidget);
    expect(find.text('Details'), findsOneWidget);
  });

  testWidgets('Friendly empty state copy is patient-facing', (tester) async {
    await tester.pumpWidget(
      _wrap(
        const EmptyView(
          message:
              'No doctors are available right now. Doctors will be added soon.',
          icon: Icons.medical_services_outlined,
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.textContaining('Doctors will be added soon'), findsOneWidget);
    expect(find.byIcon(Icons.medical_services_outlined), findsOneWidget);
  });

  testWidgets('Main navigation keeps the patient shell to five tabs', (
    tester,
  ) async {
    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          authControllerProvider.overrideWith(TestAuthController.new),
          notificationsRepositoryProvider.overrideWithValue(
            _UxNotificationsRepository(),
          ),
        ],
        child: _wrap(const HomePage()),
      ),
    );
    await tester.pumpAndSettle();

    expect(
      find.byWidgetPredicate(
        (widget) =>
            widget.key is ValueKey<String> &&
            (widget.key as ValueKey<String>).value.startsWith('legacy_nav_'),
      ),
      findsNWidgets(5),
    );
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
    home: Scaffold(body: child),
  );
}

class TestAuthController extends AuthController {
  TestAuthController(super.ref) {
    state = const AuthState(
      status: AuthStatus.authenticated,
      user: AuthUser(
        id: 1,
        email: 'patient@example.test',
        name: 'Patient User',
        roles: ['patient'],
      ),
    );
  }
}

class _UxNotificationsRepository implements NotificationsRepository {
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
      const ApiSuccess(NotificationUnreadCount(unreadCount: 2)),
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
