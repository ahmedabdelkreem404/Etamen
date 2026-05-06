import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_theme.dart';
import 'package:etamen_app/core/legal/legal_document_type.dart';
import 'package:etamen_app/features/account/presentation/pages/about_page.dart';
import 'package:etamen_app/features/account/presentation/pages/account_page.dart';
import 'package:etamen_app/features/account/presentation/pages/legal_page.dart';
import 'package:etamen_app/features/account/presentation/pages/support_page.dart';
import 'package:etamen_app/features/account/presentation/widgets/logout_button.dart';
import 'package:etamen_app/features/auth/domain/entities/auth_user.dart';
import 'package:etamen_app/features/auth/presentation/providers/auth_controller.dart';
import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('AccountPage shows legal support settings links', (tester) async {
    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          authControllerProvider.overrideWith(TestAuthController.new),
        ],
        child: _wrap(const AccountPage()),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('Settings'), findsOneWidget);
    expect(find.text('Privacy Policy'), findsOneWidget);
    await tester.scrollUntilVisible(find.text('Medical Disclaimer'), 300);
    expect(find.text('Medical Disclaimer'), findsOneWidget);
    await tester.scrollUntilVisible(find.text('Support & Help'), 300);
    expect(find.text('Support & Help'), findsOneWidget);
    await tester.scrollUntilVisible(find.text('About app'), 300);
    expect(find.text('About app'), findsOneWidget);
  });

  testWidgets('LegalPage renders privacy policy', (tester) async {
    await tester.pumpWidget(
      _wrap(const LegalPage(type: LegalDocumentType.privacyPolicy)),
    );
    await tester.pumpAndSettle();

    expect(find.text('Privacy Policy'), findsOneWidget);
    expect(find.textContaining('Etamen collects data'), findsOneWidget);
  });

  testWidgets('Medical disclaimer includes doctor safety wording', (
    tester,
  ) async {
    await tester.pumpWidget(
      _wrap(const LegalPage(type: LegalDocumentType.medicalDisclaimer)),
    );
    await tester.pumpAndSettle();

    expect(find.textContaining('does not replace a doctor'), findsOneWidget);
  });

  testWidgets('AI disclaimer includes not doctor wording', (tester) async {
    await tester.pumpWidget(
      _wrap(const LegalPage(type: LegalDocumentType.aiDisclaimer)),
    );
    await tester.pumpAndSettle();

    expect(
      find.textContaining('The AI assistant is not a doctor'),
      findsOneWidget,
    );
  });

  testWidgets('Refund policy mentions review and manual payments', (
    tester,
  ) async {
    await tester.pumpWidget(
      _wrap(const LegalPage(type: LegalDocumentType.refundPolicy)),
    );
    await tester.pumpAndSettle();

    expect(find.textContaining('require support/admin review'), findsOneWidget);
    expect(
      find.textContaining('Manual payments require proof'),
      findsOneWidget,
    );
  });

  testWidgets('SupportPage shows placeholder safely', (tester) async {
    await tester.pumpWidget(_wrap(const SupportPage()));
    await tester.pumpAndSettle();

    expect(find.text('Support & Help'), findsOneWidget);
    expect(find.textContaining('will be added soon'), findsWidgets);
  });

  testWidgets('AboutPage shows app version and links', (tester) async {
    await tester.pumpWidget(_wrap(const AboutPage()));
    await tester.pumpAndSettle();

    expect(find.text('About app'), findsOneWidget);
    expect(find.text('App version'), findsOneWidget);
    expect(find.text('Privacy Policy'), findsOneWidget);
  });

  testWidgets('Logout confirmation appears', (tester) async {
    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          authControllerProvider.overrideWith(TestAuthController.new),
        ],
        child: _wrap(const Scaffold(body: Center(child: LogoutButton()))),
      ),
    );
    await tester.pumpAndSettle();

    await tester.tap(find.text('Logout'));
    await tester.pumpAndSettle();

    expect(find.text('Do you want to log out?'), findsOneWidget);
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
    home: child,
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

  @override
  Future<bool> logout() async {
    state = const AuthState(status: AuthStatus.unauthenticated);
    return true;
  }
}
