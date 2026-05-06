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
                      onCreatePayment: controller.createPayment,
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
    required this.onCreatePayment,
  });

  final LabOrder order;
  final PaymentStatusDetails? paymentStatus;
  final bool isCreatingPayment;
  final Future<int?> Function() onCreatePayment;

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
                  value: order.paymentStatus ?? '-',
                ),
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
