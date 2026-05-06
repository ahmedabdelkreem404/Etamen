import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment.dart';
import 'package:etamen_app/features/appointments/presentation/widgets/appointment_status_chip.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

class AppointmentCard extends StatelessWidget {
  const AppointmentCard({required this.appointment, super.key});

  final Appointment appointment;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: () =>
            context.push(RouteNames.appointmentDetails(appointment.id)),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const CircleAvatar(child: Icon(Icons.person)),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          appointment.doctorName ?? l10n.get('doctor'),
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                        if (appointment.specialty != null)
                          Text(
                            appointment.specialty!,
                            style: const TextStyle(color: AppColors.muted),
                          ),
                      ],
                    ),
                  ),
                  AppointmentStatusChip(status: appointment.status),
                ],
              ),
              const SizedBox(height: 12),
              _Line(
                icon: Icons.schedule,
                text: _formatRange(context, appointment),
              ),
              if (appointment.location != null) ...[
                const SizedBox(height: 6),
                _Line(
                  icon: Icons.location_on_outlined,
                  text: appointment.location!,
                ),
              ],
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(
                    child: Text(
                      '${appointment.price} ${appointment.currency}',
                      style: const TextStyle(fontWeight: FontWeight.w800),
                    ),
                  ),
                  if (appointment.paymentStatus != null)
                    Text(
                      appointment.paymentStatus!,
                      style: const TextStyle(color: AppColors.warning),
                    ),
                ],
              ),
              const SizedBox(height: 12),
              Align(
                alignment: AlignmentDirectional.centerEnd,
                child: FilledButton.tonal(
                  onPressed: () {
                    if (appointment.isPendingPayment &&
                        appointment.paymentId != null) {
                      context.push(
                        RouteNames.payment(
                          appointment.paymentId!,
                          appointmentId: appointment.id,
                        ),
                      );
                      return;
                    }
                    context.push(RouteNames.appointmentDetails(appointment.id));
                  },
                  child: Text(
                    appointment.isPendingPayment &&
                            appointment.paymentId != null
                        ? l10n.get('payNow')
                        : l10n.get('viewDetails'),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _formatRange(BuildContext context, Appointment appointment) {
    final l10n = AppLocalizations.of(context);
    final start = appointment.startsAt;
    if (start == null) return l10n.get('dateUnavailable');
    final formatter = DateFormat(
      l10n.isArabic ? 'd MMM yyyy، h:mm a' : 'MMM d, yyyy h:mm a',
    );
    return formatter.format(start.toLocal());
  }
}

class _Line extends StatelessWidget {
  const _Line({required this.icon, required this.text});

  final IconData icon;
  final String text;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 18, color: AppColors.muted),
        const SizedBox(width: 8),
        Expanded(child: Text(text)),
      ],
    );
  }
}
