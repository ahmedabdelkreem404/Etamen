import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/payments/presentation/providers/payment_status_controller.dart';
import 'package:etamen_app/features/payments/presentation/widgets/payment_copy.dart';
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
    this.labOrderId,
    this.radiologyOrderId,
    this.gymBookingId,
    this.coachBookingId,
    super.key,
  });

  final int paymentId;
  final int? appointmentId;
  final int? pharmacyOrderId;
  final int? labOrderId;
  final int? radiologyOrderId;
  final int? gymBookingId;
  final int? coachBookingId;

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
          label: _verifiedActionLabel(context, l10n),
          onPressed: () => _goToPayable(context),
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
              labOrderId: widget.labOrderId,
              radiologyOrderId: widget.radiologyOrderId,
              gymBookingId: widget.gymBookingId,
              coachBookingId: widget.coachBookingId,
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

  String _verifiedActionLabel(BuildContext context, AppLocalizations l10n) {
    if (widget.labOrderId != null) return l10n.get('labOrderDetails');
    if (widget.radiologyOrderId != null) {
      return uxCopy(context, 'تفاصيل طلب الأشعة', 'Radiology order');
    }
    if (widget.gymBookingId != null) {
      return uxCopy(context, 'تفاصيل حجز الجيم', 'Gym booking');
    }
    if (widget.coachBookingId != null) {
      return uxCopy(context, 'تفاصيل حجز الكوتش', 'Coach booking');
    }
    if (widget.pharmacyOrderId != null) return l10n.get('pharmacyOrderDetails');
    return l10n.get('viewAppointment');
  }

  void _goToPayable(BuildContext context) {
    if (widget.appointmentId != null) {
      context.go(RouteNames.appointmentDetails(widget.appointmentId!));
      return;
    }
    if (widget.pharmacyOrderId != null) {
      context.go(RouteNames.pharmacyOrderDetails(widget.pharmacyOrderId!));
      return;
    }
    if (widget.labOrderId != null) {
      context.go(RouteNames.labOrderDetails(widget.labOrderId!));
      return;
    }
    if (widget.radiologyOrderId != null) {
      context.go(RouteNames.radiologyOrderDetails(widget.radiologyOrderId!));
      return;
    }
    if (widget.gymBookingId != null) {
      context.go(RouteNames.gymBookingDetails(widget.gymBookingId!));
      return;
    }
    if (widget.coachBookingId != null) {
      context.go(RouteNames.coachBookingDetails(widget.coachBookingId!));
      return;
    }
    context.go(RouteNames.home);
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
              value: friendlyPaymentMethodType(context, status.methodType),
            ),
            _PayableStatusLine(status: status),
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

class _PayableStatusLine extends StatelessWidget {
  const _PayableStatusLine({required this.status});

  final PaymentStatusDetails status;

  @override
  Widget build(BuildContext context) {
    if (status.pharmacyOrderStatus != null ||
        status.pharmacyPaymentStatus != null) {
      return _InfoLine(
        label: uxCopy(context, 'حالة طلب الصيدلية', 'Pharmacy order status'),
        value: _friendlyPharmacyStatus(
          context,
          status.pharmacyOrderStatus,
          status.pharmacyPaymentStatus,
        ),
      );
    }
    if (status.labOrderStatus != null || status.labPaymentStatus != null) {
      return _InfoLine(
        label: uxCopy(context, 'حالة طلب المعمل', 'Lab order status'),
        value: _friendlyLabStatus(
          context,
          status.labOrderStatus,
          status.labPaymentStatus,
        ),
      );
    }
    if (status.radiologyOrderStatus != null) {
      return _InfoLine(
        label: uxCopy(context, 'حالة طلب الأشعة', 'Radiology status'),
        value: _friendlyRadiologyStatus(context, status.radiologyOrderStatus),
      );
    }
    if (status.gymBookingStatus != null) {
      return _InfoLine(
        label: uxCopy(context, 'حالة حجز الجيم', 'Gym booking status'),
        value: _friendlyGymStatus(context, status.gymBookingStatus),
      );
    }
    if (status.coachBookingStatus != null) {
      return _InfoLine(
        label: uxCopy(context, 'حالة حجز الكوتش', 'Coach booking status'),
        value: _friendlyCoachStatus(context, status.coachBookingStatus),
      );
    }
    return _InfoLine(
      label: AppLocalizations.of(context).get('appointmentStatus'),
      value: friendlyAppointmentStatus(context, status.appointmentStatus),
    );
  }
}

String _friendlyPharmacyStatus(
  BuildContext context,
  String? orderStatus,
  String? paymentStatus,
) {
  final isArabic = AppLocalizations.of(context).isArabic;
  if (paymentStatus == 'pending_payment_review') {
    return isArabic ? 'الدفع في انتظار مراجعة الأدمن' : 'Payment under review';
  }
  if (paymentStatus == 'pending_payment') {
    return isArabic ? 'في انتظار الدفع' : 'Awaiting payment';
  }
  if (paymentStatus == 'paid') return isArabic ? 'تم الدفع' : 'Paid';

  return switch (orderStatus) {
    'pharmacy_review' => isArabic ? 'في مراجعة الصيدلية' : 'Pharmacy review',
    'accepted' ||
    'awaiting_payment' => isArabic ? 'في انتظار الدفع' : 'Awaiting payment',
    'paid' => isArabic ? 'تم الدفع' : 'Paid',
    'preparing' => isArabic ? 'جاري التحضير' : 'Preparing',
    'ready_for_pickup' => isArabic ? 'جاهز للاستلام' : 'Ready for pickup',
    'out_for_delivery' => isArabic ? 'خرج للتوصيل' : 'Out for delivery',
    'delivered' => isArabic ? 'تم التسليم' : 'Delivered',
    'cancelled' => isArabic ? 'ملغي' : 'Cancelled',
    'rejected' => isArabic ? 'مرفوض' : 'Rejected',
    _ => isArabic ? 'غير متاح' : 'Unavailable',
  };
}

String _friendlyLabStatus(
  BuildContext context,
  String? orderStatus,
  String? paymentStatus,
) {
  final isArabic = AppLocalizations.of(context).isArabic;
  if (paymentStatus == 'pending_payment_review') {
    return isArabic ? 'الدفع في انتظار مراجعة الأدمن' : 'Payment under review';
  }
  if (paymentStatus == 'pending_payment') {
    return isArabic ? 'في انتظار الدفع' : 'Awaiting payment';
  }
  if (paymentStatus == 'paid') return isArabic ? 'تم الدفع' : 'Paid';

  return switch (orderStatus) {
    'lab_review' => isArabic ? 'في مراجعة المعمل' : 'Lab review',
    'accepted' ||
    'awaiting_payment' => isArabic ? 'في انتظار الدفع' : 'Awaiting payment',
    'paid' => isArabic ? 'تم الدفع' : 'Paid',
    'sample_scheduled' =>
      isArabic ? 'تم تحديد موعد العينة' : 'Sample scheduled',
    'sample_collected' => isArabic ? 'تم سحب العينة' : 'Sample collected',
    'processing' => isArabic ? 'قيد التحليل' : 'Processing',
    'result_ready' => isArabic ? 'النتيجة جاهزة' : 'Result ready',
    'completed' => isArabic ? 'مكتمل' : 'Completed',
    'cancelled' => isArabic ? 'ملغي' : 'Cancelled',
    'rejected' => isArabic ? 'مرفوض' : 'Rejected',
    _ => isArabic ? 'غير متاح' : 'Unavailable',
  };
}

String _friendlyRadiologyStatus(BuildContext context, String? status) {
  final isArabic = AppLocalizations.of(context).isArabic;
  return switch (status) {
    'pending_payment' => isArabic ? 'في انتظار الدفع' : 'Awaiting payment',
    'pending_payment_review' =>
      isArabic ? 'جاري مراجعة الدفع' : 'Payment under review',
    'paid' => isArabic ? 'تم الدفع' : 'Paid',
    'accepted' => isArabic ? 'تم قبول الطلب' : 'Accepted',
    'in_progress' => isArabic ? 'جاري التنفيذ' : 'In progress',
    'result_ready' => isArabic ? 'النتيجة جاهزة' : 'Result ready',
    'completed' => isArabic ? 'مكتمل' : 'Completed',
    'cancelled_by_patient' => isArabic ? 'ملغي بواسطتك' : 'Cancelled by you',
    'cancelled_by_provider' =>
      isArabic ? 'ملغي من المركز' : 'Cancelled by center',
    'rejected' => isArabic ? 'مرفوض' : 'Rejected',
    _ => isArabic ? 'غير متاح' : 'Unavailable',
  };
}

String _friendlyGymStatus(BuildContext context, String? status) {
  final isArabic = AppLocalizations.of(context).isArabic;
  return switch (status) {
    'pending_payment' => isArabic ? 'في انتظار الدفع' : 'Awaiting payment',
    'pending_payment_review' =>
      isArabic ? 'جاري مراجعة الدفع' : 'Payment under review',
    'paid' => isArabic ? 'تم الدفع' : 'Paid',
    'confirmed' => isArabic ? 'مؤكد' : 'Confirmed',
    'active' => isArabic ? 'نشط' : 'Active',
    'completed' => isArabic ? 'مكتمل' : 'Completed',
    'cancelled_by_user' => isArabic ? 'ملغي بواسطتك' : 'Cancelled by you',
    'cancelled_by_provider' => isArabic ? 'ملغي من الجيم' : 'Cancelled by gym',
    'rejected' => isArabic ? 'مرفوض' : 'Rejected',
    _ => isArabic ? 'غير متاح' : 'Unavailable',
  };
}

String _friendlyCoachStatus(BuildContext context, String? status) {
  final isArabic = AppLocalizations.of(context).isArabic;
  return switch (status) {
    'pending_payment' => isArabic ? 'في انتظار الدفع' : 'Awaiting payment',
    'pending_payment_review' =>
      isArabic ? 'جاري مراجعة الدفع' : 'Payment under review',
    'paid' => isArabic ? 'تم الدفع' : 'Paid',
    'confirmed' => isArabic ? 'مؤكد' : 'Confirmed',
    'in_progress' => isArabic ? 'قيد التنفيذ' : 'In progress',
    'completed' => isArabic ? 'مكتمل' : 'Completed',
    'cancelled_by_user' => isArabic ? 'ملغي بواسطتك' : 'Cancelled by you',
    'cancelled_by_coach' => isArabic ? 'ملغي من الكوتش' : 'Cancelled by coach',
    'rejected' => isArabic ? 'مرفوض' : 'Rejected',
    _ => isArabic ? 'غير متاح' : 'Unavailable',
  };
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
