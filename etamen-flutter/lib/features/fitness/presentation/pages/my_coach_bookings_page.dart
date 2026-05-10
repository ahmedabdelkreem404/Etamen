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

class MyCoachBookingsPage extends ConsumerWidget {
  const MyCoachBookingsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(coachBookingsControllerProvider);
    final controller = ref.read(coachBookingsControllerProvider.notifier);

    return AppScaffold(
      title: uxCopy(context, 'حجوزات الكوتش', 'My coach bookings'),
      body: RefreshIndicator(
        onRefresh: controller.load,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            if (state.isLoading)
              const LoadingView()
            else if (state.error != null)
              ErrorView(message: state.error!.message, onRetry: controller.load)
            else if (state.isEmpty)
              EmptyView(
                message: uxCopy(
                  context,
                  'لا توجد حجوزات كوتش بعد.',
                  'No coach bookings yet.',
                ),
                icon: Icons.sports_handball_outlined,
              )
            else
              ...state.items.map((booking) => _CoachBookingCard(booking)),
          ],
        ),
      ),
    );
  }
}

class _CoachBookingCard extends StatelessWidget {
  const _CoachBookingCard(this.booking);

  final CoachBooking booking;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return SoftMedicalCard(
      margin: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  booking.bookingNumber ?? '#${booking.id}',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
              FitnessStatusChip(label: booking.status.friendlyLabel(isArabic)),
            ],
          ),
          const SizedBox(height: 8),
          Text(booking.summary(isArabic)),
          if (booking.availabilitySlot?.startsAt != null) ...[
            const SizedBox(height: 6),
            Text(dateTimeLabel(booking.availabilitySlot!.startsAt)),
          ],
          const SizedBox(height: 8),
          Text(fitnessMoney(booking.totalAmount, currency: booking.currency)),
          const SizedBox(height: 12),
          FilledButton.icon(
            onPressed: () =>
                context.push(RouteNames.coachBookingDetails(booking.id)),
            icon: const Icon(Icons.arrow_forward, size: 16),
            label: Text(uxCopy(context, 'التفاصيل', 'Details')),
          ),
        ],
      ),
    );
  }
}
