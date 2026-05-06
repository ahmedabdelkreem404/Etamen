import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_order.dart';
import 'package:flutter/material.dart';

class LabOrderStatusChip extends StatelessWidget {
  const LabOrderStatusChip({required this.status, super.key});

  final LabOrderStatus status;

  @override
  Widget build(BuildContext context) {
    final color = _color();
    return Chip(
      label: Text(_label(context)),
      backgroundColor: color.withValues(alpha: 0.12),
      labelStyle: TextStyle(color: color, fontWeight: FontWeight.w700),
      side: BorderSide(color: color.withValues(alpha: 0.2)),
    );
  }

  String _label(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return switch (status) {
      LabOrderStatus.labReview => l10n.get('labReview'),
      LabOrderStatus.accepted ||
      LabOrderStatus.awaitingPayment => l10n.get('awaitingPayment'),
      LabOrderStatus.paid => l10n.get('paid'),
      LabOrderStatus.sampleScheduled ||
      LabOrderStatus.sampleCollected ||
      LabOrderStatus.sampleCollection => l10n.get('sampleCollection'),
      LabOrderStatus.processing => l10n.get('labProcessing'),
      LabOrderStatus.resultReady => l10n.get('resultReady'),
      LabOrderStatus.completed => l10n.get('completed'),
      LabOrderStatus.rejected => l10n.get('rejected'),
      LabOrderStatus.cancelled => l10n.get('cancelled'),
      LabOrderStatus.unknown => l10n.get('unknownStatus'),
    };
  }

  Color _color() {
    return switch (status) {
      LabOrderStatus.completed ||
      LabOrderStatus.resultReady ||
      LabOrderStatus.paid => AppColors.success,
      LabOrderStatus.rejected || LabOrderStatus.cancelled => AppColors.danger,
      LabOrderStatus.labReview ||
      LabOrderStatus.accepted ||
      LabOrderStatus.awaitingPayment => AppColors.warning,
      _ => AppColors.primary,
    };
  }
}
