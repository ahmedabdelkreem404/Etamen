import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/care_plans/presentation/providers/care_plans_providers.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_day_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class CarePlanDayPage extends ConsumerWidget {
  const CarePlanDayPage({required this.planId, required this.dayId, super.key});

  final int planId;
  final int dayId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(carePlanDetailsControllerProvider(planId));
    final controller = ref.read(
      carePlanDetailsControllerProvider(planId).notifier,
    );
    final matchingDays = state.days.where((item) => item.id == dayId);
    final day = matchingDays.isEmpty ? null : matchingDays.first;

    return AppScaffold(
      title: l10n.get('planDay'),
      body: state.isLoading
          ? const LoadingView()
          : state.error != null
          ? ErrorView(message: state.error!.message, onRetry: controller.load)
          : day == null
          ? EmptyView(message: l10n.get('noAdditionalDetails'))
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [CarePlanDayCard(day: day)],
            ),
    );
  }
}
