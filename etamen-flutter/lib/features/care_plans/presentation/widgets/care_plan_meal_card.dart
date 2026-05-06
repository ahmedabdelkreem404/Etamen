import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_meal.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_labels.dart';
import 'package:flutter/material.dart';

class CarePlanMealCard extends StatelessWidget {
  const CarePlanMealCard({required this.meal, super.key});

  final CarePlanMeal meal;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final title = meal.title ?? mealTypeLabel(context, meal.mealType);
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: Colors.black.withValues(alpha: 0.06)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  title,
                  style: Theme.of(context).textTheme.titleSmall,
                ),
              ),
              if (meal.isRequired)
                Chip(
                  visualDensity: VisualDensity.compact,
                  label: Text(l10n.get('required')),
                ),
            ],
          ),
          Text(
            mealTypeLabel(context, meal.mealType),
            style: const TextStyle(color: AppColors.muted),
          ),
          if (meal.description?.isNotEmpty == true) ...[
            const SizedBox(height: 6),
            Text(meal.description!),
          ],
          if (meal.instructions?.isNotEmpty == true) ...[
            const SizedBox(height: 6),
            Text(
              meal.instructions!,
              style: const TextStyle(color: AppColors.muted),
            ),
          ],
          if (meal.calories != null ||
              meal.proteinG != null ||
              meal.carbsG != null ||
              meal.fatG != null) ...[
            const SizedBox(height: 8),
            Text(
              l10n.get('approxNutritionValues'),
              style: const TextStyle(color: AppColors.muted, fontSize: 12),
            ),
            const SizedBox(height: 4),
            Wrap(
              spacing: 8,
              runSpacing: 4,
              children: [
                if (meal.calories != null) Text('${meal.calories} kcal'),
                if (meal.proteinG != null) Text('P ${meal.proteinG}g'),
                if (meal.carbsG != null) Text('C ${meal.carbsG}g'),
                if (meal.fatG != null) Text('F ${meal.fatG}g'),
              ],
            ),
          ],
        ],
      ),
    );
  }
}
