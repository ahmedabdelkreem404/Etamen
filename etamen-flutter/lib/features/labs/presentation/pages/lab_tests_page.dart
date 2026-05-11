import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_cart_item.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_package.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_test.dart';
import 'package:etamen_app/features/labs/presentation/providers/labs_providers.dart';
import 'package:etamen_app/features/labs/presentation/widgets/lab_package_card.dart';
import 'package:etamen_app/features/labs/presentation/widgets/lab_test_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class LabTestsPage extends ConsumerWidget {
  const LabTestsPage({required this.labId, super.key});

  final int labId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(labTestsControllerProvider(labId));
    final controller = ref.read(labTestsControllerProvider(labId).notifier);
    final cart = ref.watch(labCartControllerProvider);

    return DefaultTabController(
      length: 2,
      child: AppScaffold(
        title: l10n.get('tests'),
        floatingActionButton: cart.itemCount == 0
            ? null
            : FloatingActionButton.extended(
                onPressed: () => context.push(RouteNames.labCart),
                icon: const Icon(Icons.science_outlined),
                label: Text('${l10n.get('labOrderCart')} (${cart.itemCount})'),
              ),
        body: Column(
          children: [
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  TextField(
                    onChanged: controller.search,
                    decoration: InputDecoration(
                      hintText: l10n.get('searchLabTest'),
                      prefixIcon: const Icon(Icons.search),
                    ),
                  ),
                  const SizedBox(height: 12),
                  _LabCatalogControls(
                    filter: state.selectedFilter,
                    sort: state.selectedSort,
                    onFilter: controller.selectFilter,
                    onSort: controller.selectSort,
                  ),
                  if (cart.itemCount > 0) ...[
                    const SizedBox(height: 12),
                    _LabSelectedItemsSummary(
                      itemCount: cart.itemCount,
                      total: cart.localSubtotal,
                    ),
                  ],
                  const SizedBox(height: 8),
                  Align(
                    alignment: AlignmentDirectional.centerStart,
                    child: Text(
                      l10n.isArabic
                          ? 'النتائج لا يتم تفسيرها طبيًا داخل التطبيق.'
                          : 'Results are not medically interpreted in the app.',
                      style: Theme.of(context).textTheme.bodySmall,
                    ),
                  ),
                ],
              ),
            ),
            TabBar(
              tabs: [
                Tab(text: l10n.get('tests')),
                Tab(text: l10n.get('packages')),
              ],
            ),
            Expanded(
              child: RefreshIndicator(
                onRefresh: controller.load,
                child: state.isLoading
                    ? const LoadingView()
                    : state.error != null
                    ? ErrorView(
                        message: state.error!.message,
                        onRetry: controller.load,
                      )
                    : TabBarView(
                        children: [
                          _TestsList(labId: labId, tests: state.filteredTests),
                          _PackagesList(
                            labId: labId,
                            packages: state.filteredPackages,
                          ),
                        ],
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _LabCatalogControls extends StatelessWidget {
  const _LabCatalogControls({
    required this.filter,
    required this.sort,
    required this.onFilter,
    required this.onSort,
  });

  final LabCatalogFilter filter;
  final LabCatalogSort sort;
  final ValueChanged<LabCatalogFilter> onFilter;
  final ValueChanged<LabCatalogSort> onSort;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: LabCatalogFilter.values
                .map(
                  (value) => Padding(
                    padding: const EdgeInsetsDirectional.only(end: 8),
                    child: FilterChip(
                      selected: filter == value,
                      label: Text(_filterLabel(value, isArabic)),
                      onSelected: (_) => onFilter(value),
                    ),
                  ),
                )
                .toList(growable: false),
          ),
        ),
        const SizedBox(height: 8),
        DropdownButtonFormField<LabCatalogSort>(
          value: sort,
          decoration: InputDecoration(
            labelText: isArabic ? 'ترتيب النتائج' : 'Sort results',
          ),
          items: LabCatalogSort.values
              .map(
                (value) => DropdownMenuItem(
                  value: value,
                  child: Text(_sortLabel(value, isArabic)),
                ),
              )
              .toList(growable: false),
          onChanged: (value) {
            if (value != null) onSort(value);
          },
        ),
      ],
    );
  }

  String _filterLabel(LabCatalogFilter value, bool isArabic) {
    return switch (value) {
      LabCatalogFilter.all => isArabic ? 'الكل' : 'All',
      LabCatalogFilter.tests => isArabic ? 'تحاليل' : 'Tests',
      LabCatalogFilter.packages => isArabic ? 'باقات' : 'Packages',
      LabCatalogFilter.quick => isArabic ? 'نتيجة سريعة' : 'Fast result',
    };
  }

  String _sortLabel(LabCatalogSort value, bool isArabic) {
    return switch (value) {
      LabCatalogSort.newest => isArabic ? 'الأحدث' : 'Newest',
      LabCatalogSort.priceAsc => isArabic ? 'السعر الأقل' : 'Lowest price',
      LabCatalogSort.priceDesc => isArabic ? 'السعر الأعلى' : 'Highest price',
      LabCatalogSort.name => isArabic ? 'الاسم' : 'Name',
      LabCatalogSort.resultTime => isArabic ? 'وقت النتيجة' : 'Result time',
    };
  }
}

class _LabSelectedItemsSummary extends StatelessWidget {
  const _LabSelectedItemsSummary({
    required this.itemCount,
    required this.total,
  });

  final int itemCount;
  final double total;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return Card(
      child: ListTile(
        leading: const Icon(Icons.science_outlined),
        title: Text(
          isArabic
              ? 'العناصر المختارة: $itemCount'
              : 'Selected items: $itemCount',
        ),
        subtitle: Text(
          isArabic
              ? 'الإجمالي النهائي يتم حسابه من السيرفر.'
              : 'Final total is calculated by the server.',
        ),
        trailing: Text('${total.toStringAsFixed(0)} EGP'),
      ),
    );
  }
}

class _TestsList extends ConsumerWidget {
  const _TestsList({required this.labId, required this.tests});

  final int labId;
  final List<LabTest> tests;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final cart = ref.watch(labCartControllerProvider);
    if (tests.isEmpty) {
      return EmptyView(message: l10n.get('noTests'), icon: Icons.science);
    }
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
      children: tests
          .map(
            (test) => LabTestCard(
              test: test,
              quantity: _quantity(cart.items, LabCartItemType.test, test.id),
              onAdd: () => _addTest(context, ref, test),
              onIncrease: () => _addTest(context, ref, test),
              onDecrease: () => ref
                  .read(labCartControllerProvider.notifier)
                  .updateQuantity(
                    LabCartItemType.test,
                    test.id,
                    _quantity(cart.items, LabCartItemType.test, test.id) - 1,
                  ),
            ),
          )
          .toList(growable: false),
    );
  }

  int _quantity(List<LabCartItem> items, LabCartItemType type, int id) {
    for (final item in items) {
      if (item.type == type && item.itemId == id) return item.quantity;
    }
    return 0;
  }

  Future<void> _addTest(
    BuildContext context,
    WidgetRef ref,
    LabTest test,
  ) async {
    final controller = ref.read(labCartControllerProvider.notifier);
    final added = controller.addTest(test, labId: labId);
    if (added || !context.mounted) return;
    if (await _confirmClear(context)) {
      controller.addTest(test, labId: labId, clearExisting: true);
    }
  }
}

class _PackagesList extends ConsumerWidget {
  const _PackagesList({required this.labId, required this.packages});

  final int labId;
  final List<LabPackage> packages;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final cart = ref.watch(labCartControllerProvider);
    if (packages.isEmpty) {
      return EmptyView(message: l10n.get('noPackages'), icon: Icons.inventory);
    }
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
      children: packages
          .map(
            (package) => LabPackageCard(
              package: package,
              quantity: _quantity(
                cart.items,
                LabCartItemType.package,
                package.id,
              ),
              onAdd: () => _addPackage(context, ref, package),
              onIncrease: () => _addPackage(context, ref, package),
              onDecrease: () => ref
                  .read(labCartControllerProvider.notifier)
                  .updateQuantity(
                    LabCartItemType.package,
                    package.id,
                    _quantity(cart.items, LabCartItemType.package, package.id) -
                        1,
                  ),
            ),
          )
          .toList(growable: false),
    );
  }

  int _quantity(List<LabCartItem> items, LabCartItemType type, int id) {
    for (final item in items) {
      if (item.type == type && item.itemId == id) return item.quantity;
    }
    return 0;
  }

  Future<void> _addPackage(
    BuildContext context,
    WidgetRef ref,
    LabPackage package,
  ) async {
    final controller = ref.read(labCartControllerProvider.notifier);
    final added = controller.addPackage(package, labId: labId);
    if (added || !context.mounted) return;
    if (await _confirmClear(context)) {
      controller.addPackage(package, labId: labId, clearExisting: true);
    }
  }
}

Future<bool> _confirmClear(BuildContext context) async {
  final l10n = AppLocalizations.of(context);
  final shouldClear = await showDialog<bool>(
    context: context,
    builder: (dialogContext) => AlertDialog(
      title: Text(l10n.get('clearCart')),
      content: Text(l10n.get('oneLabCartNotice')),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(dialogContext).pop(false),
          child: Text(l10n.get('back')),
        ),
        FilledButton(
          onPressed: () => Navigator.of(dialogContext).pop(true),
          child: Text(l10n.get('clearCart')),
        ),
      ],
    ),
  );
  return shouldClear == true;
}
