import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_day.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_meal_card.dart';
import 'package:flutter/material.dart';

class CarePlanDayCard extends StatelessWidget {
  const CarePlanDayCard({required this.day, super.key});

  final CarePlanDay day;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final title =
        day.title ??
        (day.dayNumber == null
            ? day.dayDate ?? l10n.get('planDay')
            : '${l10n.get('planDay')} ${day.dayNumber}');
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: Theme.of(context).textTheme.titleMedium),
            if (day.instructions?.isNotEmpty == true) ...[
              const SizedBox(height: 8),
              Text(
                day.instructions!,
                style: const TextStyle(color: AppColors.muted),
              ),
            ],
            if (day.meals.isEmpty) ...[
              const SizedBox(height: 8),
              Text(
                l10n.get('noMealsForPlan'),
                style: const TextStyle(color: AppColors.muted),
              ),
            ] else ...[
              const SizedBox(height: 10),
              ...day.meals.map((meal) => CarePlanMealCard(meal: meal)),
            ],
          ],
        ),
      ),
    );
  }
}
