import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_theme.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_cart_item.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_order.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_product.dart';
import 'package:etamen_app/features/pharmacy/presentation/widgets/pharmacy_cart_item_tile.dart';
import 'package:etamen_app/features/pharmacy/presentation/widgets/pharmacy_order_status_chip.dart';
import 'package:etamen_app/features/pharmacy/presentation/widgets/pharmacy_payment_card.dart';
import 'package:etamen_app/features/pharmacy/presentation/widgets/pharmacy_product_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('Product card shows prescription badge', (tester) async {
    await tester.pumpWidget(
      _wrap(
        PharmacyProductCard(
          product: const PharmacyProduct(
            id: 1,
            name: 'Medicine',
            price: '30.00',
            currency: 'EGP',
            requiresPrescription: true,
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

    expect(find.text('Medicine'), findsOneWidget);
    expect(
      find.text(
        AppLocalizations(const Locale('ar')).get('requiresPrescription'),
      ),
      findsOneWidget,
    );
  });

  testWidgets('Cart item tile displays quantity and display price', (
    tester,
  ) async {
    await tester.pumpWidget(
      _wrap(
        PharmacyCartItemTile(
          item: PharmacyCartItem(
            product: const PharmacyProduct(
              id: 1,
              name: 'Medicine',
              price: '30.00',
              currency: 'EGP',
              requiresPrescription: false,
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

    expect(find.text('Medicine'), findsOneWidget);
    expect(find.text('30.00 EGP x 2'), findsOneWidget);
  });

  testWidgets('PharmacyPaymentCard shows pending payment CTA', (tester) async {
    await tester.pumpWidget(
      _wrap(
        PharmacyPaymentCard(
          order: const PharmacyOrder(
            id: 77,
            status: PharmacyOrderStatus.awaitingPayment,
            paymentId: 501,
            grandTotal: '120.00',
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

  testWidgets('PharmacyPaymentCard shows paid state', (tester) async {
    await tester.pumpWidget(
      _wrap(
        PharmacyPaymentCard(
          order: const PharmacyOrder(
            id: 77,
            status: PharmacyOrderStatus.paid,
            grandTotal: '120.00',
            currency: 'EGP',
            items: [],
          ),
          paymentStatus: const PaymentStatusDetails(
            id: 501,
            status: PaymentStatusEnum.verified,
            amount: '120.00',
            currency: 'EGP',
            payableType: 'pharmacy_order',
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

  testWidgets('PharmacyOrderStatusChip displays delivered state', (
    tester,
  ) async {
    await tester.pumpWidget(
      _wrap(
        const PharmacyOrderStatusChip(status: PharmacyOrderStatus.delivered),
      ),
    );
    await tester.pumpAndSettle();

    expect(
      find.text(AppLocalizations(const Locale('ar')).get('delivered')),
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
