import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_meal.dart';
import 'package:etamen_app/features/care_plans/domain/entities/meal_log.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_labels.dart';
import 'package:flutter/material.dart';

class MealLogForm extends StatelessWidget {
  const MealLogForm({
    required this.meals,
    required this.selectedMealId,
    required this.mealType,
    required this.status,
    required this.descriptionController,
    required this.notesController,
    required this.onMealChanged,
    required this.onMealTypeChanged,
    required this.onStatusChanged,
    super.key,
  });

  final List<CarePlanMeal> meals;
  final int? selectedMealId;
  final MealType mealType;
  final MealLogStatus status;
  final TextEditingController descriptionController;
  final TextEditingController notesController;
  final ValueChanged<int?> onMealChanged;
  final ValueChanged<MealType> onMealTypeChanged;
  final ValueChanged<MealLogStatus> onStatusChanged;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Column(
      children: [
        DropdownButtonFormField<int?>(
          value: selectedMealId,
          decoration: InputDecoration(labelText: l10n.get('plannedMeal')),
          items: [
            DropdownMenuItem<int?>(
              value: null,
              child: Text(l10n.get('withoutPlannedMeal')),
            ),
            ...meals.map(
              (meal) => DropdownMenuItem<int?>(
                value: meal.id,
                child: Text(
                  meal.title ?? mealTypeLabel(context, meal.mealType),
                ),
              ),
            ),
          ],
          onChanged: onMealChanged,
        ),
        const SizedBox(height: 12),
        DropdownButtonFormField<MealType>(
          value: mealType,
          decoration: InputDecoration(labelText: l10n.get('mealType')),
          items: MealType.values
              .where((item) => item != MealType.unknown)
              .map(
                (item) => DropdownMenuItem(
                  value: item,
                  child: Text(mealTypeLabel(context, item)),
                ),
              )
              .toList(growable: false),
          onChanged: (value) {
            if (value != null) onMealTypeChanged(value);
          },
        ),
        const SizedBox(height: 12),
        DropdownButtonFormField<MealLogStatus>(
          value: status,
          decoration: InputDecoration(labelText: l10n.get('mealLogStatus')),
          items: MealLogStatus.values
              .where((item) => item != MealLogStatus.unknown)
              .map(
                (item) => DropdownMenuItem(
                  value: item,
                  child: Text(mealLogStatusLabel(context, item)),
                ),
              )
              .toList(growable: false),
          onChanged: (value) {
            if (value != null) onStatusChanged(value);
          },
        ),
        const SizedBox(height: 12),
        TextField(
          controller: descriptionController,
          maxLines: 3,
          decoration: InputDecoration(labelText: l10n.get('description')),
        ),
        const SizedBox(height: 12),
        TextField(
          controller: notesController,
          maxLines: 3,
          decoration: InputDecoration(labelText: l10n.get('notes')),
        ),
      ],
    );
  }
}
