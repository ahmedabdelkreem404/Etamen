import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/appointments/data/models/book_appointment_request.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment.dart';
import 'package:etamen_app/features/appointments/domain/entities/hospital_booking_context.dart';
import 'package:etamen_app/features/appointments/presentation/providers/appointment_booking_controller.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor_slot.dart';
import 'package:etamen_app/features/doctors/presentation/providers/doctors_providers.dart';
import 'package:etamen_app/features/doctors/presentation/widgets/doctor_card.dart';
import 'package:etamen_app/features/doctors/presentation/widgets/slot_picker.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

class AppointmentBookingPage extends ConsumerStatefulWidget {
  const AppointmentBookingPage({
    required this.doctorId,
    this.hospitalContext,
    super.key,
  });

  final int doctorId;
  final HospitalBookingContext? hospitalContext;

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
          success: (doctor) => Column(
            children: [
              Expanded(
                child: ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    _BookingStepper(currentStep: _selectedSlot == null ? 0 : 1),
                    if (widget.hospitalContext != null) ...[
                      const SizedBox(height: 16),
                      _HospitalBookingContextCardFixed(
                        contextHint: widget.hospitalContext!,
                      ),
                    ],
                    const SizedBox(height: 16),
                    _DoctorSummary(doctor: doctor),
                    const SizedBox(height: 16),
                    SoftMedicalCard(
                      child: slots.when(
                        loading: () => const LoadingView(),
                        error: (error, _) =>
                            ErrorView(message: error.toString()),
                        data: (slotResult) => slotResult.when(
                          success: (items) => SlotPicker(
                            slots: items
                                .where((slot) => slot.isAvailable)
                                .toList(),
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
                            uxCopy(
                              context,
                              'أكد بيانات الحجز',
                              'Confirm details',
                            ),
                            style: Theme.of(context).textTheme.titleMedium
                                ?.copyWith(fontWeight: FontWeight.w800),
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
                        _friendlyBookingError(
                          context,
                          bookingState.error!.message,
                        ),
                        style: const TextStyle(color: AppColors.danger),
                      ),
                    ],
                    if (bookingState.appointment != null) ...[
                      const SizedBox(height: 16),
                      _BookingResultCard(
                        appointment: bookingState.appointment!,
                      ),
                    ],
                    const SizedBox(height: 12),
                  ],
                ),
              ),
              _BookingActionBar(
                selectedSlot: _selectedSlot,
                isLoading: bookingState.isLoading,
                canSubmit:
                    _selectedSlot != null && doctor.doctorProfileId != null,
                onSubmit: () => _book(doctor),
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
            hospitalContext: widget.hospitalContext,
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

// ignore: unused_element
class _HospitalBookingContextCard extends StatelessWidget {
  const _HospitalBookingContextCard({required this.contextHint});

  final HospitalBookingContext contextHint;

  @override
  Widget build(BuildContext context) {
    final label = [
      contextHint.hospitalName,
      contextHint.departmentName == null
          ? null
          : uxCopy(
              context,
              'قسم ${contextHint.departmentName}',
              '${contextHint.departmentName} department',
            ),
    ].whereType<String>().join(' - ');

    return SoftMedicalCard(
      padding: const EdgeInsets.all(14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.apartment_outlined, color: AppColors.primaryDark),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  uxCopy(
                    context,
                    'سيتم تسجيل الحجز على المستشفى بعد تحقق الخادم.',
                    'The server will validate and attach this hospital context.',
                  ),
                  style: Theme.of(context).textTheme.titleSmall?.copyWith(
                    fontWeight: FontWeight.w900,
                    color: AppColors.primaryDark,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  label.isEmpty
                      ? uxCopy(
                          context,
                          'حجز من خلال مستشفى',
                          'Hospital booking',
                        )
                      : label,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: AppColors.softText,
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

class _BookingActionBar extends StatelessWidget {
  const _BookingActionBar({
    required this.selectedSlot,
    required this.isLoading,
    required this.canSubmit,
    required this.onSubmit,
  });

  final DoctorSlot? selectedSlot;
  final bool isLoading;
  final bool canSubmit;
  final VoidCallback onSubmit;

  @override
  Widget build(BuildContext context) {
    final slot = selectedSlot;
    final summary = slot == null
        ? uxCopy(context, 'اختر اليوم والموعد أولًا', 'Choose a day and time')
        : DateFormat('EEE d MMM - HH:mm').format(slot.startsAt.toLocal());

    return SafeArea(
      top: false,
      child: Container(
        padding: const EdgeInsets.fromLTRB(16, 10, 16, 12),
        decoration: BoxDecoration(
          color: Colors.white,
          border: Border(top: BorderSide(color: AppColors.softBorder)),
          boxShadow: [
            BoxShadow(
              color: AppColors.primaryDark.withValues(alpha: 0.12),
              blurRadius: 18,
              offset: const Offset(0, -8),
            ),
          ],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Row(
              children: [
                const Icon(
                  Icons.event_available_outlined,
                  color: AppColors.medicalAccent,
                  size: 18,
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    summary,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: Theme.of(context).textTheme.labelLarge?.copyWith(
                      color: slot == null ? AppColors.muted : AppColors.text,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            AppButton(
              label: AppLocalizations.of(context).get('confirmBooking'),
              isLoading: isLoading,
              onPressed: canSubmit && !isLoading ? onSubmit : null,
            ),
            const SizedBox(height: 6),
            Text(
              uxCopy(
                context,
                'السعر وحالة الحجز يتم تأكيدهما من الخادم.',
                'Price and booking state are confirmed by the server.',
              ),
              textAlign: TextAlign.center,
              style: Theme.of(
                context,
              ).textTheme.bodySmall?.copyWith(color: AppColors.muted),
            ),
          ],
        ),
      ),
    );
  }
}

class _HospitalBookingContextCardFixed extends StatelessWidget {
  const _HospitalBookingContextCardFixed({required this.contextHint});

  final HospitalBookingContext contextHint;

  @override
  Widget build(BuildContext context) {
    final label = [
      contextHint.hospitalName,
      contextHint.departmentName == null
          ? null
          : uxCopy(
              context,
              '\u0642\u0633\u0645 ${contextHint.departmentName}',
              '${contextHint.departmentName} department',
            ),
    ].whereType<String>().join(' - ');

    return SoftMedicalCard(
      padding: const EdgeInsets.all(14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.apartment_outlined, color: AppColors.primaryDark),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  uxCopy(
                    context,
                    '\u0627\u0644\u062E\u0627\u062F\u0645 \u064A\u062A\u062D\u0642\u0642 \u0645\u0646 \u0628\u064A\u0627\u0646\u0627\u062A \u0627\u0644\u0645\u0633\u062A\u0634\u0641\u0649 \u0642\u0628\u0644 \u0627\u0644\u062D\u062C\u0632',
                    'The server validates hospital context before booking.',
                  ),
                  style: Theme.of(context).textTheme.titleSmall?.copyWith(
                    fontWeight: FontWeight.w900,
                    color: AppColors.primaryDark,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  label.isEmpty
                      ? uxCopy(
                          context,
                          '\u062D\u062C\u0632 \u0645\u0646 \u062E\u0644\u0627\u0644 \u0645\u0633\u062A\u0634\u0641\u0649',
                          'Hospital booking',
                        )
                      : label,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: AppColors.softText,
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

class _DoctorSummary extends StatelessWidget {
  const _DoctorSummary({required this.doctor});

  final Doctor doctor;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final specialty = doctor.specialties.isEmpty
        ? uxCopy(context, 'تخصص غير محدد', 'Specialty pending')
        : doctor.specialties.join('، ');
    final branch = doctor.branches.isEmpty
        ? uxCopy(context, 'المكان يضاف قريبًا', 'Location soon')
        : doctor.branches.first;
    return SoftMedicalCard(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          DoctorAvatar(name: doctor.name, imageUrl: doctor.avatarUrl, size: 56),
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
                Wrap(
                  spacing: 8,
                  runSpacing: 6,
                  children: [
                    _SummaryChip(
                      icon: Icons.medical_services_outlined,
                      label: specialty,
                    ),
                    _SummaryChip(
                      icon: Icons.location_on_outlined,
                      label: branch,
                    ),
                    if (doctor.consultationFee != null)
                      _SummaryChip(
                        icon: Icons.payments_outlined,
                        label:
                            '${l10n.get('fee')}: ${doctor.consultationFee} EGP',
                      ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _SummaryChip extends StatelessWidget {
  const _SummaryChip({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
      decoration: BoxDecoration(
        color: AppColors.medicalMint,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: AppColors.primary),
          const SizedBox(width: 4),
          ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 180),
            child: Text(
              label,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: Theme.of(context).textTheme.labelSmall?.copyWith(
                color: AppColors.primaryDark,
                fontWeight: FontWeight.w800,
              ),
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
