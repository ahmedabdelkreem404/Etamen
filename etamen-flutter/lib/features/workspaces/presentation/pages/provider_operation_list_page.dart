import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/workspaces/data/models/provider_operation_models.dart';
import 'package:etamen_app/features/workspaces/presentation/pages/provider_operation_sections.dart';
import 'package:etamen_app/features/workspaces/presentation/providers/provider_operation_providers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class ProviderOperationListPage extends ConsumerStatefulWidget {
  const ProviderOperationListPage({
    required this.providerId,
    required this.section,
    super.key,
  });

  final int providerId;
  final String section;

  @override
  ConsumerState<ProviderOperationListPage> createState() =>
      _ProviderOperationListPageState();
}

class _ProviderOperationListPageState
    extends ConsumerState<ProviderOperationListPage> {
  String? _selectedStatus;

  @override
  Widget build(BuildContext context) {
    final config = providerOperationSection(widget.section);
    final isArabic = AppLocalizations.of(context).isArabic;
    final args = ProviderOperationArgs(
      providerId: widget.providerId,
      section: config.section,
    );
    final state = ref.watch(providerOperationListControllerProvider(args));
    final controller = ref.read(
      providerOperationListControllerProvider(args).notifier,
    );
    final filterStatuses = _statusesForSection(config.section);
    final items = _selectedStatus == null
        ? state.items
        : state.items
              .where((item) => item.status == _selectedStatus)
              .toList(growable: false);
    final isEmpty = !state.isLoading && state.error == null && items.isEmpty;

    return AppScaffold(
      title: config.title(isArabic),
      body: RefreshIndicator(
        onRefresh: controller.load,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            _SectionIntro(config: config),
            const SizedBox(height: 12),
            if (filterStatuses.isNotEmpty) ...[
              _ProviderStatusFilterChips(
                statuses: filterStatuses,
                selected: _selectedStatus,
                onSelected: (status) => setState(() {
                  _selectedStatus = status;
                }),
              ),
              const SizedBox(height: 12),
            ],
            if (state.isLoading)
              const LoadingView()
            else if (state.error != null)
              ErrorView(message: state.error!.message, onRetry: controller.load)
            else if (isEmpty)
              EmptyView(
                message: isArabic
                    ? 'لا توجد بيانات متاحة لهذا القسم الآن.'
                    : 'No data is available for this section yet.',
                icon: _iconFor(config.iconKey),
              )
            else
              for (final item in items)
                _OperationCard(
                  item: item,
                  detailsEnabled: config.detailsEnabled,
                  onTap: config.detailsEnabled
                      ? () => context.push(
                          RouteNames.providerOperationDetails(
                            widget.providerId,
                            config.section,
                            item.id,
                          ),
                        )
                      : null,
                ),
          ],
        ),
      ),
    );
  }
}

class _SectionIntro extends StatelessWidget {
  const _SectionIntro({required this.config});

  final ProviderOperationSection config;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Row(
          children: [
            Icon(_iconFor(config.iconKey), color: AppColors.primary),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                isArabic
                    ? 'بيانات تشغيلية آمنة من السيرفر حسب صلاحيات مساحة العمل.'
                    : 'Safe operational data from the server for this workspace.',
                style: Theme.of(context).textTheme.bodyMedium,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ProviderStatusFilterChips extends StatelessWidget {
  const _ProviderStatusFilterChips({
    required this.statuses,
    required this.selected,
    required this.onSelected,
  });

  final List<String> statuses;
  final String? selected;
  final ValueChanged<String?> onSelected;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: [
          Padding(
            padding: const EdgeInsetsDirectional.only(end: 8),
            child: FilterChip(
              selected: selected == null,
              label: Text(isArabic ? 'الكل' : 'All'),
              onSelected: (_) => onSelected(null),
            ),
          ),
          for (final status in statuses)
            Padding(
              padding: const EdgeInsetsDirectional.only(end: 8),
              child: FilterChip(
                selected: selected == status,
                label: Text(friendlyStatus(status, isArabic)),
                onSelected: (_) => onSelected(status),
              ),
            ),
        ],
      ),
    );
  }
}

class _OperationCard extends StatelessWidget {
  const _OperationCard({
    required this.item,
    required this.detailsEnabled,
    this.onTap,
  });

  final ProviderOperationItem item;
  final bool detailsEnabled;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return Card(
      child: ListTile(
        leading: const Icon(
          Icons.assignment_outlined,
          color: AppColors.primary,
        ),
        title: Text(
          item.title(isArabic),
          style: const TextStyle(fontWeight: FontWeight.w800),
        ),
        subtitle: Text(item.subtitle(isArabic)),
        trailing: item.amountLabel(isArabic) != null
            ? Text(
                item.amountLabel(isArabic)!,
                style: const TextStyle(fontWeight: FontWeight.w900),
              )
            : detailsEnabled
            ? const Icon(Icons.chevron_right)
            : null,
        onTap: onTap,
      ),
    );
  }
}

IconData _iconFor(String key) {
  return switch (key) {
    'appointments' => Icons.event_available_outlined,
    'departments' => Icons.account_tree_outlined,
    'doctors' => Icons.medical_services_outlined,
    'orders' => Icons.receipt_long_outlined,
    'products' => Icons.inventory_2_outlined,
    'catalog' => Icons.list_alt_outlined,
    'bookings' => Icons.book_online_outlined,
    'plans' => Icons.card_membership_outlined,
    'classes' => Icons.groups_outlined,
    'availability' => Icons.schedule_outlined,
    'sessions' => Icons.sports_outlined,
    _ => Icons.business_center_outlined,
  };
}

List<String> _statusesForSection(String section) {
  return switch (section) {
    'pharmacy/orders' => const [
      'pharmacy_review',
      'awaiting_payment',
      'paid',
      'preparing',
      'ready_for_pickup',
      'out_for_delivery',
      'delivered',
      'rejected',
      'cancelled',
    ],
    'lab/orders' => const [
      'lab_review',
      'awaiting_payment',
      'accepted',
      'sample_collected',
      'processing',
      'result_ready',
      'completed',
      'rejected',
      'cancelled',
    ],
    _ => const [],
  };
}
