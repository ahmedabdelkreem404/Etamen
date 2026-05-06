import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/care_plans/presentation/providers/care_plans_providers.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_card.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_disclaimer_box.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class CarePlansPage extends ConsumerWidget {
  const CarePlansPage({this.showAppBar = true, super.key});

  final bool showAppBar;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(carePlansControllerProvider);
    final controller = ref.read(carePlansControllerProvider.notifier);

    final body = RefreshIndicator(
      onRefresh: controller.load,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        children: [
          if (!showAppBar) ...[
            Text(
              l10n.get('carePlans'),
              style: Theme.of(
                context,
              ).textTheme.headlineMedium?.copyWith(fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 12),
          ],
          const CarePlanDisclaimerBox(),
          const SizedBox(height: 16),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: CarePlanFilter.values
                  .map(
                    (filter) => Padding(
                      padding: const EdgeInsetsDirectional.only(end: 8),
                      child: ChoiceChip(
                        selected: state.filter == filter,
                        label: Text(_filterLabel(context, filter)),
                        onSelected: (_) => controller.selectFilter(filter),
                      ),
                    ),
                  )
                  .toList(growable: false),
            ),
          ),
          const SizedBox(height: 16),
          if (state.isLoading)
            const LoadingView()
          else if (state.error != null)
            ErrorView(message: state.error!.message, onRetry: controller.load)
          else if (state.isEmpty)
            EmptyView(
              message: l10n.get('noCarePlansYet'),
              icon: Icons.assignment_outlined,
            )
          else
            ...state.filteredItems.map(
              (plan) => CarePlanCard(
                plan: plan,
                onTap: () => context.push(RouteNames.carePlanDetails(plan.id)),
                onProgress: () =>
                    context.push(RouteNames.carePlanProgress(plan.id)),
              ),
            ),
        ],
      ),
    );

    if (!showAppBar) return body;
    return AppScaffold(title: l10n.get('carePlans'), body: body);
  }

  static String _filterLabel(BuildContext context, CarePlanFilter filter) {
    final l10n = AppLocalizations.of(context);
    return switch (filter) {
      CarePlanFilter.all => l10n.get('all'),
      CarePlanFilter.active => l10n.get('active'),
      CarePlanFilter.paused => l10n.get('paused'),
      CarePlanFilter.completed => l10n.get('completed'),
      CarePlanFilter.cancelled => l10n.get('cancelled'),
    };
  }
}
