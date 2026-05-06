import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/features/pharmacy/data/models/create_pharmacy_order_request.dart';
import 'package:etamen_app/features/pharmacy/presentation/providers/pharmacy_providers.dart';
import 'package:etamen_app/features/pharmacy/presentation/widgets/pharmacy_cart_item_tile.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class PharmacyCartPage extends ConsumerWidget {
  const PharmacyCartPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final cart = ref.watch(pharmacyCartControllerProvider);
    final createState = ref.watch(createPharmacyOrderControllerProvider);
    final cartController = ref.read(pharmacyCartControllerProvider.notifier);

    return AppScaffold(
      title: l10n.get('cart'),
      body: cart.isEmpty
          ? EmptyView(
              message: l10n.get('cartEmpty'),
              icon: Icons.shopping_basket_outlined,
            )
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                ...cart.items.map(
                  (item) => PharmacyCartItemTile(
                    item: item,
                    onIncrease: () => cartController.updateQuantity(
                      item.product.id,
                      item.quantity + 1,
                    ),
                    onDecrease: () => cartController.updateQuantity(
                      item.product.id,
                      item.quantity - 1,
                    ),
                    onRemove: () => cartController.remove(item.product.id),
                  ),
                ),
                const SizedBox(height: 12),
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          l10n.get('approximateTotal'),
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                        const SizedBox(height: 8),
                        Text(
                          '${cart.localSubtotal.toStringAsFixed(2)} EGP',
                          style: Theme.of(context).textTheme.headlineSmall,
                        ),
                        const SizedBox(height: 8),
                        Text(
                          l10n.get('pharmacyConfirmsTotal'),
                          style: const TextStyle(color: AppColors.muted),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                Card(
                  child: ListTile(
                    leading: const Icon(Icons.description_outlined),
                    title: Text(l10n.get('uploadPrescription')),
                    subtitle: Text(
                      cart.prescription == null
                          ? l10n.get('prescriptionPrivate')
                          : l10n.get('prescriptionUploaded'),
                    ),
                    trailing: FilledButton.tonal(
                      onPressed: () =>
                          context.push(RouteNames.pharmacyPrescriptionUpload),
                      child: Text(l10n.get('uploadPrescription')),
                    ),
                  ),
                ),
                TextField(
                  onChanged: cartController.updateNotes,
                  maxLines: 3,
                  decoration: InputDecoration(labelText: l10n.get('notes')),
                ),
                if (createState.error != null) ...[
                  const SizedBox(height: 12),
                  Text(
                    createState.error!.message,
                    style: const TextStyle(color: AppColors.danger),
                  ),
                ],
                const SizedBox(height: 24),
                AppButton(
                  label: l10n.get('createOrder'),
                  isLoading: createState.isSubmitting,
                  onPressed: createState.isSubmitting
                      ? null
                      : () => _createOrder(context, ref),
                ),
              ],
            ),
    );
  }

  Future<void> _createOrder(BuildContext context, WidgetRef ref) async {
    final l10n = AppLocalizations.of(context);
    final cart = ref.read(pharmacyCartControllerProvider);

    if (cart.pharmacyId == null || cart.items.isEmpty) return;
    if (cart.requiresPrescription && cart.prescription == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(l10n.get('prescriptionRequiredMessage'))),
      );
      return;
    }

    final order = await ref
        .read(createPharmacyOrderControllerProvider.notifier)
        .create(
          CreatePharmacyOrderRequest(
            pharmacyProviderId: cart.pharmacyId!,
            items: cart.items,
            prescriptionId: cart.prescription?.id,
            notes: cart.notes,
            deliveryAddress: cart.deliveryAddress,
          ),
        );

    if (order == null || !context.mounted) return;
    ref.read(pharmacyCartControllerProvider.notifier).clear();
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(l10n.get('orderCreated'))));
    context.go(RouteNames.pharmacyOrderDetails(order.id));
  }
}
