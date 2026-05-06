import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_food_item.dart';
import 'package:etamen_app/features/care_plans/presentation/providers/care_plans_providers.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_day_card.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_disclaimer_box.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_labels.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_meal_card.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_status_chip.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/food_item_chip.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/instruction_card.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/progress_summary_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class CarePlanDetailsPage extends ConsumerWidget {
  const CarePlanDetailsPage({required this.planId, super.key});

  final int planId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(carePlanDetailsControllerProvider(planId));
    final controller = ref.read(
      carePlanDetailsControllerProvider(planId).notifier,
    );

    return AppScaffold(
      title: l10n.get('carePlanDetails'),
      body: RefreshIndicator(
        onRefresh: controller.load,
        child: state.isLoading
            ? const LoadingView()
            : state.error != null
            ? ErrorView(message: state.error!.message, onRetry: controller.load)
            : state.plan == null
            ? EmptyView(message: l10n.get('carePlanNotFound'))
            : ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                children: [
                  CarePlanDisclaimerBox(text: state.plan!.safetyDisclaimer),
                  const SizedBox(height: 16),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              state.plan!.title,
                              style: Theme.of(context).textTheme.headlineSmall,
                            ),
                            Text(
                              carePlanTypeLabel(context, state.plan!.planType),
                              style: const TextStyle(color: AppColors.muted),
                            ),
                          ],
                        ),
                      ),
                      CarePlanStatusChip(status: state.plan!.status),
                    ],
                  ),
                  if (state.plan!.goalText?.isNotEmpty == true) ...[
                    const SizedBox(height: 12),
                    Text(
                      l10n.get('planGoal'),
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                    Text(state.plan!.goalText!),
                  ],
                  if (state.plan!.description?.isNotEmpty == true) ...[
                    const SizedBox(height: 12),
                    Text(state.plan!.description!),
                  ],
                  const SizedBox(height: 16),
                  if (state.canTrack)
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        FilledButton.icon(
                          onPressed: () =>
                              context.push(RouteNames.carePlanCheckin(planId)),
                          icon: const Icon(Icons.check_circle_outline),
                          label: Text(l10n.get('dailyCheckin')),
                        ),
                        OutlinedButton.icon(
                          onPressed: () =>
                              context.push(RouteNames.carePlanMealLog(planId)),
                          icon: const Icon(Icons.restaurant_outlined),
                          label: Text(l10n.get('logMeal')),
                        ),
                        OutlinedButton.icon(
                          onPressed: () =>
                              context.push(RouteNames.carePlanProgress(planId)),
                          icon: const Icon(Icons.insights_outlined),
                          label: Text(l10n.get('viewProgress')),
                        ),
                      ],
                    )
                  else
                    Card(
                      child: ListTile(
                        leading: const Icon(Icons.lock_outline),
                        title: Text(l10n.get('inactivePlanMessage')),
                      ),
                    ),
                  const SizedBox(height: 16),
                  if (state.progress != null)
                    ProgressSummaryCard(progress: state.progress!),
                  _SectionTitle(l10n.get('planDays')),
                  if (state.days.isEmpty && state.meals.isEmpty)
                    EmptyView(message: l10n.get('noMealsForPlan'))
                  else if (state.days.isNotEmpty)
                    ...state.days.map((day) => CarePlanDayCard(day: day))
                  else
                    ...state.meals.map((meal) => CarePlanMealCard(meal: meal)),
                  _SectionTitle(l10n.get('foods')),
                  ...FoodCategory.values
                      .where((category) => category != FoodCategory.unknown)
                      .map(
                        (category) => _FoodGroup(
                          title: foodCategoryLabel(context, category),
                          items: state.foods
                              .where((item) => item.category == category)
                              .toList(growable: false),
                        ),
                      ),
                  _SectionTitle(l10n.get('instructions')),
                  if (state.instructions.isEmpty)
                    Text(
                      l10n.get('noAdditionalDetails'),
                      style: const TextStyle(color: AppColors.muted),
                    )
                  else
                    ...state.instructions.map(
                      (instruction) =>
                          InstructionCard(instruction: instruction),
                    ),
                ],
              ),
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  const _SectionTitle(this.title);

  final String title;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(top: 18, bottom: 8),
      child: Text(title, style: Theme.of(context).textTheme.titleMedium),
    );
  }
}

class _FoodGroup extends StatelessWidget {
  const _FoodGroup({required this.title, required this.items});

  final String title;
  final List<CarePlanFoodItem> items;

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) return const SizedBox.shrink();
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: Theme.of(context).textTheme.titleSmall),
          const SizedBox(height: 6),
          Wrap(
            spacing: 6,
            runSpacing: 6,
            children: items.map((item) => FoodItemChip(item: item)).toList(),
          ),
        ],
      ),
    );
  }
}
