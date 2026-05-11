import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_order.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_result.dart';
import 'package:etamen_app/features/labs/presentation/providers/labs_providers.dart';
import 'package:etamen_app/features/labs/presentation/widgets/lab_order_status_chip.dart';
import 'package:etamen_app/features/labs/presentation/widgets/lab_payment_card.dart';
import 'package:etamen_app/features/labs/presentation/widgets/lab_result_card.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

class LabOrderDetailsPage extends ConsumerWidget {
  const LabOrderDetailsPage({required this.orderId, super.key});

  final int orderId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(labOrderDetailsControllerProvider(orderId));
    final controller = ref.read(
      labOrderDetailsControllerProvider(orderId).notifier,
    );

    return AppScaffold(
      title: l10n.get('labOrderDetails'),
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
                    Text(
                      state.error!.message,
                      style: const TextStyle(color: AppColors.danger),
                    ),
                  ],
                ],
              ),
            ),
    );
  }
}

class _Details extends ConsumerWidget {
  const _Details({
    required this.order,
    required this.paymentStatus,
    required this.isCreatingPayment,
    required this.isCancelling,
    required this.onCreatePayment,
    required this.onCancel,
  });

  final LabOrder order;
  final PaymentStatusDetails? paymentStatus;
  final bool isCreatingPayment;
  final bool isCancelling;
  final Future<int?> Function() onCreatePayment;
  final Future<bool> Function({String? reason}) onCancel;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final downloadState = ref.watch(labResultDownloadControllerProvider);
    final downloadController = ref.read(
      labResultDownloadControllerProvider.notifier,
    );

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
                    LabOrderStatusChip(status: order.status),
                  ],
                ),
                const SizedBox(height: 12),
                _InfoLine(label: l10n.get('lab'), value: order.labName ?? '-'),
                _InfoLine(
                  label: l10n.get('createdAt'),
                  value: _formatDate(context, order.createdAt),
                ),
                _InfoLine(
                  label: l10n.get('sampleCollectionMethod'),
                  value: _sampleMethodLabel(
                    context,
                    order.sampleCollectionMethod,
                  ),
                ),
                if (order.homeAddress?.isNotEmpty == true)
                  _InfoLine(
                    label: l10n.get('homeAddress'),
                    value: order.homeAddress!,
                  ),
                _InfoLine(
                  label: l10n.get('paymentStatus'),
                  value: _friendlyLabPaymentStatus(
                    context,
                    order.paymentStatus,
                  ),
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
        LabPaymentCard(
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
                      title: Text(item.itemName),
                      subtitle: Text('${item.unitPrice} x ${item.quantity}'),
                      trailing: Text(item.lineTotal),
                    ),
                  ),
              ],
            ),
          ),
        ),
        if (order.results.isNotEmpty) ...[
          const SizedBox(height: 12),
          ...order.results.map(
            (result) => LabResultCard(
              result: result,
              isDownloading: downloadState.isDownloading,
              onDownload: () => _download(context, downloadController, result),
            ),
          ),
        ] else if (order.status == LabOrderStatus.resultReady ||
            order.status == LabOrderStatus.completed) ...[
          const SizedBox(height: 12),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Text(l10n.get('resultUnavailable')),
            ),
          ),
        ],
        if (downloadState.error != null) ...[
          const SizedBox(height: 12),
          Text(
            downloadState.error!.message,
            style: const TextStyle(color: AppColors.danger),
          ),
        ],
      ],
    );
  }

  Future<void> _download(
    BuildContext context,
    LabResultDownloadController controller,
    LabResult result,
  ) async {
    final l10n = AppLocalizations.of(context);
    final download = await controller.download(result.id);
    if (download == null || !context.mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('${l10n.get('resultDownloaded')}: ${download.localPath}'),
      ),
    );
  }

  String _sampleMethodLabel(BuildContext context, String? value) {
    final l10n = AppLocalizations.of(context);
    return switch (value) {
      'home_collection' => l10n.get('homeCollection'),
      'branch_visit' => l10n.get('branchVisit'),
      _ => value ?? '-',
    };
  }

  String _friendlyLabPaymentStatus(BuildContext context, String? status) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return switch (status) {
      'unpaid' => isArabic ? 'لم يتم الدفع' : 'Unpaid',
      'pending_payment' => isArabic ? 'في انتظار الدفع' : 'Awaiting payment',
      'pending_payment_review' =>
        isArabic ? 'في انتظار مراجعة الأدمن' : 'Pending admin review',
      'paid' => isArabic ? 'تم الدفع' : 'Paid',
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
