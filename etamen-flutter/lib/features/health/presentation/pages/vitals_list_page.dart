import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/health/presentation/providers/health_providers.dart';
import 'package:etamen_app/features/health/presentation/widgets/health_disclaimer_box.dart';
import 'package:etamen_app/features/health/presentation/widgets/vital_card.dart';
import 'package:etamen_app/features/health/presentation/widgets/vital_type_selector.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class VitalsListPage extends ConsumerWidget {
  const VitalsListPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(vitalsListControllerProvider);
    final controller = ref.read(vitalsListControllerProvider.notifier);

    return AppScaffold(
      title: l10n.get('vitals'),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push(RouteNames.addVital()),
        icon: const Icon(Icons.add),
        label: Text(l10n.get('addVital')),
      ),
      body: RefreshIndicator(
        onRefresh: () => controller.load(type: state.selectedType),
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            const HealthDisclaimerBox(),
            const SizedBox(height: 16),
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  ChoiceChip(
                    label: Text(l10n.get('all')),
                    selected: state.selectedType == null,
                    onSelected: (_) => controller.selectType(null),
                  ),
                  const SizedBox(width: 8),
                  ...supportedVitalTypes.map(
                    (type) => Padding(
                      padding: const EdgeInsetsDirectional.only(end: 8),
                      child: ChoiceChip(
                        label: Text(vitalTypeLabel(context, type)),
                        selected: state.selectedType == type,
                        onSelected: (_) => controller.selectType(type),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            if (state.isLoading)
              const LoadingView()
            else if (state.error != null)
              ErrorView(
                message: state.error!.message,
                onRetry: () => controller.load(type: state.selectedType),
              )
            else if (state.isEmpty)
              EmptyView(
                message: l10n.get('noVitalsYet'),
                icon: Icons.monitor_heart_outlined,
              )
            else
              ...state.items.map((record) => VitalCard(record: record)),
          ],
        ),
      ),
    );
  }
}
