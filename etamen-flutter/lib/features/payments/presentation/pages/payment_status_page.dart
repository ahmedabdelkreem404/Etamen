import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/payments/presentation/providers/payment_status_controller.dart';
import 'package:etamen_app/features/payments/presentation/widgets/payment_polling_banner.dart';
import 'package:etamen_app/features/payments/presentation/widgets/payment_status_badge.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class PaymentStatusPage extends ConsumerStatefulWidget {
  const PaymentStatusPage({
    required this.paymentId,
    this.appointmentId,
    this.pharmacyOrderId,
    super.key,
  });

  final int paymentId;
  final int? appointmentId;
  final int? pharmacyOrderId;

  @override
  ConsumerState<PaymentStatusPage> createState() => _PaymentStatusPageState();
}

class _PaymentStatusPageState extends ConsumerState<PaymentStatusPage> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      ref
          .read(paymentStatusControllerProvider(widget.paymentId).notifier)
          .startPolling();
    });
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(paymentStatusControllerProvider(widget.paymentId));

    return AppScaffold(
      title: l10n.get('paymentStatus'),
      body: state.isLoading && state.status == null
          ? const LoadingView()
          : state.error != null && state.status == null
          ? ErrorView(
              message: state.error!.message,
              onRetry: () => ref
                  .read(
                    paymentStatusControllerProvider(widget.paymentId).notifier,
                  )
                  .loadStatus(),
            )
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                if (state.status != null) _StatusCard(status: state.status!),
                const SizedBox(height: 16),
                if (state.status?.status.shouldPoll == true)
                  PaymentPollingBanner(isPolling: state.isPolling),
                if (state.error != null) ...[
                  const SizedBox(height: 12),
                  Text(
                    state.error!.message,
                    style: const TextStyle(color: AppColors.danger),
                  ),
                ],
                const SizedBox(height: 24),
                ..._actions(context, state.status?.status),
              ],
            ),
    );
  }

  List<Widget> _actions(BuildContext context, PaymentStatusEnum? status) {
    final l10n = AppLocalizations.of(context);
    final controller = ref.read(
      paymentStatusControllerProvider(widget.paymentId).notifier,
    );

    if (status == PaymentStatusEnum.verified) {
      return [
        AppButton(
          label: widget.pharmacyOrderId != null
              ? l10n.get('pharmacyOrderDetails')
              : l10n.get('viewAppointment'),
          onPressed: () {
            if (widget.appointmentId != null) {
              context.go(RouteNames.appointmentDetails(widget.appointmentId!));
              return;
            }
            if (widget.pharmacyOrderId != null) {
              context.go(
                RouteNames.pharmacyOrderDetails(widget.pharmacyOrderId!),
              );
              return;
            }
            context.go(RouteNames.home);
          },
        ),
        const SizedBox(height: 12),
        AppButton(
          label: l10n.get('backHome'),
          onPressed: () => context.go(RouteNames.home),
        ),
      ];
    }

    if (status == PaymentStatusEnum.awaitingProof ||
        status == PaymentStatusEnum.rejected) {
      return [
        AppButton(
          label: status == PaymentStatusEnum.rejected
              ? l10n.get('retryProof')
              : l10n.get('uploadProof'),
          onPressed: () => context.push(
            RouteNames.payment(
              widget.paymentId,
              appointmentId: widget.appointmentId,
              pharmacyOrderId: widget.pharmacyOrderId,
            ),
          ),
        ),
      ];
    }

    return [
      AppButton(
        label: l10n.get('refresh'),
        onPressed: () => controller.loadStatus(),
      ),
      const SizedBox(height: 12),
      AppButton(
        label: l10n.get('backHome'),
        onPressed: () => context.go(RouteNames.home),
      ),
    ];
  }
}

class _StatusCard extends StatelessWidget {
  const _StatusCard({required this.status});

  final PaymentStatusDetails status;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
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
            const SizedBox(height: 12),
            _InfoLine(
              label: l10n.get('paymentMethod'),
              value: status.methodType ?? '-',
            ),
            _InfoLine(
              label: l10n.get('appointmentStatus'),
              value: status.appointmentStatus ?? '-',
            ),
            _InfoLine(
              label: l10n.get('lastUpdated'),
              value: status.updatedAt?.toLocal().toString() ?? '-',
            ),
            if (status.status == PaymentStatusEnum.pendingReview) ...[
              const SizedBox(height: 12),
              Text(l10n.get('waitingAdminReview')),
            ],
            if (status.status == PaymentStatusEnum.pendingGateway) ...[
              const SizedBox(height: 12),
              Text(l10n.get('waitingGatewayConfirmation')),
            ],
            if (status.status == PaymentStatusEnum.verified) ...[
              const SizedBox(height: 12),
              Text(
                l10n.get('paymentVerified'),
                style: const TextStyle(
                  color: AppColors.success,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ],
            if (status.status == PaymentStatusEnum.rejected) ...[
              const SizedBox(height: 12),
              Text(
                status.rejectionReason?.isNotEmpty == true
                    ? status.rejectionReason!
                    : l10n.get('paymentRejectedRetry'),
                style: const TextStyle(color: AppColors.danger),
              ),
            ],
          ],
        ),
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
