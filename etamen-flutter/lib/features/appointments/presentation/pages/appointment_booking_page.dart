import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/appointments/data/models/book_appointment_request.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment.dart';
import 'package:etamen_app/features/appointments/presentation/providers/appointment_booking_controller.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor_slot.dart';
import 'package:etamen_app/features/doctors/presentation/providers/doctors_providers.dart';
import 'package:etamen_app/features/doctors/presentation/widgets/slot_picker.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class AppointmentBookingPage extends ConsumerStatefulWidget {
  const AppointmentBookingPage({required this.doctorId, super.key});

  final int doctorId;

  @override
  ConsumerState<AppointmentBookingPage> createState() =>
      _AppointmentBookingPageState();
}

class _AppointmentBookingPageState
    extends ConsumerState<AppointmentBookingPage> {
  DoctorSlot? _selectedSlot;
  ConsultationType _consultationType = ConsultationType.clinic;
  final _problemController = TextEditingController();

  @override
  void dispose() {
    _problemController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final details = ref.watch(doctorDetailsProvider(widget.doctorId));
    final slots = ref.watch(doctorSlotsProvider(widget.doctorId));
    final bookingState = ref.watch(appointmentBookingControllerProvider);

    return AppScaffold(
      title: l10n.get('bookAppointment'),
      body: details.when(
        loading: () => const LoadingView(),
        error: (error, _) => ErrorView(message: error.toString()),
        data: (doctorResult) => doctorResult.when(
          success: (doctor) => ListView(
            padding: const EdgeInsets.all(16),
            children: [
              const _BookingStepper(currentStep: 1),
              const SizedBox(height: 16),
              _DoctorSummary(doctor: doctor),
              const SizedBox(height: 16),
              SoftMedicalCard(
                child: slots.when(
                  loading: () => const LoadingView(),
                  error: (error, _) => ErrorView(message: error.toString()),
                  data: (slotResult) => slotResult.when(
                    success: (items) => SlotPicker(
                      slots: items.where((slot) => slot.isAvailable).toList(),
                      selectedSlot: _selectedSlot,
                      onSelected: (slot) =>
                          setState(() => _selectedSlot = slot),
                    ),
                    failure: (failure) =>
                        ErrorView(message: failure.error.message),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              SoftMedicalCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      uxCopy(context, 'أكد بيانات الحجز', 'Confirm details'),
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      l10n.get('consultationType'),
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                    const SizedBox(height: 8),
                    SegmentedButton<ConsultationType>(
                      segments: [
                        ButtonSegment(
                          value: ConsultationType.clinic,
                          label: Text(l10n.get('clinic')),
                          icon: const Icon(Icons.local_hospital_outlined),
                        ),
                        ButtonSegment(
                          value: ConsultationType.online,
                          label: Text(l10n.get('online')),
                          icon: const Icon(Icons.videocam_outlined),
                        ),
                      ],
                      selected: {_consultationType},
                      onSelectionChanged: (value) {
                        setState(() => _consultationType = value.first);
                      },
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: _problemController,
                      minLines: 3,
                      maxLines: 5,
                      decoration: InputDecoration(
                        labelText: l10n.get('problemDescription'),
                      ),
                    ),
                  ],
                ),
              ),
              if (bookingState.error != null) ...[
                const SizedBox(height: 12),
                Text(
                  _friendlyBookingError(context, bookingState.error!.message),
                  style: const TextStyle(color: AppColors.danger),
                ),
              ],
              if (bookingState.appointment != null) ...[
                const SizedBox(height: 16),
                _BookingResultCard(appointment: bookingState.appointment!),
              ],
              const SizedBox(height: 24),
              AppButton(
                label: l10n.get('confirmBooking'),
                isLoading: bookingState.isLoading,
                onPressed:
                    _selectedSlot == null ||
                        doctor.doctorProfileId == null ||
                        bookingState.isLoading
                    ? null
                    : () => _book(doctor),
              ),
              const SizedBox(height: 8),
              Text(
                uxCopy(
                  context,
                  'لن نرسل سعر أو حالة حجز من التطبيق؛ السيرفر هو مصدر التأكيد.',
                  'The backend confirms price and booking state.',
                ),
                textAlign: TextAlign.center,
                style: Theme.of(
                  context,
                ).textTheme.bodySmall?.copyWith(color: AppColors.muted),
              ),
            ],
          ),
          failure: (failure) => ErrorView(message: failure.error.message),
        ),
      ),
    );
  }

  Future<void> _book(Doctor doctor) async {
    final slot = _selectedSlot;
    final doctorProfileId = doctor.doctorProfileId;
    if (slot == null || doctorProfileId == null) return;

    final appointment = await ref
        .read(appointmentBookingControllerProvider.notifier)
        .book(
          BookAppointmentRequest(
            doctorProfileId: doctorProfileId,
            appointmentSlotId: slot.id,
            consultationType: _consultationType,
            problemDescription: _problemController.text,
          ),
        );

    if (!mounted) return;

    if (appointment == null) {
      ref.invalidate(doctorSlotsProvider(widget.doctorId));
      return;
    }

    if (appointment.isPendingPayment) {
      final paymentId = appointment.paymentId;
      if (paymentId == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              AppLocalizations.of(context).get('bookingPaymentMissing'),
            ),
          ),
        );
        return;
      }

      context.go(RouteNames.payment(paymentId, appointmentId: appointment.id));
      return;
    }

    if (appointment.isConfirmed) {
      context.go(RouteNames.appointmentResult(appointment.id));
    }
  }

  String _friendlyBookingError(BuildContext context, String message) {
    final lower = message.toLowerCase();
    if (lower.contains('slot') || lower.contains('booked')) {
      return AppLocalizations.of(context).get('slotNoLongerAvailable');
    }
    return message;
  }
}

class _DoctorSummary extends StatelessWidget {
  const _DoctorSummary({required this.doctor});

  final Doctor doctor;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return SoftMedicalCard(
      child: Row(
        children: [
          Container(
            width: 50,
            height: 50,
            decoration: BoxDecoration(
              color: AppColors.primary.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(16),
            ),
            child: const Icon(Icons.person, color: AppColors.primary),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  doctor.name,
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  doctor.consultationFee == null
                      ? doctor.specialties.join('، ')
                      : '${doctor.specialties.join('، ')}\n${l10n.get('fee')}: ${doctor.consultationFee} EGP',
                  style: Theme.of(
                    context,
                  ).textTheme.bodySmall?.copyWith(color: AppColors.muted),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _BookingStepper extends StatelessWidget {
  const _BookingStepper({required this.currentStep});

  final int currentStep;

  @override
  Widget build(BuildContext context) {
    final steps = [
      uxCopy(context, 'الموعد', 'Slot'),
      uxCopy(context, 'البيانات', 'Details'),
      uxCopy(context, 'الدفع', 'Payment'),
      uxCopy(context, 'التأكيد', 'Confirm'),
    ];

    return SoftMedicalCard(
      padding: const EdgeInsets.all(14),
      child: Row(
        children: [
          for (var i = 0; i < steps.length; i++) ...[
            Expanded(
              child: Column(
                children: [
                  Container(
                    width: 28,
                    height: 28,
                    decoration: BoxDecoration(
                      color: i <= currentStep
                          ? AppColors.primary
                          : AppColors.primary.withValues(alpha: 0.1),
                      shape: BoxShape.circle,
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      '${i + 1}',
                      style: TextStyle(
                        color: i <= currentStep
                            ? Colors.white
                            : AppColors.primary,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    steps[i],
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: Theme.of(context).textTheme.labelSmall?.copyWith(
                      fontWeight: FontWeight.w700,
                      color: i <= currentStep
                          ? AppColors.primary
                          : AppColors.muted,
                    ),
                  ),
                ],
              ),
            ),
            if (i != steps.length - 1)
              Container(
                width: 12,
                height: 1,
                color: i < currentStep
                    ? AppColors.primary
                    : AppColors.primary.withValues(alpha: 0.16),
              ),
          ],
        ],
      ),
    );
  }
}

class _BookingResultCard extends StatelessWidget {
  const _BookingResultCard({required this.appointment});

  final Appointment appointment;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final pending = appointment.isPendingPayment;
    return SoftMedicalCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                pending ? Icons.payments_outlined : Icons.check_circle_outline,
                color: pending ? AppColors.warning : AppColors.success,
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  pending
                      ? l10n.get('bookingPendingPayment')
                      : l10n.get('bookingConfirmed'),
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    color: pending ? AppColors.warning : AppColors.success,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text('${appointment.price} ${appointment.currency}'),
          if (pending && appointment.paymentId != null) ...[
            const SizedBox(height: 8),
            Text(
              uxCopy(
                context,
                'رقم عملية الدفع: ${appointment.paymentId}',
                'Payment reference: ${appointment.paymentId}',
              ),
            ),
            const SizedBox(height: 8),
            AppButton(
              label: l10n.get('goToPayment'),
              onPressed: () => context.go(
                RouteNames.payment(
                  appointment.paymentId!,
                  appointmentId: appointment.id,
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}
