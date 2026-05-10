import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/fitness/presentation/providers/fitness_providers.dart';
import 'package:etamen_app/features/fitness/presentation/widgets/fitness_widgets.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class GymsPage extends ConsumerWidget {
  const GymsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(gymsControllerProvider);
    final controller = ref.read(gymsControllerProvider.notifier);

    return AppScaffold(
      title: uxCopy(context, 'الجيمات', 'Gyms'),
      body: RefreshIndicator(
        onRefresh: controller.load,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            TextField(
              onChanged: controller.search,
              decoration: InputDecoration(
                hintText: uxCopy(
                  context,
                  'ابحث عن جيم أو منطقة',
                  'Search gym or area',
                ),
                prefixIcon: const Icon(Icons.search),
              ),
            ),
            const SizedBox(height: 12),
            OutlinedButton.icon(
              onPressed: () => context.push(RouteNames.gymBookings),
              icon: const Icon(Icons.receipt_long_outlined),
              label: Text(uxCopy(context, 'حجوزات الجيم', 'My gym bookings')),
            ),
            const SizedBox(height: 14),
            if (state.isLoading)
              const LoadingView()
            else if (state.error != null)
              ErrorView(message: state.error!.message, onRetry: controller.load)
            else if (state.isEmpty)
              EmptyView(
                message: uxCopy(
                  context,
                  'لا توجد جيمات متاحة حاليًا.',
                  'No gyms are available right now.',
                ),
                icon: Icons.fitness_center_outlined,
              )
            else
              ...state.filteredItems.map(
                (gym) => GymCard(
                  gym: gym,
                  onTap: () => context.push(RouteNames.gymDetails(gym.id)),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
