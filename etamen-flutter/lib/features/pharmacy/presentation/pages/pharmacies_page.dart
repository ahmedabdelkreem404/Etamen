import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/pharmacy/presentation/providers/pharmacy_providers.dart';
import 'package:etamen_app/features/pharmacy/presentation/widgets/pharmacy_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class PharmaciesPage extends ConsumerWidget {
  const PharmaciesPage({this.showAppBar = true, super.key});

  final bool showAppBar;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(pharmaciesControllerProvider);
    final controller = ref.read(pharmaciesControllerProvider.notifier);
    final cart = ref.watch(pharmacyCartControllerProvider);

    final body = RefreshIndicator(
      onRefresh: controller.load,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        children: [
          if (!showAppBar) ...[
            Text(
              l10n.get('pharmacies'),
              style: Theme.of(
                context,
              ).textTheme.headlineMedium?.copyWith(fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 12),
          ],
          TextField(
            onChanged: controller.search,
            decoration: InputDecoration(
              hintText: l10n.get('searchMedicine'),
              prefixIcon: const Icon(Icons.search),
            ),
          ),
          const SizedBox(height: 12),
          if (cart.itemCount > 0)
            Card(
              child: ListTile(
                leading: const Icon(Icons.shopping_basket_outlined),
                title: Text(l10n.get('cart')),
                subtitle: Text('${cart.itemCount}'),
                trailing: FilledButton.tonal(
                  onPressed: () => context.push(RouteNames.pharmacyCart),
                  child: Text(l10n.get('viewDetails')),
                ),
              ),
            ),
          Row(
            children: [
              Expanded(child: Text(l10n.get('pharmacies'))),
              TextButton.icon(
                onPressed: () => context.push(RouteNames.pharmacyOrders),
                icon: const Icon(Icons.receipt_long_outlined),
                label: Text(l10n.get('pharmacyOrders')),
              ),
            ],
          ),
          const SizedBox(height: 8),
          if (state.isLoading)
            const LoadingView()
          else if (state.error != null)
            ErrorView(message: state.error!.message, onRetry: controller.load)
          else if (state.isEmpty)
            EmptyView(
              message: l10n.get('noPharmacies'),
              icon: Icons.local_pharmacy_outlined,
            )
          else
            ...state.filteredItems.map(
              (pharmacy) => PharmacyCard(
                pharmacy: pharmacy,
                onTap: () =>
                    context.push(RouteNames.pharmacyProducts(pharmacy.id)),
              ),
            ),
        ],
      ),
    );

    if (!showAppBar) return body;
    return AppScaffold(
      title: l10n.get('pharmacies'),
      actions: [
        IconButton(
          tooltip: l10n.get('pharmacyOrders'),
          onPressed: () => context.push(RouteNames.pharmacyOrders),
          icon: const Icon(Icons.receipt_long_outlined),
        ),
      ],
      body: body,
    );
  }
}
