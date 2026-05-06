import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_theme.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment.dart';
import 'package:etamen_app/features/appointments/presentation/widgets/appointment_card.dart';
import 'package:etamen_app/features/appointments/presentation/widgets/appointment_payment_card.dart';
import 'package:etamen_app/features/appointments/presentation/widgets/appointment_status_chip.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('AppointmentCard shows pending payment CTA', (tester) async {
    await tester.pumpWidget(
      _wrap(
        const AppointmentCard(
          appointment: Appointment(
            id: 1,
            doctorProfileId: 7,
            appointmentSlotId: 8,
            consultationType: ConsultationType.clinic,
            price: '250.00',
            currency: 'EGP',
            status: AppointmentStatus.pendingPayment,
            paymentId: 200,
            doctorName: 'Dr Test',
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('Dr Test'), findsOneWidget);
    expect(
      find.text(AppLocalizations(const Locale('ar')).get('payNow')),
      findsOneWidget,
    );
  });

  testWidgets('AppointmentPaymentCard shows verified payment state', (
    tester,
  ) async {
    await tester.pumpWidget(
      _wrap(
        const AppointmentPaymentCard(
          paymentId: 200,
          appointmentId: 1,
          amount: '250.00',
          currency: 'EGP',
          paymentStatus: PaymentStatusDetails(
            id: 200,
            status: PaymentStatusEnum.verified,
            amount: '250.00',
            currency: 'EGP',
            payableType: 'appointment',
            payableId: 1,
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(
      find.text(AppLocalizations(const Locale('ar')).get('paymentDone')),
      findsOneWidget,
    );
  });

  testWidgets('AppointmentStatusChip displays completed status', (
    tester,
  ) async {
    await tester.pumpWidget(
      _wrap(const AppointmentStatusChip(status: AppointmentStatus.completed)),
    );
    await tester.pumpAndSettle();

    expect(
      find.text(
        AppLocalizations(const Locale('ar')).get('appointmentCompleted'),
      ),
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
