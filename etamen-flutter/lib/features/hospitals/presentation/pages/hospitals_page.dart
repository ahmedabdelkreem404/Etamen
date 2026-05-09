import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/hospitals/presentation/providers/hospitals_providers.dart';
import 'package:etamen_app/features/hospitals/presentation/widgets/hospital_widgets.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class HospitalsPage extends ConsumerWidget {
  const HospitalsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(hospitalsControllerProvider);
    final controller = ref.read(hospitalsControllerProvider.notifier);

    return AppScaffold(
      title: uxCopy(context, 'المستشفيات', 'Hospitals'),
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
                  'ابحث عن مستشفى أو منطقة',
                  'Search hospital or area',
                ),
                prefixIcon: const Icon(Icons.search),
              ),
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
                  'لا توجد مستشفيات متاحة حاليًا. سيتم إضافة مستشفيات قريبًا.',
                  'No hospitals are available right now.',
                ),
                icon: Icons.local_hospital_outlined,
              )
            else
              ...state.filteredItems.map(
                (hospital) => HospitalCard(
                  hospital: hospital,
                  onTap: () =>
                      context.push(RouteNames.hospitalDetails(hospital.id)),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
