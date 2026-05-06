import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_method.dart';
import 'package:etamen_app/features/payments/presentation/providers/payment_controller.dart';
import 'package:etamen_app/features/payments/presentation/providers/payment_status_controller.dart';
import 'package:etamen_app/features/payments/presentation/widgets/payment_method_card.dart';
import 'package:etamen_app/features/payments/presentation/widgets/payment_status_badge.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class PaymentPage extends ConsumerWidget {
  const PaymentPage({
    required this.paymentId,
    this.appointmentId,
    this.pharmacyOrderId,
    this.labOrderId,
    super.key,
  });

  final int paymentId;
  final int? appointmentId;
  final int? pharmacyOrderId;
  final int? labOrderId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(paymentControllerProvider(paymentId));
    final statusState = ref.watch(paymentStatusControllerProvider(paymentId));
    final status = statusState.status;

    return AppScaffold(
      title: l10n.get('paymentMethods'),
      body: RefreshIndicator(
        onRefresh: () async {
          await ref
              .read(paymentControllerProvider(paymentId).notifier)
              .loadMethods();
          await ref
              .read(paymentStatusControllerProvider(paymentId).notifier)
              .loadStatus();
        },
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            _PaymentFlowBanner(
              title: uxCopy(context, 'اختار طريقة الدفع', 'Choose payment'),
              subtitle: uxCopy(
                context,
                'الدفع اليدوي يتم مراجعته من الإدارة قبل تأكيد الخدمة.',
                'Manual payments are reviewed before confirmation.',
              ),
            ),
            const SizedBox(height: 16),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      l10n.get('paymentSummary'),
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 8),
                    if (statusState.isLoading && status == null)
                      const LoadingView()
                    else if (status != null) ...[
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              '${status.amount} ${status.currency}',
                              style: Theme.of(context).textTheme.headlineSmall,
                            ),
                          ),
                          PaymentStatusBadge(status: status.status),
                        ],
                      ),
                      if (status.appointmentStatus != null) ...[
                        const SizedBox(height: 8),
                        Text(
                          '${l10n.get('appointmentStatus')}: ${status.appointmentStatus}',
                        ),
                      ],
                    ] else
                      Text(l10n.get('paymentStatusUnavailable')),
                    const SizedBox(height: 12),
                    Text(
                      l10n.get('paymentAdminReviewNotice'),
                      style: const TextStyle(color: AppColors.muted),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            if (state.isLoading)
              const LoadingView()
            else if (state.error != null)
              ErrorView(
                message: state.error!.message,
                onRetry: () => ref
                    .read(paymentControllerProvider(paymentId).notifier)
                    .loadMethods(),
              )
            else if (state.methods.isEmpty)
              EmptyView(message: l10n.get('emptyPaymentMethods'))
            else ...[
              Text(
                l10n.get('choosePaymentMethod'),
                style: Theme.of(context).textTheme.titleLarge,
              ),
              const SizedBox(height: 8),
              ...state.methods.map(
                (method) => PaymentMethodCard(
                  method: method,
                  isLoading: state.isCreatingPaymobSession,
                  onTap: () => _handleMethod(context, method),
                ),
              ),
            ],
            const SizedBox(height: 16),
            AppButton(
              label: l10n.get('checkPaymentStatus'),
              onPressed: () => context.push(
                RouteNames.paymentStatus(
                  paymentId,
                  appointmentId: appointmentId,
                  pharmacyOrderId: pharmacyOrderId,
                  labOrderId: labOrderId,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _handleMethod(BuildContext context, PaymentMethod method) {
    if (method.type.isManual) {
      context.push(
        RouteNames.manualPayment(
          paymentId,
          methodId: method.id,
          appointmentId: appointmentId,
          pharmacyOrderId: pharmacyOrderId,
          labOrderId: labOrderId,
        ),
      );
      return;
    }

    if (method.type == PaymentMethodType.paymob) {
      context.push(
        RouteNames.paymobCheckout(
          paymentId,
          appointmentId: appointmentId,
          pharmacyOrderId: pharmacyOrderId,
          labOrderId: labOrderId,
        ),
      );
    }
  }
}

class _PaymentFlowBanner extends StatelessWidget {
  const _PaymentFlowBanner({required this.title, required this.subtitle});

  final String title;
  final String subtitle;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.primary,
        borderRadius: BorderRadius.circular(18),
      ),
      child: Row(
        children: [
          Container(
            width: 46,
            height: 46,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.14),
              borderRadius: BorderRadius.circular(14),
            ),
            child: const Icon(
              Icons.verified_user_outlined,
              color: Colors.white,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    color: Colors.white,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  subtitle,
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: Colors.white.withValues(alpha: 0.86),
                    height: 1.35,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
