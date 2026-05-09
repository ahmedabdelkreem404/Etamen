import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_order.dart';
import 'package:etamen_app/features/radiology/presentation/providers/radiology_providers.dart';
import 'package:etamen_app/features/radiology/presentation/widgets/radiology_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class MyRadiologyOrdersPage extends ConsumerWidget {
  const MyRadiologyOrdersPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(radiologyOrdersControllerProvider);
    final controller = ref.read(radiologyOrdersControllerProvider.notifier);

    return AppScaffold(
      title: uxCopy(context, 'طلبات الأشعة', 'Radiology orders'),
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
                  'لا توجد طلبات أشعة حتى الآن',
                  'No radiology orders yet',
                ),
                icon: Icons.biotech_outlined,
              )
            else
              ...state.items.map(
                (order) => _RadiologyOrderCard(
                  order: order,
                  onTap: () =>
                      context.push(RouteNames.radiologyOrderDetails(order.id)),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class _RadiologyOrderCard extends StatelessWidget {
  const _RadiologyOrderCard({required this.order, required this.onTap});

  final RadiologyOrder order;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final provider = order.provider?.name(
      Localizations.localeOf(context).languageCode == 'ar',
    );
    return SoftMedicalCard(
      margin: const EdgeInsets.only(bottom: 12),
      onTap: onTap,
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  order.orderNumber ?? '#${order.id}',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
              RadiologyStatusChip(status: order.status),
            ],
          ),
          const SizedBox(height: 8),
          Text(provider ?? uxCopy(context, 'مركز أشعة', 'Radiology center')),
          const SizedBox(height: 4),
          Text(
            uxCopy(
              context,
              '${order.items.length} فحص - ${order.totalAmount ?? '-'} ${order.currency}',
              '${order.items.length} scan(s) - ${order.totalAmount ?? '-'} ${order.currency}',
            ),
          ),
        ],
      ),
    );
  }
}
