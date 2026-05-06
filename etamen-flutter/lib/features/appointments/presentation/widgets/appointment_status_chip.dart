import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment.dart';
import 'package:flutter/material.dart';

class AppointmentStatusChip extends StatelessWidget {
  const AppointmentStatusChip({required this.status, super.key});

  final AppointmentStatus status;

  @override
  Widget build(BuildContext context) {
    final color = _color(status);
    return DecoratedBox(
      decoration: BoxDecoration(
        color: color.withValues(alpha: .12),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        child: Text(
          label(context, status),
          style: TextStyle(color: color, fontWeight: FontWeight.w800),
        ),
      ),
    );
  }

  static String label(BuildContext context, AppointmentStatus status) {
    final l10n = AppLocalizations.of(context);
    return switch (status) {
      AppointmentStatus.draft => l10n.get('appointmentDraft'),
      AppointmentStatus.pendingPayment => l10n.get('pendingPayment'),
      AppointmentStatus.pendingPaymentReview => l10n.get(
        'pendingPaymentReview',
      ),
      AppointmentStatus.confirmed => l10n.get('appointmentConfirmed'),
      AppointmentStatus.accepted => l10n.get('appointmentAccepted'),
      AppointmentStatus.rejected => l10n.get('appointmentRejected'),
      AppointmentStatus.cancelled => l10n.get('appointmentCancelled'),
      AppointmentStatus.cancelledByPatient => l10n.get('cancelledByPatient'),
      AppointmentStatus.cancelledByDoctor => l10n.get('cancelledByDoctor'),
      AppointmentStatus.completed => l10n.get('appointmentCompleted'),
      AppointmentStatus.noShow => l10n.get('appointmentNoShow'),
      AppointmentStatus.expired => l10n.get('appointmentExpired'),
      AppointmentStatus.unknown => l10n.get('unknownStatus'),
    };
  }

  Color _color(AppointmentStatus status) {
    return switch (status) {
      AppointmentStatus.confirmed ||
      AppointmentStatus.accepted ||
      AppointmentStatus.completed => AppColors.success,
      AppointmentStatus.pendingPayment ||
      AppointmentStatus.pendingPaymentReview ||
      AppointmentStatus.noShow => AppColors.warning,
      AppointmentStatus.rejected => AppColors.danger,
      AppointmentStatus.cancelled ||
      AppointmentStatus.cancelledByPatient ||
      AppointmentStatus.cancelledByDoctor ||
      AppointmentStatus.expired => AppColors.muted,
      _ => AppColors.primary,
    };
  }
}
