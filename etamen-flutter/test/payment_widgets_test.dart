import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_theme.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_method.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/payments/presentation/widgets/manual_instructions_card.dart';
import 'package:etamen_app/features/payments/presentation/widgets/payment_method_card.dart';
import 'package:etamen_app/features/payments/presentation/widgets/payment_status_badge.dart';
import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('Payment method card shows manual method details', (
    tester,
  ) async {
    await tester.pumpWidget(
      _wrap(
        PaymentMethodCard(
          method: const PaymentMethod(
            id: 2,
            type: PaymentMethodType.manualVodafoneCash,
            nameAr: 'Vodafone Cash AR',
            nameEn: 'Vodafone Cash',
            isActive: true,
          ),
          onTap: () {},
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('Vodafone Cash AR'), findsOneWidget);
    expect(
      find.text(
        AppLocalizations(const Locale('ar')).get('manualPaymentSubtitle'),
      ),
      findsOneWidget,
    );
  });

  testWidgets('Manual instructions card displays backend instructions', (
    tester,
  ) async {
    await tester.pumpWidget(
      _wrap(const ManualInstructionsCard(instructions: 'Transfer then upload')),
    );
    await tester.pumpAndSettle();

    expect(find.text('Transfer then upload'), findsOneWidget);
  });

  testWidgets('Payment status badge shows verified state', (tester) async {
    await tester.pumpWidget(
      _wrap(const PaymentStatusBadge(status: PaymentStatusEnum.verified)),
    );
    await tester.pumpAndSettle();

    expect(
      find.text(AppLocalizations(const Locale('ar')).get('paymentVerified')),
      findsOneWidget,
    );
  });
}

Widget _wrap(Widget child) {
  return MaterialApp(
    theme: AppTheme.light,
    locale: const Locale('ar'),
    supportedLocales: AppLocalizations.supportedLocales,
    localizationsDelegates: const [
      AppLocalizations.delegate,
      GlobalMaterialLocalizations.delegate,
      GlobalCupertinoLocalizations.delegate,
      GlobalWidgetsLocalizations.delegate,
    ],
    home: Scaffold(body: child),
  );
}
