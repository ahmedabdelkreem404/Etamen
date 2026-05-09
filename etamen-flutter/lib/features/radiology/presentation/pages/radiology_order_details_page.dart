import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_order.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_result.dart';
import 'package:etamen_app/features/radiology/presentation/providers/radiology_providers.dart';
import 'package:etamen_app/features/radiology/presentation/widgets/radiology_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

class RadiologyOrderDetailsPage extends ConsumerWidget {
  const RadiologyOrderDetailsPage({required this.orderId, super.key});

  final int orderId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(radiologyOrderDetailsControllerProvider(orderId));
    final controller = ref.read(
      radiologyOrderDetailsControllerProvider(orderId).notifier,
    );

    return AppScaffold(
      title: uxCopy(context, 'تفاصيل طلب الأشعة', 'Radiology order'),
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
                    _OrderDetails(
                      order: state.order!,
                      paymentStatus: state.paymentStatus,
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

class _OrderDetails extends ConsumerWidget {
  const _OrderDetails({required this.order, required this.paymentStatus});

  final RadiologyOrder order;
  final PaymentStatusDetails? paymentStatus;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final downloadState = ref.watch(radiologyResultDownloadControllerProvider);
    final downloadController = ref.read(
      radiologyResultDownloadControllerProvider.notifier,
    );
    final provider = order.provider?.name(l10n.isArabic) ?? '-';
    final branchAddress = order.branch?.address(l10n.isArabic);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        SoftMedicalCard(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(
                      order.orderNumber ?? '#${order.id}',
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ),
                  RadiologyStatusChip(status: order.status),
                ],
              ),
              const SizedBox(height: 12),
              _InfoLine(
                label: uxCopy(context, 'المركز', 'Center'),
                value: provider,
              ),
              if (branchAddress?.isNotEmpty == true)
                _InfoLine(
                  label: uxCopy(context, 'العنوان', 'Address'),
                  value: branchAddress!,
                ),
              _InfoLine(
                label: uxCopy(context, 'تاريخ الطلب', 'Created at'),
                value: _formatDate(context, order.createdAt),
              ),
              if (order.patientNotes?.isNotEmpty == true)
                _InfoLine(
                  label: uxCopy(context, 'ملاحظاتك', 'Your notes'),
                  value: order.patientNotes!,
                ),
            ],
          ),
        ),
        const SizedBox(height: 12),
        RadiologyPaymentCard(order: order, paymentStatus: paymentStatus),
        const SizedBox(height: 12),
        SoftMedicalCard(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                uxCopy(context, 'الفحوصات', 'Scans'),
                style: Theme.of(
                  context,
                ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
              ),
              const SizedBox(height: 8),
              if (order.items.isEmpty)
                Text(uxCopy(context, 'لا توجد تفاصيل إضافية', 'No details'))
              else
                ...order.items.map(
                  (item) => ListTile(
                    contentPadding: EdgeInsets.zero,
                    title: Text(item.scanName(l10n.isArabic)),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('${item.unitPrice ?? '-'} x ${item.quantity}'),
                        if (item.preparation(l10n.isArabic)?.isNotEmpty == true)
                          Text(
                            item.preparation(l10n.isArabic)!,
                            style: const TextStyle(color: AppColors.muted),
                          ),
                      ],
                    ),
                    trailing: Text(item.totalPrice ?? '-'),
                  ),
                ),
            ],
          ),
        ),
        const SizedBox(height: 12),
        _SafeResultNotice(),
        const SizedBox(height: 12),
        if (order.results.isNotEmpty)
          ...order.results.map(
            (result) => RadiologyResultCard(
              result: result,
              isDownloading: downloadState.isDownloading,
              onDownload: () => _download(context, downloadController, result),
            ),
          )
        else if (order.status.hasResult)
          SoftMedicalCard(
            padding: const EdgeInsets.all(14),
            child: Text(
              uxCopy(
                context,
                'لم تظهر ملفات النتيجة للمريض بعد.',
                'Result files are not visible to the patient yet.',
              ),
            ),
          ),
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
    RadiologyResultDownloadController controller,
    RadiologyResult result,
  ) async {
    final download = await controller.download(result.id);
    if (download == null || !context.mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          uxCopy(
            context,
            'تم تحميل ملف النتيجة بنجاح',
            'Result file downloaded successfully',
          ),
        ),
      ),
    );
  }

  String _formatDate(BuildContext context, DateTime? value) {
    if (value == null) return uxCopy(context, 'غير متاح', 'Unavailable');
    return DateFormat('d MMM yyyy, h:mm a').format(value.toLocal());
  }
}

class _SafeResultNotice extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return SoftMedicalCard(
      padding: const EdgeInsets.all(14),
      child: Row(
        children: [
          const Icon(Icons.info_outline, color: AppColors.primary),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              uxCopy(
                context,
                'اطمن يعرض ملف النتيجة فقط ولا يفسر الأشعة أو يقدم تشخيصًا.',
                'Etamen only shows the result file and does not interpret scans or diagnose.',
              ),
              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                color: AppColors.softText,
                height: 1.35,
              ),
            ),
          ),
        ],
      ),
    );
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
