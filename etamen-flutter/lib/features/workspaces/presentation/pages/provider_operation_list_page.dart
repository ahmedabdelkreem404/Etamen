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
  String _query = '';
  _CatalogSort _catalogSort = _CatalogSort.newest;

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
    final isCatalog = _isCatalogSection(config.section);
    final items = _filteredItems(state.items, isCatalog);
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
            if (isCatalog) ...[
              TextField(
                onChanged: (value) => setState(() => _query = value),
                decoration: InputDecoration(
                  hintText: isArabic ? 'ابحث في الكتالوج' : 'Search catalog',
                  prefixIcon: const Icon(Icons.search),
                ),
              ),
              const SizedBox(height: 12),
              _ProviderCatalogControls(
                section: config.section,
                selectedStatus: _selectedStatus,
                selectedSort: _catalogSort,
                onStatus: (status) => setState(() => _selectedStatus = status),
                onSort: (sort) => setState(() => _catalogSort = sort),
              ),
              const SizedBox(height: 12),
            ],
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

  List<ProviderOperationItem> _filteredItems(
    List<ProviderOperationItem> source,
    bool isCatalog,
  ) {
    Iterable<ProviderOperationItem> items = source;
    if (_selectedStatus != null) {
      if (isCatalog) {
        final active = _selectedStatus == 'active';
        items = items.where((item) => item.isActive == active);
      } else {
        items = items.where((item) => item.status == _selectedStatus);
      }
    }
    if (isCatalog && _query.trim().isNotEmpty) {
      final needle = _query.trim().toLowerCase();
      items = items.where(
        (item) =>
            item.title(false).toLowerCase().contains(needle) ||
            item.title(true).toLowerCase().contains(needle) ||
            item.subtitle(false).toLowerCase().contains(needle) ||
            item.subtitle(true).toLowerCase().contains(needle),
      );
    }
    final sorted = items.toList(growable: false);
    if (isCatalog) {
      sorted.sort((a, b) {
        return switch (_catalogSort) {
          _CatalogSort.priceAsc => _amount(a).compareTo(_amount(b)),
          _CatalogSort.priceDesc => _amount(b).compareTo(_amount(a)),
          _CatalogSort.name => a.title(false).compareTo(b.title(false)),
          _CatalogSort.resultTime => (a.resultTimeHours ?? 9999).compareTo(
            b.resultTimeHours ?? 9999,
          ),
          _CatalogSort.newest => b.id.compareTo(a.id),
        };
      });
    }
    return sorted;
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

enum _CatalogSort { newest, priceAsc, priceDesc, name, resultTime }

class _ProviderCatalogControls extends StatelessWidget {
  const _ProviderCatalogControls({
    required this.section,
    required this.selectedStatus,
    required this.selectedSort,
    required this.onStatus,
    required this.onSort,
  });

  final String section;
  final String? selectedStatus;
  final _CatalogSort selectedSort;
  final ValueChanged<String?> onStatus;
  final ValueChanged<_CatalogSort> onSort;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    final sorts = section == 'lab/catalog'
        ? _CatalogSort.values
        : const [
            _CatalogSort.newest,
            _CatalogSort.priceAsc,
            _CatalogSort.priceDesc,
            _CatalogSort.name,
          ];
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: [
              Padding(
                padding: const EdgeInsetsDirectional.only(end: 8),
                child: FilterChip(
                  selected: selectedStatus == null,
                  label: Text(isArabic ? 'الكل' : 'All'),
                  onSelected: (_) => onStatus(null),
                ),
              ),
              for (final status in const ['active', 'inactive'])
                Padding(
                  padding: const EdgeInsetsDirectional.only(end: 8),
                  child: FilterChip(
                    selected: selectedStatus == status,
                    label: Text(
                      status == 'active'
                          ? (isArabic ? 'نشط' : 'Active')
                          : (isArabic ? 'غير نشط' : 'Inactive'),
                    ),
                    onSelected: (_) => onStatus(status),
                  ),
                ),
            ],
          ),
        ),
        const SizedBox(height: 8),
        DropdownButtonFormField<_CatalogSort>(
          value: selectedSort,
          decoration: InputDecoration(
            labelText: isArabic ? 'ترتيب النتائج' : 'Sort results',
          ),
          items: sorts
              .map(
                (sort) => DropdownMenuItem(
                  value: sort,
                  child: Text(_sortLabel(sort, isArabic)),
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

  String _sortLabel(_CatalogSort sort, bool isArabic) {
    return switch (sort) {
      _CatalogSort.newest => isArabic ? 'الأحدث' : 'Newest',
      _CatalogSort.priceAsc => isArabic ? 'السعر الأقل' : 'Lowest price',
      _CatalogSort.priceDesc => isArabic ? 'السعر الأعلى' : 'Highest price',
      _CatalogSort.name => isArabic ? 'الاسم' : 'Name',
      _CatalogSort.resultTime => isArabic ? 'وقت النتيجة' : 'Result time',
    };
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

bool _isCatalogSection(String section) {
  return section == 'pharmacy/products' || section == 'lab/catalog';
}

num _amount(ProviderOperationItem item) {
  final raw = item.raw;
  final value =
      raw['total_amount'] ??
      raw['grand_total'] ??
      raw['price'] ??
      raw['consultation_fee'];
  if (value is num) return value;
  return num.tryParse(value?.toString() ?? '') ?? 0;
}
