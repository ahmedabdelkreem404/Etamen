import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_order.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/payments/presentation/widgets/payment_status_badge.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class LabPaymentCard extends StatelessWidget {
  const LabPaymentCard({
    required this.order,
    required this.paymentStatus,
    required this.isCreatingPayment,
    required this.onCreatePayment,
    super.key,
  });

  final LabOrder order;
  final PaymentStatusDetails? paymentStatus;
  final bool isCreatingPayment;
  final Future<int?> Function() onCreatePayment;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final isArabic = AppLocalizations.of(context).isArabic;
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              l10n.get('orderPayment'),
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(
                  child: Text(
                    '${order.grandTotal ?? '-'} ${order.currency ?? ''}',
                  ),
                ),
                if (paymentStatus != null)
                  PaymentStatusBadge(status: paymentStatus!.status)
                else if (order.paymentStatus != null)
                  Text(order.paymentStatusLabel(isArabic: isArabic)),
              ],
            ),
            const SizedBox(height: 12),
            if (paymentStatus?.status == PaymentStatusEnum.verified ||
                order.status == LabOrderStatus.paid)
              Text(
                l10n.get('paymentDone'),
                style: const TextStyle(
                  color: AppColors.success,
                  fontWeight: FontWeight.w800,
                ),
              )
            else if (order.paymentId != null &&
                (order.canPay || order.canUploadProof))
              AppButton(
                label: l10n.get('continuePayment'),
                onPressed: () => context.push(
                  RouteNames.payment(order.paymentId!, labOrderId: order.id),
                ),
              )
            else if (order.canCreatePayment)
              AppButton(
                label: l10n.get('createPayment'),
                isLoading: isCreatingPayment,
                onPressed: isCreatingPayment
                    ? null
                    : () async {
                        final paymentId = await onCreatePayment();
                        if (paymentId != null && context.mounted) {
                          context.push(
                            RouteNames.payment(paymentId, labOrderId: order.id),
                          );
                        }
                      },
              )
            else
              Text(
                order.nextActionLabel(isArabic: isArabic) ??
                    l10n.get('labWaitingReview'),
                style: const TextStyle(color: AppColors.muted),
              ),
          ],
        ),
      ),
    );
  }
}
