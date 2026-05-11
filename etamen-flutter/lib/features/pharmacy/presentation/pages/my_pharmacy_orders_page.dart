import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/pharmacy/presentation/providers/pharmacy_providers.dart';
import 'package:etamen_app/features/pharmacy/presentation/widgets/pharmacy_order_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class MyPharmacyOrdersPage extends ConsumerWidget {
  const MyPharmacyOrdersPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(pharmacyOrdersControllerProvider);
    final controller = ref.read(pharmacyOrdersControllerProvider.notifier);

    return AppScaffold(
      title: l10n.get('pharmacyOrders'),
      body: RefreshIndicator(
        onRefresh: controller.load,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            _FilterChips(
              selected: state.selectedFilter,
              onSelected: controller.selectFilter,
            ),
            const SizedBox(height: 12),
            if (state.isLoading)
              const LoadingView()
            else if (state.error != null)
              ErrorView(message: state.error!.message, onRetry: controller.load)
            else if (state.isEmpty)
              EmptyView(
                message: l10n.get('noPharmacyOrders'),
                icon: Icons.receipt_long_outlined,
              )
            else
              ...state.filteredItems.map(
                (order) => PharmacyOrderCard(order: order),
              ),
          ],
        ),
      ),
    );
  }
}

class _FilterChips extends StatelessWidget {
  const _FilterChips({required this.selected, required this.onSelected});

  final PharmacyOrderFilter selected;
  final ValueChanged<PharmacyOrderFilter> onSelected;

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: PharmacyOrderFilter.values
            .map(
              (filter) => Padding(
                padding: const EdgeInsetsDirectional.only(end: 8),
                child: FilterChip(
                  selected: selected == filter,
                  label: Text(_label(context, filter)),
                  onSelected: (_) => onSelected(filter),
                ),
              ),
            )
            .toList(growable: false),
      ),
    );
  }

  String _label(BuildContext context, PharmacyOrderFilter filter) {
    final l10n = AppLocalizations.of(context);
    return switch (filter) {
      PharmacyOrderFilter.all => l10n.get('all'),
      PharmacyOrderFilter.review => l10n.get('pharmacyReview'),
      PharmacyOrderFilter.awaitingPayment => l10n.get('awaitingPayment'),
      PharmacyOrderFilter.preparing => l10n.get('preparing'),
      PharmacyOrderFilter.ready => _copy(context, 'جاهز', 'Ready'),
      PharmacyOrderFilter.outForDelivery => _copy(
        context,
        'في التوصيل',
        'Out for delivery',
      ),
      PharmacyOrderFilter.completed => l10n.get('completed'),
      PharmacyOrderFilter.rejected => l10n.get('rejected'),
    };
  }

  String _copy(BuildContext context, String ar, String en) {
    return AppLocalizations.of(context).isArabic ? ar : en;
  }
}
