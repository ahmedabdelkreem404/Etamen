import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/payments/presentation/widgets/payment_status_badge.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class AppointmentPaymentCard extends StatelessWidget {
  const AppointmentPaymentCard({
    required this.paymentId,
    required this.appointmentId,
    required this.amount,
    required this.currency,
    this.paymentStatus,
    super.key,
  });

  final int? paymentId;
  final int appointmentId;
  final String amount;
  final String currency;
  final PaymentStatusDetails? paymentStatus;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              l10n.get('paymentStatus'),
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: Text(
                    '$amount $currency',
                    style: Theme.of(context).textTheme.titleLarge,
                  ),
                ),
                if (paymentStatus != null)
                  PaymentStatusBadge(status: paymentStatus!.status)
                else if (paymentId == null)
                  Text(l10n.get('noPaymentRequired')),
              ],
            ),
            if (paymentStatus?.status == PaymentStatusEnum.verified) ...[
              const SizedBox(height: 12),
              Text(
                l10n.get('paymentDone'),
                style: const TextStyle(
                  color: AppColors.success,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ],
            if (paymentId != null &&
                (paymentStatus == null ||
                    paymentStatus!.status == PaymentStatusEnum.awaitingMethod ||
                    paymentStatus!.status == PaymentStatusEnum.awaitingProof ||
                    paymentStatus!.status == PaymentStatusEnum.pendingReview ||
                    paymentStatus!.status == PaymentStatusEnum.pendingGateway ||
                    paymentStatus!.status == PaymentStatusEnum.rejected)) ...[
              const SizedBox(height: 12),
              AppButton(
                label: paymentStatus?.status == PaymentStatusEnum.rejected
                    ? l10n.get('retryProof')
                    : l10n.get('continuePayment'),
                onPressed: () => context.push(
                  RouteNames.payment(paymentId!, appointmentId: appointmentId),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
