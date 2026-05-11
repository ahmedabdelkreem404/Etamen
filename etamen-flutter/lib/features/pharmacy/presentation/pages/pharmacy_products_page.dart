import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_cart_item.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_product.dart';
import 'package:etamen_app/features/pharmacy/presentation/providers/pharmacy_providers.dart';
import 'package:etamen_app/features/pharmacy/presentation/widgets/pharmacy_product_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class PharmacyProductsPage extends ConsumerWidget {
  const PharmacyProductsPage({required this.pharmacyId, super.key});

  final int pharmacyId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(pharmacyProductsControllerProvider(pharmacyId));
    final controller = ref.read(
      pharmacyProductsControllerProvider(pharmacyId).notifier,
    );
    final cart = ref.watch(pharmacyCartControllerProvider);

    return AppScaffold(
      title: l10n.get('products'),
      floatingActionButton: cart.itemCount == 0
          ? null
          : FloatingActionButton.extended(
              onPressed: () => context.push(RouteNames.pharmacyCart),
              icon: const Icon(Icons.shopping_basket_outlined),
              label: Text('${l10n.get('cart')} (${cart.itemCount})'),
            ),
      body: RefreshIndicator(
        onRefresh: controller.load,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            TextField(
              onChanged: controller.search,
              decoration: InputDecoration(
                hintText: l10n.get('searchMedicine'),
                prefixIcon: const Icon(Icons.search),
              ),
            ),
            const SizedBox(height: 12),
            _PharmacyCatalogControls(
              filter: state.selectedFilter,
              sort: state.selectedSort,
              onFilter: controller.selectFilter,
              onSort: controller.selectSort,
            ),
            if (cart.itemCount > 0) ...[
              const SizedBox(height: 12),
              _SelectedItemsSummary(
                itemCount: cart.itemCount,
                total: cart.localSubtotal,
              ),
            ],
            const SizedBox(height: 12),
            if (state.isLoading)
              const LoadingView()
            else if (state.error != null)
              ErrorView(message: state.error!.message, onRetry: controller.load)
            else if (state.isEmpty)
              EmptyView(
                message: l10n.get('noProducts'),
                icon: Icons.medication_outlined,
              )
            else
              ...state.filteredItems.map(
                (product) => PharmacyProductCard(
                  product: product,
                  quantity: _quantity(cart.items, product),
                  onAdd: () => _addProduct(context, ref, product),
                  onIncrease: () => _addProduct(context, ref, product),
                  onDecrease: () => ref
                      .read(pharmacyCartControllerProvider.notifier)
                      .updateQuantity(
                        product.id,
                        _quantity(cart.items, product) - 1,
                      ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  int _quantity(List<PharmacyCartItem> items, PharmacyProduct product) {
    for (final item in items) {
      if (item.product.id == product.id) return item.quantity;
    }
    return 0;
  }

  Future<void> _addProduct(
    BuildContext context,
    WidgetRef ref,
    PharmacyProduct product,
  ) async {
    final cart = ref.read(pharmacyCartControllerProvider.notifier);
    final added = cart.addProduct(product, pharmacyId: pharmacyId);
    if (added || !context.mounted) return;

    final l10n = AppLocalizations.of(context);
    final shouldClear = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: Text(l10n.get('clearCart')),
        content: Text(l10n.get('onePharmacyCartNotice')),
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

    if (shouldClear == true) {
      cart.addProduct(product, pharmacyId: pharmacyId, clearExisting: true);
    }
  }
}

class _PharmacyCatalogControls extends StatelessWidget {
  const _PharmacyCatalogControls({
    required this.filter,
    required this.sort,
    required this.onFilter,
    required this.onSort,
  });

  final PharmacyCatalogFilter filter;
  final PharmacyCatalogSort sort;
  final ValueChanged<PharmacyCatalogFilter> onFilter;
  final ValueChanged<PharmacyCatalogSort> onSort;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: PharmacyCatalogFilter.values
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
        DropdownButtonFormField<PharmacyCatalogSort>(
          value: sort,
          decoration: InputDecoration(
            labelText: isArabic ? 'ترتيب النتائج' : 'Sort results',
          ),
          items: PharmacyCatalogSort.values
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

  String _filterLabel(PharmacyCatalogFilter value, bool isArabic) {
    return switch (value) {
      PharmacyCatalogFilter.all => isArabic ? 'الكل' : 'All',
      PharmacyCatalogFilter.inStock => isArabic ? 'متاح' : 'In stock',
      PharmacyCatalogFilter.prescription =>
        isArabic ? 'يحتاج روشتة' : 'Prescription',
      PharmacyCatalogFilter.nonPrescription =>
        isArabic ? 'لا يحتاج روشتة' : 'No prescription',
    };
  }

  String _sortLabel(PharmacyCatalogSort value, bool isArabic) {
    return switch (value) {
      PharmacyCatalogSort.newest => isArabic ? 'الأحدث' : 'Newest',
      PharmacyCatalogSort.priceAsc => isArabic ? 'السعر الأقل' : 'Lowest price',
      PharmacyCatalogSort.priceDesc =>
        isArabic ? 'السعر الأعلى' : 'Highest price',
      PharmacyCatalogSort.name => isArabic ? 'الاسم' : 'Name',
    };
  }
}

class _SelectedItemsSummary extends StatelessWidget {
  const _SelectedItemsSummary({required this.itemCount, required this.total});

  final int itemCount;
  final double total;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return Card(
      child: ListTile(
        leading: const Icon(Icons.shopping_basket_outlined),
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
