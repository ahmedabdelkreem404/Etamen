import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_order.dart';
import 'package:etamen_app/features/labs/presentation/widgets/lab_order_status_chip.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

class LabOrderCard extends StatelessWidget {
  const LabOrderCard({required this.order, super.key});

  final LabOrder order;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final isArabic = AppLocalizations.of(context).isArabic;
    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: () => context.push(RouteNames.labOrderDetails(order.id)),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(
                      order.orderNumber ?? '#${order.id}',
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                  ),
                  LabOrderStatusChip(status: order.status),
                ],
              ),
              const SizedBox(height: 8),
              Text(order.labName ?? l10n.get('lab')),
              const SizedBox(height: 8),
              Row(
                children: [
                  Expanded(child: Text(_formatDate(context, order.createdAt))),
                  Text('${order.grandTotal ?? '-'} ${order.currency ?? ''}'),
                ],
              ),
              if (order.paymentStatus != null) ...[
                const SizedBox(height: 8),
                Text(
                  '${l10n.get('paymentStatus')}: ${order.paymentStatusLabel(isArabic: isArabic)}',
                ),
              ],
              if (order.nextActionLabel(isArabic: isArabic) != null) ...[
                const SizedBox(height: 8),
                Text(order.nextActionLabel(isArabic: isArabic)!),
              ],
              const SizedBox(height: 12),
              Align(
                alignment: AlignmentDirectional.centerEnd,
                child: FilledButton.tonal(
                  onPressed: () =>
                      context.push(RouteNames.labOrderDetails(order.id)),
                  child: Text(
                    order.canPay ||
                            order.canCreatePayment ||
                            order.canUploadProof
                        ? l10n.get('continuePayment')
                        : l10n.get('viewDetails'),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _formatDate(BuildContext context, DateTime? value) {
    if (value == null) {
      return AppLocalizations.of(context).get('dateUnavailable');
    }
    return DateFormat('d MMM yyyy, h:mm a').format(value.toLocal());
  }
}
