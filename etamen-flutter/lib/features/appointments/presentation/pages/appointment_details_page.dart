import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment_details.dart';
import 'package:etamen_app/features/appointments/presentation/providers/appointment_details_controller.dart';
import 'package:etamen_app/features/appointments/presentation/widgets/appointment_payment_card.dart';
import 'package:etamen_app/features/appointments/presentation/widgets/appointment_status_chip.dart';
import 'package:etamen_app/features/appointments/presentation/widgets/appointment_timeline.dart';
import 'package:etamen_app/features/appointments/presentation/widgets/cancel_appointment_dialog.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

class AppointmentDetailsPage extends ConsumerWidget {
  const AppointmentDetailsPage({required this.appointmentId, super.key});

  final int appointmentId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(
      appointmentDetailsControllerProvider(appointmentId),
    );
    final controller = ref.read(
      appointmentDetailsControllerProvider(appointmentId).notifier,
    );

    return AppScaffold(
      title: l10n.get('appointmentDetails'),
      body: state.isLoading && state.details == null
          ? const LoadingView()
          : state.error != null && state.details == null
          ? ErrorView(
              message: _friendlyError(context, state.error!.message),
              onRetry: controller.load,
            )
          : RefreshIndicator(
              onRefresh: controller.load,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  if (state.details != null)
                    _DetailsContent(
                      details: state.details!,
                      paymentStatus: state.paymentStatus,
                      isCancelling: state.isCancelling,
                      onCancel: () => _cancel(context, ref),
                    ),
                  if (state.error != null) ...[
                    const SizedBox(height: 12),
                    Text(
                      _friendlyError(context, state.error!.message),
                      style: const TextStyle(color: AppColors.danger),
                    ),
                  ],
                ],
              ),
            ),
    );
  }

  Future<void> _cancel(BuildContext context, WidgetRef ref) async {
    final reason = await showDialog<String?>(
      context: context,
      builder: (_) => const CancelAppointmentDialog(),
    );
    if (reason == null || !context.mounted) return;

    final success = await ref
        .read(appointmentDetailsControllerProvider(appointmentId).notifier)
        .cancel(reason: reason);

    if (!context.mounted) return;
    final l10n = AppLocalizations.of(context);
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          success
              ? l10n.get('appointmentCancelledDone')
              : l10n.get('cannotCancelAppointment'),
        ),
      ),
    );
  }

  String _friendlyError(BuildContext context, String message) {
    final lower = message.toLowerCase();
    final l10n = AppLocalizations.of(context);
    if (lower.contains('refund') || lower.contains('paid')) {
      return l10n.get('paidCancellationBlocked');
    }
    return message;
  }
}

class _DetailsContent extends StatelessWidget {
  const _DetailsContent({
    required this.details,
    required this.paymentStatus,
    required this.isCancelling,
    required this.onCancel,
  });

  final AppointmentDetails details;
  final PaymentStatusDetails? paymentStatus;
  final bool isCancelling;
  final VoidCallback onCancel;

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
                        details.appointmentNumber ?? '#${details.id}',
                        style: Theme.of(context).textTheme.titleLarge,
                      ),
                    ),
                    AppointmentStatusChip(status: details.status),
                  ],
                ),
                const SizedBox(height: 12),
                _InfoLine(
                  label: l10n.get('doctor'),
                  value: details.doctorName ?? '-',
                ),
                _InfoLine(
                  label: l10n.get('specialties'),
                  value: details.specialty ?? '-',
                ),
                _InfoLine(
                  label: l10n.get('appointmentTime'),
                  value: _formatDate(context, details.startsAt),
                ),
                _InfoLine(
                  label: l10n.get('consultationType'),
                  value: details.consultationType.wireValue,
                ),
                if (details.location != null)
                  _InfoLine(
                    label: l10n.get('location'),
                    value: details.location!,
                  ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 12),
        AppointmentPaymentCard(
          paymentId: details.paymentId,
          appointmentId: details.id,
          amount: details.price,
          currency: details.currency,
          paymentStatus: paymentStatus,
        ),
        const SizedBox(height: 12),
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  l10n.get('notes'),
                  style: Theme.of(context).textTheme.titleMedium,
                ),
                const SizedBox(height: 8),
                Text(
                  details.problemDescription?.isNotEmpty == true
                      ? details.problemDescription!
                      : l10n.get('noAdditionalDetails'),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 12),
        AppointmentTimeline(items: details.statusHistory),
        if (details.isCancellable) ...[
          const SizedBox(height: 16),
          OutlinedButton.icon(
            onPressed: isCancelling ? null : onCancel,
            icon: isCancelling
                ? const SizedBox(
                    width: 18,
                    height: 18,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Icon(Icons.cancel_outlined),
            label: Text(l10n.get('cancelAppointment')),
          ),
        ],
        const SizedBox(height: 8),
        AppButton(
          label: l10n.get('backHome'),
          onPressed: () => context.go(RouteNames.home),
        ),
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
