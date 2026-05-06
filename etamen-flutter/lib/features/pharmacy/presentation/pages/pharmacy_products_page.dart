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
