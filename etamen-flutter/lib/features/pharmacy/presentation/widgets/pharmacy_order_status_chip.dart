import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_order.dart';
import 'package:flutter/material.dart';

class PharmacyOrderStatusChip extends StatelessWidget {
  const PharmacyOrderStatusChip({required this.status, super.key});

  final PharmacyOrderStatus status;

  @override
  Widget build(BuildContext context) {
    return Chip(
      label: Text(_label(context)),
      backgroundColor: _color().withValues(alpha: 0.12),
      labelStyle: TextStyle(color: _color(), fontWeight: FontWeight.w700),
      side: BorderSide(color: _color().withValues(alpha: 0.2)),
    );
  }

  String _label(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return switch (status) {
      PharmacyOrderStatus.pending ||
      PharmacyOrderStatus.pharmacyReview => l10n.get('pharmacyReview'),
      PharmacyOrderStatus.accepted ||
      PharmacyOrderStatus.awaitingPayment => l10n.get('awaitingPayment'),
      PharmacyOrderStatus.paid => l10n.get('paid'),
      PharmacyOrderStatus.preparing => l10n.get('preparing'),
      PharmacyOrderStatus.ready ||
      PharmacyOrderStatus.readyForPickup ||
      PharmacyOrderStatus.outForDelivery => l10n.get('ready'),
      PharmacyOrderStatus.delivered => l10n.get('delivered'),
      PharmacyOrderStatus.rejected => l10n.get('rejected'),
      PharmacyOrderStatus.cancelled => l10n.get('cancelled'),
      PharmacyOrderStatus.unknown => l10n.get('unknownStatus'),
    };
  }

  Color _color() {
    return switch (status) {
      PharmacyOrderStatus.delivered ||
      PharmacyOrderStatus.paid => AppColors.success,
      PharmacyOrderStatus.rejected ||
      PharmacyOrderStatus.cancelled => AppColors.danger,
      PharmacyOrderStatus.accepted ||
      PharmacyOrderStatus.awaitingPayment ||
      PharmacyOrderStatus.pending ||
      PharmacyOrderStatus.pharmacyReview => AppColors.warning,
      _ => AppColors.primary,
    };
  }
}
