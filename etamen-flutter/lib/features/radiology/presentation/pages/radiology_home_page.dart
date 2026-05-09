import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:etamen_app/features/radiology/presentation/providers/radiology_providers.dart';
import 'package:etamen_app/features/radiology/presentation/widgets/radiology_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class RadiologyHomePage extends ConsumerWidget {
  const RadiologyHomePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(radiologyCatalogControllerProvider);
    final controller = ref.read(radiologyCatalogControllerProvider.notifier);
    final cart = ref.watch(radiologyCartControllerProvider);
    final cartController = ref.read(radiologyCartControllerProvider.notifier);

    return AppScaffold(
      title: uxCopy(context, 'الأشعة', 'Radiology'),
      actions: [
        IconButton(
          tooltip: uxCopy(context, 'طلباتي', 'My orders'),
          onPressed: () => context.push(RouteNames.radiologyOrders),
          icon: const Icon(Icons.receipt_long_outlined),
        ),
      ],
      body: RefreshIndicator(
        onRefresh: controller.load,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            const RadiologyHeroCard(),
            const SizedBox(height: 14),
            RadiologyCartSummary(
              itemCount: cart.itemCount,
              total: cart.localTotal,
              onOpen: () => context.push(RouteNames.radiologyOrderBuilder),
            ),
            TextField(
              onChanged: controller.search,
              decoration: InputDecoration(
                hintText: uxCopy(
                  context,
                  'ابحث عن فحص أو مركز أشعة',
                  'Search scan or center',
                ),
                prefixIcon: const Icon(Icons.search),
              ),
            ),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: Text(
                    uxCopy(context, 'أنواع الفحوصات', 'Scan categories'),
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ),
                TextButton(
                  onPressed: () => controller.selectCategory(null),
                  child: Text(uxCopy(context, 'الكل', 'All')),
                ),
              ],
            ),
            const SizedBox(height: 8),
            SizedBox(
              height: 46,
              child: state.categories.isEmpty
                  ? Center(
                      child: Text(
                        uxCopy(
                          context,
                          'لا توجد تصنيفات أشعة متاحة الآن',
                          'No radiology categories are available now',
                        ),
                      ),
                    )
                  : ListView(
                      scrollDirection: Axis.horizontal,
                      children: [
                        for (final category in state.categories)
                          RadiologyCategoryChip(
                            category: category,
                            selected: state.selectedCategoryId == category.id,
                            onTap: () => controller.selectCategory(category.id),
                          ),
                      ],
                    ),
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: Text(
                    uxCopy(context, 'الفحوصات المتاحة', 'Available scans'),
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ),
                TextButton.icon(
                  onPressed: () => context.push(RouteNames.radiologyOrders),
                  icon: const Icon(Icons.history),
                  label: Text(uxCopy(context, 'طلباتي', 'Orders')),
                ),
              ],
            ),
            const SizedBox(height: 8),
            if (cart.lastMessage != null) ...[
              Text(
                cart.lastMessage!,
                style: const TextStyle(color: Colors.redAccent),
              ),
              const SizedBox(height: 8),
            ],
            if (state.isLoading)
              const LoadingView()
            else if (state.error != null)
              ErrorView(message: state.error!.message, onRetry: controller.load)
            else if (state.isEmpty)
              EmptyView(
                message: uxCopy(
                  context,
                  'لا توجد فحوصات أشعة متاحة حاليًا',
                  'No radiology scans are available now',
                ),
                icon: Icons.biotech_outlined,
              )
            else
              ...state.filteredScans.map(
                (scan) => RadiologyScanCard(
                  scan: scan,
                  isSelected: cart.items.any((item) => item.scanId == scan.id),
                  onAdd: () {
                    final added = cartController.addScan(scan);
                    if (!added) return;
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text(
                          uxCopy(
                            context,
                            'تمت إضافة الفحص للطلب',
                            'Scan added to order',
                          ),
                        ),
                      ),
                    );
                  },
                ),
              ),
            const SizedBox(height: 16),
            Text(
              uxCopy(
                context,
                'تنبيه: لا يفسر اطمن نتائج الأشعة ولا يقدم تشخيصًا. راجع الطبيب أو مركز الأشعة لفهم النتيجة.',
                'Note: Etamen does not interpret radiology results or diagnose. Review results with your doctor or center.',
              ),
              style: Theme.of(context).textTheme.bodySmall,
            ),
          ],
        ),
      ),
      floatingActionButton: cart.itemCount > 0
          ? FloatingActionButton.extended(
              onPressed: () => context.push(RouteNames.radiologyOrderBuilder),
              icon: const Icon(Icons.shopping_bag_outlined),
              label: Text('${cart.itemCount}'),
            )
          : null,
    );
  }
}
