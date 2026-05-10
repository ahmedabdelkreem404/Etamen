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

class GymBookingDetailsPage extends ConsumerWidget {
  const GymBookingDetailsPage({required this.bookingId, super.key});

  final int bookingId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(gymBookingDetailsControllerProvider(bookingId));
    final controller = ref.read(
      gymBookingDetailsControllerProvider(bookingId).notifier,
    );

    return AppScaffold(
      title: uxCopy(context, 'تفاصيل حجز الجيم', 'Gym booking'),
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
                icon: Icons.fitness_center_outlined,
              )
            else
              _GymBookingDetails(booking: state.booking!),
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

class _GymBookingDetails extends StatelessWidget {
  const _GymBookingDetails({required this.booking});

  final GymBooking booking;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    final providerName = booking.provider?.name(isArabic);
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
              if (providerName != null)
                FitnessInfoLine(
                  label: uxCopy(context, 'الجيم', 'Gym'),
                  value: providerName,
                ),
              FitnessInfoLine(
                label: uxCopy(context, 'الحجز', 'Booking'),
                value: booking.summary(isArabic),
              ),
              if (booking.startsAt != null)
                FitnessInfoLine(
                  label: uxCopy(context, 'البداية', 'Starts'),
                  value: dateTimeLabel(booking.startsAt),
                ),
              if (booking.notes?.trim().isNotEmpty == true)
                FitnessInfoLine(
                  label: uxCopy(context, 'ملاحظات', 'Notes'),
                  value: booking.notes!,
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
                    gymBookingId: booking.id,
                  ),
                ),
        ),
      ],
    );
  }
}
