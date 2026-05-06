import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_theme.dart';
import 'package:etamen_app/features/auth/domain/entities/auth_user.dart';
import 'package:etamen_app/features/auth/presentation/providers/auth_controller.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor.dart';
import 'package:etamen_app/features/doctors/presentation/widgets/doctor_card.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
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

    expect(find.textContaining('Hello, Patient'), findsOneWidget);
    expect(find.text('Quick actions'), findsOneWidget);
    expect(find.text('Today overview'), findsOneWidget);
    await tester.drag(find.byType(ListView), const Offset(0, -700));
    await tester.pumpAndSettle();
    expect(find.text('Ask the AI assistant safely'), findsOneWidget);
  });

  testWidgets('Services tab groups doctors pharmacy and labs', (tester) async {
    await tester.pumpWidget(_wrap(const ServicesTab()));
    await tester.pumpAndSettle();

    expect(find.text('Services'), findsOneWidget);
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
    expect(find.text('Book now'), findsOneWidget);
    expect(find.textContaining('Open profile'), findsOneWidget);
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
