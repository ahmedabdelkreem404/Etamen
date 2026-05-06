import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/labs/presentation/providers/labs_providers.dart';
import 'package:etamen_app/features/labs/presentation/widgets/lab_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class LabsPage extends ConsumerWidget {
  const LabsPage({this.showAppBar = true, super.key});

  final bool showAppBar;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(labsControllerProvider);
    final controller = ref.read(labsControllerProvider.notifier);
    final cart = ref.watch(labCartControllerProvider);

    final body = RefreshIndicator(
      onRefresh: controller.load,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        children: [
          if (!showAppBar) ...[
            Text(
              l10n.get('labs'),
              style: Theme.of(
                context,
              ).textTheme.headlineMedium?.copyWith(fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 12),
          ],
          TextField(
            onChanged: controller.search,
            decoration: InputDecoration(
              hintText: l10n.get('searchLabTest'),
              prefixIcon: const Icon(Icons.search),
            ),
          ),
          const SizedBox(height: 12),
          if (cart.itemCount > 0)
            Card(
              child: ListTile(
                leading: const Icon(Icons.science_outlined),
                title: Text(l10n.get('labOrderCart')),
                subtitle: Text('${cart.itemCount}'),
                trailing: FilledButton.tonal(
                  onPressed: () => context.push(RouteNames.labCart),
                  child: Text(l10n.get('viewDetails')),
                ),
              ),
            ),
          Row(
            children: [
              Expanded(child: Text(l10n.get('labs'))),
              TextButton.icon(
                onPressed: () => context.push(RouteNames.labOrders),
                icon: const Icon(Icons.receipt_long_outlined),
                label: Text(l10n.get('labOrders')),
              ),
            ],
          ),
          const SizedBox(height: 8),
          if (state.isLoading)
            const LoadingView()
          else if (state.error != null)
            ErrorView(message: state.error!.message, onRetry: controller.load)
          else if (state.isEmpty)
            EmptyView(message: l10n.get('noLabs'), icon: Icons.biotech_outlined)
          else
            ...state.filteredItems.map(
              (lab) => LabCard(
                lab: lab,
                onTap: () => context.push(RouteNames.labTests(lab.id)),
              ),
            ),
        ],
      ),
    );

    if (!showAppBar) return body;
    return AppScaffold(
      title: l10n.get('labs'),
      actions: [
        IconButton(
          tooltip: l10n.get('labOrders'),
          onPressed: () => context.push(RouteNames.labOrders),
          icon: const Icon(Icons.receipt_long_outlined),
        ),
      ],
      body: body,
    );
  }
}
