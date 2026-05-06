import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/health/domain/entities/vital_record.dart';
import 'package:etamen_app/features/health/presentation/providers/health_providers.dart';
import 'package:etamen_app/features/health/presentation/widgets/health_disclaimer_box.dart';
import 'package:etamen_app/features/health/presentation/widgets/health_summary_card.dart';
import 'package:etamen_app/features/health/presentation/widgets/trend_preview_card.dart';
import 'package:etamen_app/features/health/presentation/widgets/vital_card.dart';
import 'package:etamen_app/features/health/presentation/widgets/vital_type_selector.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class HealthDashboardPage extends ConsumerWidget {
  const HealthDashboardPage({this.showAppBar = true, super.key});

  final bool showAppBar;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(healthDashboardControllerProvider);
    final controller = ref.read(healthDashboardControllerProvider.notifier);

    final body = RefreshIndicator(
      onRefresh: controller.load,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        children: [
          if (!showAppBar) ...[
            Text(
              l10n.get('health'),
              style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                fontWeight: FontWeight.w800,
              ),
            ),
            const SizedBox(height: 12),
          ],
          const HealthDisclaimerBox(),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: FilledButton.icon(
                  onPressed: () => context.push(RouteNames.healthProfile),
                  icon: const Icon(Icons.badge_outlined),
                  label: Text(l10n.get('healthProfile')),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: () => context.push(RouteNames.healthVitals),
                  icon: const Icon(Icons.list_alt_outlined),
                  label: Text(l10n.get('vitals')),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(
            l10n.get('addVital'),
            style: Theme.of(context).textTheme.titleMedium,
          ),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: supportedVitalTypes.map((type) {
              return ActionChip(
                label: Text(vitalTypeLabel(context, type)),
                avatar: Icon(_iconFor(type), size: 18),
                onPressed: () => context.push(RouteNames.addVital(type)),
              );
            }).toList(growable: false),
          ),
          const SizedBox(height: 16),
          if (state.isLoading)
            const LoadingView()
          else if (state.error != null)
            ErrorView(message: state.error!.message, onRetry: controller.load)
          else if (state.isEmpty)
            EmptyView(
              message: l10n.get('addFirstHealthRecord'),
              icon: Icons.health_and_safety_outlined,
            )
          else ...[
            if (state.summary != null) HealthSummaryCard(summary: state.summary!),
            if (state.trend != null) TrendPreviewCard(trend: state.trend!),
            const SizedBox(height: 8),
            Text(
              l10n.get('latestVitals'),
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 8),
            ...state.latestVitals.take(5).map((record) => VitalCard(record: record)),
          ],
        ],
      ),
    );

    if (!showAppBar) return body;
    return AppScaffold(
      title: l10n.get('health'),
      actions: [
        IconButton(
          tooltip: l10n.get('addVital'),
          onPressed: () => context.push(RouteNames.addVital()),
          icon: const Icon(Icons.add_circle_outline),
        ),
      ],
      body: body,
    );
  }

  static IconData _iconFor(VitalType type) {
    return switch (type) {
      VitalType.bloodPressure => Icons.monitor_heart_outlined,
      VitalType.bloodSugar => Icons.bloodtype_outlined,
      VitalType.heartRate => Icons.favorite_border,
      VitalType.oxygen => Icons.air_outlined,
      VitalType.temperature => Icons.thermostat_outlined,
      VitalType.weight => Icons.scale_outlined,
      VitalType.sleep => Icons.bedtime_outlined,
      VitalType.mood => Icons.mood_outlined,
      VitalType.symptoms => Icons.note_alt_outlined,
      VitalType.unknown => Icons.health_and_safety_outlined,
    };
  }
}
