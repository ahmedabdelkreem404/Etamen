import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_theme.dart';
import 'package:etamen_app/features/labs/data/models/create_lab_order_request.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_cart_item.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_order.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_result.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_test.dart';
import 'package:etamen_app/features/labs/presentation/widgets/lab_cart_item_tile.dart';
import 'package:etamen_app/features/labs/presentation/widgets/lab_order_status_chip.dart';
import 'package:etamen_app/features/labs/presentation/widgets/lab_payment_card.dart';
import 'package:etamen_app/features/labs/presentation/widgets/lab_result_card.dart';
import 'package:etamen_app/features/labs/presentation/widgets/lab_test_card.dart';
import 'package:etamen_app/features/labs/presentation/widgets/sample_option_selector.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('LabTestCard shows add button and price', (tester) async {
    await tester.pumpWidget(
      _wrap(
        LabTestCard(
          test: const LabTest(
            id: 1,
            name: 'CBC',
            price: '120.00',
            currency: 'EGP',
            sampleType: 'blood',
            resultTimeHours: 12,
            isActive: true,
          ),
          quantity: 0,
          onAdd: () {},
          onIncrease: () {},
          onDecrease: () {},
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('CBC'), findsOneWidget);
    expect(find.text('120.00 EGP'), findsOneWidget);
    expect(
      find.text(AppLocalizations(const Locale('ar')).get('addToLabOrder')),
      findsOneWidget,
    );
  });

  testWidgets('LabCartItemTile displays display-only quantity price', (
    tester,
  ) async {
    await tester.pumpWidget(
      _wrap(
        LabCartItemTile(
          item: LabCartItem(
            type: LabCartItemType.test,
            test: const LabTest(
              id: 1,
              name: 'CBC',
              price: '120.00',
              currency: 'EGP',
              isActive: true,
            ),
            quantity: 2,
          ),
          onIncrease: () {},
          onDecrease: () {},
          onRemove: () {},
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('CBC'), findsOneWidget);
    expect(find.text('120.00 EGP x 2'), findsOneWidget);
  });

  testWidgets('SampleOptionSelector shows home collection option', (
    tester,
  ) async {
    await tester.pumpWidget(
      _wrap(
        SampleOptionSelector(
          value: LabSampleCollectionMethod.homeCollection,
          onChanged: (_) {},
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(
      find.text(AppLocalizations(const Locale('ar')).get('homeCollection')),
      findsOneWidget,
    );
  });

  testWidgets('LabPaymentCard shows pending payment CTA', (tester) async {
    await tester.pumpWidget(
      _wrap(
        LabPaymentCard(
          order: const LabOrder(
            id: 77,
            status: LabOrderStatus.awaitingPayment,
            paymentId: 501,
            grandTotal: '450.00',
            currency: 'EGP',
            items: [],
          ),
          paymentStatus: null,
          isCreatingPayment: false,
          onCreatePayment: () async => 501,
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(
      find.text(AppLocalizations(const Locale('ar')).get('continuePayment')),
      findsOneWidget,
    );
  });

  testWidgets('LabPaymentCard shows paid state', (tester) async {
    await tester.pumpWidget(
      _wrap(
        LabPaymentCard(
          order: const LabOrder(
            id: 77,
            status: LabOrderStatus.paid,
            grandTotal: '450.00',
            currency: 'EGP',
            items: [],
          ),
          paymentStatus: const PaymentStatusDetails(
            id: 501,
            status: PaymentStatusEnum.verified,
            amount: '450.00',
            currency: 'EGP',
            payableType: 'lab_order',
            payableId: 77,
          ),
          isCreatingPayment: false,
          onCreatePayment: () async => null,
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(
      find.text(AppLocalizations(const Locale('ar')).get('paymentDone')),
      findsOneWidget,
    );
  });

  testWidgets('LabResultCard shows download CTA', (tester) async {
    await tester.pumpWidget(
      _wrap(
        LabResultCard(
          result: const LabResult(id: 8, fileName: 'result.pdf'),
          isDownloading: false,
          onDownload: () async {},
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('result.pdf'), findsOneWidget);
    expect(
      find.text(AppLocalizations(const Locale('ar')).get('downloadResult')),
      findsOneWidget,
    );
  });

  testWidgets('LabOrderStatusChip displays result ready state', (tester) async {
    await tester.pumpWidget(
      _wrap(const LabOrderStatusChip(status: LabOrderStatus.resultReady)),
    );
    await tester.pumpAndSettle();

    expect(
      find.text(AppLocalizations(const Locale('ar')).get('resultReady')),
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
