import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_order.dart';
import 'package:etamen_app/features/pharmacy/presentation/providers/pharmacy_providers.dart';
import 'package:etamen_app/features/pharmacy/presentation/widgets/pharmacy_order_status_chip.dart';
import 'package:etamen_app/features/pharmacy/presentation/widgets/pharmacy_payment_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

class PharmacyOrderDetailsPage extends ConsumerWidget {
  const PharmacyOrderDetailsPage({required this.orderId, super.key});

  final int orderId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(pharmacyOrderDetailsControllerProvider(orderId));
    final controller = ref.read(
      pharmacyOrderDetailsControllerProvider(orderId).notifier,
    );

    return AppScaffold(
      title: l10n.get('pharmacyOrderDetails'),
      body: state.isLoading && state.order == null
          ? const LoadingView()
          : state.error != null && state.order == null
          ? ErrorView(message: state.error!.message, onRetry: controller.load)
          : RefreshIndicator(
              onRefresh: controller.load,
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                children: [
                  if (state.order != null)
                    _Details(
                      order: state.order!,
                      paymentStatus: state.paymentStatus,
                      isCreatingPayment: state.isCreatingPayment,
                      onCreatePayment: controller.createPayment,
                    ),
                  if (state.error != null) ...[
                    const SizedBox(height: 12),
                    Text(state.error!.message),
                  ],
                ],
              ),
            ),
    );
  }
}

class _Details extends StatelessWidget {
  const _Details({
    required this.order,
    required this.paymentStatus,
    required this.isCreatingPayment,
    required this.onCreatePayment,
  });

  final PharmacyOrder order;
  final PaymentStatusDetails? paymentStatus;
  final bool isCreatingPayment;
  final Future<int?> Function() onCreatePayment;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Card(
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
                        style: Theme.of(context).textTheme.titleLarge,
                      ),
                    ),
                    PharmacyOrderStatusChip(status: order.status),
                  ],
                ),
                const SizedBox(height: 12),
                _InfoLine(
                  label: l10n.get('pharmacy'),
                  value: order.pharmacyName ?? '-',
                ),
                _InfoLine(
                  label: l10n.get('createdAt'),
                  value: _formatDate(context, order.createdAt),
                ),
                _InfoLine(
                  label: l10n.get('paymentStatus'),
                  value: order.paymentStatus ?? '-',
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 12),
        PharmacyPaymentCard(
          order: order,
          paymentStatus: paymentStatus,
          isCreatingPayment: isCreatingPayment,
          onCreatePayment: onCreatePayment,
        ),
        const SizedBox(height: 12),
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  l10n.get('items'),
                  style: Theme.of(context).textTheme.titleMedium,
                ),
                const SizedBox(height: 8),
                if (order.items.isEmpty)
                  Text(l10n.get('noAdditionalDetails'))
                else
                  ...order.items.map(
                    (item) => ListTile(
                      contentPadding: EdgeInsets.zero,
                      title: Text(item.productName),
                      subtitle: Text('${item.unitPrice} x ${item.quantity}'),
                      trailing: Text(item.lineTotal),
                    ),
                  ),
              ],
            ),
          ),
        ),
        if (order.prescription != null) ...[
          const SizedBox(height: 12),
          Card(
            child: ListTile(
              leading: const Icon(Icons.description_outlined),
              title: Text(l10n.get('prescriptionUploaded')),
              subtitle: Text(
                order.prescription!.fileName ?? l10n.get('privateFile'),
              ),
            ),
          ),
        ],
        if (order.notes?.isNotEmpty == true) ...[
          const SizedBox(height: 12),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Text(order.notes!),
            ),
          ),
        ],
      ],
    );
  }

  String _formatDate(BuildContext context, DateTime? value) {
    if (value == null) {
      return AppLocalizations.of(context).get('dateUnavailable');
    }
    return DateFormat('d MMM yyyy, h:mm a').format(value.toLocal());
  }
}

class _InfoLine extends StatelessWidget {
  const _InfoLine({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(top: 8),
      child: Row(
        children: [
          Expanded(child: Text(label)),
          const SizedBox(width: 12),
          Flexible(
            child: Text(
              value,
              textAlign: TextAlign.end,
              style: const TextStyle(fontWeight: FontWeight.w700),
            ),
          ),
        ],
      ),
    );
  }
}
