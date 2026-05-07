import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
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
    final accent = _accent(method.type);
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.softBorder),
        boxShadow: [
          BoxShadow(
            color: accent.withValues(alpha: 0.10),
            blurRadius: 18,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(18),
          onTap: method.isActive && !isLoading ? onTap : null,
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                Container(
                  width: 52,
                  height: 52,
                  decoration: BoxDecoration(
                    color: accent.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Icon(_icon(method.type), color: accent, size: 28),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        method.displayName(l10n.isArabic),
                        style: Theme.of(context).textTheme.titleMedium
                            ?.copyWith(fontWeight: FontWeight.w900),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _subtitle(context, method.type),
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: AppColors.muted,
                          height: 1.35,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                if (isLoading)
                  const SizedBox(
                    width: 22,
                    height: 22,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                else
                  Container(
                    width: 34,
                    height: 34,
                    decoration: BoxDecoration(
                      color: AppColors.medicalMint,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(
                      Icons.chevron_right,
                      color: AppColors.primary,
                    ),
                  ),
              ],
            ),
          ),
        ),
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

  Color _accent(PaymentMethodType type) {
    return switch (type) {
      PaymentMethodType.manualVodafoneCash => const Color(0xFFE60000),
      PaymentMethodType.manualInstapay => const Color(0xFF6F2DBD),
      PaymentMethodType.paymob => AppColors.primary,
      PaymentMethodType.unknown => AppColors.primaryDark,
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
