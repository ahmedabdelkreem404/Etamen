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
                      isCancelling: state.isCancelling,
                      onCreatePayment: controller.createPayment,
                      onCancel: controller.cancel,
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
    required this.isCancelling,
    required this.onCreatePayment,
    required this.onCancel,
  });

  final PharmacyOrder order;
  final PaymentStatusDetails? paymentStatus;
  final bool isCreatingPayment;
  final bool isCancelling;
  final Future<int?> Function() onCreatePayment;
  final Future<bool> Function({String? reason}) onCancel;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final isArabic = AppLocalizations.of(context).isArabic;
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
                  value: order.paymentStatusLabel(isArabic: isArabic),
                ),
                if (order.paymentStatus == 'pending_payment_review') ...[
                  const SizedBox(height: 8),
                  Text(
                    _copy(
                      context,
                      'الدفع في انتظار مراجعة الأدمن.',
                      'Payment is waiting for admin review.',
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),
        const SizedBox(height: 12),
        if (order.nextActionLabel(isArabic: isArabic) != null) ...[
          Card(
            child: ListTile(
              leading: const Icon(Icons.flag_outlined),
              title: Text(_copy(context, 'الإجراء التالي', 'Next action')),
              subtitle: Text(order.nextActionLabel(isArabic: isArabic)!),
            ),
          ),
          const SizedBox(height: 12),
        ],
        _PharmacyTimeline(order: order),
        const SizedBox(height: 12),
        PharmacyPaymentCard(
          order: order,
          paymentStatus: paymentStatus,
          isCreatingPayment: isCreatingPayment,
          onCreatePayment: onCreatePayment,
        ),
        if (order.canCancel) ...[
          const SizedBox(height: 12),
          OutlinedButton.icon(
            onPressed: isCancelling ? null : () => _confirmCancel(context),
            icon: const Icon(Icons.cancel_outlined),
            label: Text(
              isCancelling
                  ? _copy(context, 'جاري الإلغاء...', 'Cancelling...')
                  : _copy(
                      context,
                      'إلغاء الطلب قبل الدفع',
                      'Cancel before payment',
                    ),
            ),
          ),
        ],
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

  // ignore: unused_element
  String _friendlyPharmacyPaymentStatus(BuildContext context, String? status) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return switch (status) {
      'unpaid' => isArabic ? 'لم يتم الدفع' : 'Unpaid',
      'pending_payment' => isArabic ? 'في انتظار الدفع' : 'Awaiting payment',
      'pending_payment_review' =>
        isArabic ? 'في انتظار مراجعة الأدمن' : 'Pending admin review',
      'paid' => isArabic ? 'تم الدفع' : 'Paid',
      'rejected' => isArabic ? 'مرفوض' : 'Rejected',
      'failed' => isArabic ? 'فشل الدفع' : 'Payment failed',
      'refunded' => isArabic ? 'تم الاسترداد' : 'Refunded',
      _ => status ?? '-',
    };
  }

  String _copy(BuildContext context, String ar, String en) {
    return AppLocalizations.of(context).isArabic ? ar : en;
  }

  Future<void> _confirmCancel(BuildContext context) async {
    final l10n = AppLocalizations.of(context);
    final reasonController = TextEditingController();
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (dialogContext) {
        return AlertDialog(
          title: Text(_copy(context, 'تأكيد إلغاء الطلب', 'Cancel order')),
          content: TextField(
            controller: reasonController,
            minLines: 2,
            maxLines: 3,
            decoration: InputDecoration(
              labelText: _copy(context, 'سبب اختياري', 'Optional reason'),
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(dialogContext).pop(false),
              child: Text(l10n.get('cancel')),
            ),
            FilledButton(
              onPressed: () => Navigator.of(dialogContext).pop(true),
              child: Text(_copy(context, 'إلغاء الطلب', 'Cancel order')),
            ),
          ],
        );
      },
    );

    if (confirmed != true || !context.mounted) return;
    final success = await onCancel(reason: reasonController.text);
    if (!context.mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          success
              ? _copy(
                  context,
                  'تم إلغاء الطلب محليا.',
                  'Order cancelled locally.',
                )
              : _copy(context, 'تعذر إلغاء الطلب.', 'Could not cancel order.'),
        ),
      ),
    );
  }
}

class _PharmacyTimeline extends StatelessWidget {
  const _PharmacyTimeline({required this.order});

  final PharmacyOrder order;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    final steps = <(PharmacyOrderStatus, String, String)>[
      (PharmacyOrderStatus.pharmacyReview, 'مراجعة', 'Review'),
      (PharmacyOrderStatus.awaitingPayment, 'الدفع', 'Payment'),
      (PharmacyOrderStatus.preparing, 'تجهيز', 'Preparing'),
      (PharmacyOrderStatus.readyForPickup, 'جاهز', 'Ready'),
      (PharmacyOrderStatus.outForDelivery, 'توصيل', 'Delivery'),
      (PharmacyOrderStatus.delivered, 'مكتمل', 'Done'),
    ];

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              isArabic ? 'مسار الطلب' : 'Order timeline',
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                for (final step in steps)
                  Chip(
                    avatar: Icon(
                      _isReached(order.status, step.$1)
                          ? Icons.check_circle
                          : Icons.circle_outlined,
                      size: 18,
                    ),
                    label: Text(isArabic ? step.$2 : step.$3),
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  bool _isReached(PharmacyOrderStatus current, PharmacyOrderStatus step) {
    final flow = [
      PharmacyOrderStatus.pharmacyReview,
      PharmacyOrderStatus.accepted,
      PharmacyOrderStatus.awaitingPayment,
      PharmacyOrderStatus.paid,
      PharmacyOrderStatus.preparing,
      PharmacyOrderStatus.readyForPickup,
      PharmacyOrderStatus.outForDelivery,
      PharmacyOrderStatus.delivered,
    ];
    final currentIndex = flow.indexOf(current);
    final stepIndex = flow.indexOf(step);
    return currentIndex >= 0 && stepIndex >= 0 && currentIndex >= stepIndex;
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
