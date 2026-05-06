import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:flutter/material.dart';

class PaymentStatusBadge extends StatelessWidget {
  const PaymentStatusBadge({required this.status, super.key});

  final PaymentStatusEnum status;

  @override
  Widget build(BuildContext context) {
    final color = _color(status);
    return DecoratedBox(
      decoration: BoxDecoration(
        color: color.withValues(alpha: .12),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        child: Text(
          _label(context, status),
          style: TextStyle(color: color, fontWeight: FontWeight.w800),
        ),
      ),
    );
  }

  Color _color(PaymentStatusEnum status) {
    return switch (status) {
      PaymentStatusEnum.verified => AppColors.success,
      PaymentStatusEnum.rejected ||
      PaymentStatusEnum.failed => AppColors.danger,
      PaymentStatusEnum.pendingReview ||
      PaymentStatusEnum.pendingGateway ||
      PaymentStatusEnum.awaitingProof => AppColors.warning,
      PaymentStatusEnum.awaitingMethod => AppColors.primary,
      _ => AppColors.muted,
    };
  }

  String _label(BuildContext context, PaymentStatusEnum status) {
    final l10n = AppLocalizations.of(context);
    return switch (status) {
      PaymentStatusEnum.draft => l10n.get('paymentDraft'),
      PaymentStatusEnum.awaitingMethod => l10n.get('awaitingMethod'),
      PaymentStatusEnum.awaitingProof => l10n.get('awaitingProof'),
      PaymentStatusEnum.pendingReview => l10n.get('pendingReview'),
      PaymentStatusEnum.pendingGateway => l10n.get('pendingGateway'),
      PaymentStatusEnum.verified => l10n.get('paymentVerified'),
      PaymentStatusEnum.rejected => l10n.get('paymentRejected'),
      PaymentStatusEnum.failed => l10n.get('paymentFailed'),
      PaymentStatusEnum.expired => l10n.get('paymentExpired'),
      PaymentStatusEnum.cancelled => l10n.get('paymentCancelled'),
      PaymentStatusEnum.refunded => l10n.get('paymentRefunded'),
      PaymentStatusEnum.unknown => l10n.get('unknownStatus'),
    };
  }
}
