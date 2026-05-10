import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/fitness/domain/entities/fitness_entities.dart';
import 'package:etamen_app/features/fitness/presentation/providers/fitness_providers.dart';
import 'package:etamen_app/features/fitness/presentation/widgets/fitness_widgets.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class CoachBookingDetailsPage extends ConsumerWidget {
  const CoachBookingDetailsPage({required this.bookingId, super.key});

  final int bookingId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(coachBookingDetailsControllerProvider(bookingId));
    final controller = ref.read(
      coachBookingDetailsControllerProvider(bookingId).notifier,
    );

    return AppScaffold(
      title: uxCopy(context, 'تفاصيل حجز الكوتش', 'Coach booking'),
      body: RefreshIndicator(
        onRefresh: controller.load,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            if (state.isLoading && state.booking == null)
              const LoadingView()
            else if (state.error != null && state.booking == null)
              ErrorView(message: state.error!.message, onRetry: controller.load)
            else if (state.booking == null)
              EmptyView(
                message: uxCopy(
                  context,
                  'الحجز غير متاح.',
                  'Booking unavailable.',
                ),
                icon: Icons.sports_handball_outlined,
              )
            else
              _CoachBookingDetails(booking: state.booking!),
            if (state.error != null && state.booking != null) ...[
              const SizedBox(height: 12),
              Text(state.error!.message),
            ],
          ],
        ),
      ),
    );
  }
}

class _CoachBookingDetails extends StatelessWidget {
  const _CoachBookingDetails({required this.booking});

  final CoachBooking booking;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    final coachName = booking.coachProvider?.name(isArabic);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SoftMedicalCard(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(
                      booking.bookingNumber ?? '#${booking.id}',
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ),
                  FitnessStatusChip(
                    label: booking.status.friendlyLabel(isArabic),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              if (coachName != null)
                FitnessInfoLine(
                  label: uxCopy(context, 'الكوتش', 'Coach'),
                  value: coachName,
                ),
              FitnessInfoLine(
                label: uxCopy(context, 'الجلسة', 'Session'),
                value: booking.summary(isArabic),
              ),
              if (booking.availabilitySlot?.startsAt != null)
                FitnessInfoLine(
                  label: uxCopy(context, 'الموعد', 'Slot'),
                  value: dateTimeLabel(booking.availabilitySlot!.startsAt),
                ),
              if (booking.patientGoal?.trim().isNotEmpty == true)
                FitnessInfoLine(
                  label: uxCopy(context, 'الهدف', 'Goal'),
                  value: booking.patientGoal!,
                ),
            ],
          ),
        ),
        const SizedBox(height: 14),
        FitnessBookingPaymentCard(
          amount: fitnessMoney(booking.totalAmount, currency: booking.currency),
          statusLabel: booking.status.friendlyLabel(isArabic),
          paymentId: booking.paymentId,
          onPay: booking.paymentId == null
              ? null
              : () => context.push(
                  RouteNames.payment(
                    booking.paymentId!,
                    coachBookingId: booking.id,
                  ),
                ),
        ),
      ],
    );
  }
}
