import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/features/labs/data/models/create_lab_order_request.dart';
import 'package:etamen_app/features/labs/presentation/providers/labs_providers.dart';
import 'package:etamen_app/features/labs/presentation/widgets/lab_cart_item_tile.dart';
import 'package:etamen_app/features/labs/presentation/widgets/sample_option_selector.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class LabCartPage extends ConsumerWidget {
  const LabCartPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final cart = ref.watch(labCartControllerProvider);
    final createState = ref.watch(createLabOrderControllerProvider);
    final cartController = ref.read(labCartControllerProvider.notifier);

    return AppScaffold(
      title: l10n.get('labOrderCart'),
      body: cart.isEmpty
          ? EmptyView(
              message: l10n.get('labCartEmpty'),
              icon: Icons.science_outlined,
            )
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                ...cart.items.map(
                  (item) => LabCartItemTile(
                    item: item,
                    onIncrease: () => cartController.updateQuantity(
                      item.type,
                      item.itemId,
                      item.quantity + 1,
                    ),
                    onDecrease: () => cartController.updateQuantity(
                      item.type,
                      item.itemId,
                      item.quantity - 1,
                    ),
                    onRemove: () =>
                        cartController.remove(item.type, item.itemId),
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
                          l10n.get('labConfirmsTotal'),
                          style: const TextStyle(color: AppColors.muted),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                SampleOptionSelector(
                  value: cart.sampleCollectionMethod,
                  onChanged: cartController.setSampleMethod,
                ),
                if (cart.requiresHomeAddress) ...[
                  const SizedBox(height: 12),
                  TextField(
                    onChanged: cartController.updateAddress,
                    maxLines: 2,
                    decoration: InputDecoration(
                      labelText: l10n.get('homeAddress'),
                    ),
                  ),
                ],
                const SizedBox(height: 12),
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
                  label: l10n.get('createLabOrder'),
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
    final cart = ref.read(labCartControllerProvider);
    if (cart.labId == null || cart.items.isEmpty) return;
    if (cart.requiresHomeAddress && !cart.hasHomeAddress) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(l10n.get('homeAddressRequired'))));
      return;
    }

    final order = await ref
        .read(createLabOrderControllerProvider.notifier)
        .create(
          CreateLabOrderRequest(
            labProviderId: cart.labId!,
            items: cart.items,
            sampleCollectionMethod: cart.sampleCollectionMethod,
            collectionAddress: cart.collectionAddress,
            notes: cart.notes,
          ),
        );

    if (order == null || !context.mounted) return;
    ref.read(labCartControllerProvider.notifier).clear();
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(l10n.get('labOrderCreated'))));
    context.go(RouteNames.labOrderDetails(order.id));
  }
}
