import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_method.dart';
import 'package:flutter/material.dart';

class PaymentMethodCard extends StatelessWidget {
  const PaymentMethodCard({
    required this.method,
    required this.onTap,
    this.isLoading = false,
    super.key,
  });

  final PaymentMethod method;
  final VoidCallback? onTap;
  final bool isLoading;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      child: ListTile(
        enabled: method.isActive && !isLoading,
        leading: CircleAvatar(child: Icon(_icon(method.type))),
        title: Text(method.displayName(l10n.isArabic)),
        subtitle: Text(_subtitle(context, method.type)),
        trailing: isLoading
            ? const SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(strokeWidth: 2),
              )
            : const Icon(Icons.chevron_right),
        onTap: method.isActive && !isLoading ? onTap : null,
      ),
    );
  }

  IconData _icon(PaymentMethodType type) {
    return switch (type) {
      PaymentMethodType.paymob => Icons.credit_card,
      PaymentMethodType.manualVodafoneCash => Icons.phone_android,
      PaymentMethodType.manualInstapay => Icons.account_balance,
      PaymentMethodType.unknown => Icons.payments_outlined,
    };
  }

  String _subtitle(BuildContext context, PaymentMethodType type) {
    final l10n = AppLocalizations.of(context);
    return switch (type) {
      PaymentMethodType.paymob => l10n.get('paymobSubtitle'),
      PaymentMethodType.manualVodafoneCash ||
      PaymentMethodType.manualInstapay => l10n.get('manualPaymentSubtitle'),
      PaymentMethodType.unknown => l10n.get('paymentMethod'),
    };
  }
}
