import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/labs/presentation/providers/labs_providers.dart';
import 'package:etamen_app/features/labs/presentation/widgets/lab_order_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class MyLabOrdersPage extends ConsumerWidget {
  const MyLabOrdersPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(labOrdersControllerProvider);
    final controller = ref.read(labOrdersControllerProvider.notifier);

    return AppScaffold(
      title: l10n.get('labOrders'),
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
                message: l10n.get('noLabOrders'),
                icon: Icons.receipt_long_outlined,
              )
            else
              ...state.filteredItems.map((order) => LabOrderCard(order: order)),
          ],
        ),
      ),
    );
  }
}

class _FilterChips extends StatelessWidget {
  const _FilterChips({required this.selected, required this.onSelected});

  final LabOrderFilter selected;
  final ValueChanged<LabOrderFilter> onSelected;

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: LabOrderFilter.values
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

  String _label(BuildContext context, LabOrderFilter filter) {
    final l10n = AppLocalizations.of(context);
    return switch (filter) {
      LabOrderFilter.all => l10n.get('all'),
      LabOrderFilter.review => l10n.get('labReview'),
      LabOrderFilter.awaitingPayment => l10n.get('awaitingPayment'),
      LabOrderFilter.accepted => _copy(context, 'تم القبول', 'Accepted'),
      LabOrderFilter.sampleCollected => _copy(
        context,
        'جمع العينة',
        'Sample collected',
      ),
      LabOrderFilter.processing => l10n.get('labProcessing'),
      LabOrderFilter.resultReady => l10n.get('resultReady'),
      LabOrderFilter.completed => l10n.get('completed'),
      LabOrderFilter.rejected => l10n.get('rejected'),
    };
  }

  String _copy(BuildContext context, String ar, String en) {
    return AppLocalizations.of(context).isArabic ? ar : en;
  }
}
