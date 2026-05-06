import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_food_item.dart';
import 'package:flutter/material.dart';

class FoodItemChip extends StatelessWidget {
  const FoodItemChip({required this.item, super.key});

  final CarePlanFoodItem item;

  @override
  Widget build(BuildContext context) {
    final color = switch (item.category) {
      FoodCategory.allowed => AppColors.success,
      FoodCategory.recommended => AppColors.info,
      FoodCategory.limited => AppColors.warning,
      FoodCategory.forbidden => AppColors.muted,
      FoodCategory.unknown => AppColors.muted,
    };
    return Tooltip(
      message: item.notes ?? item.safetyNote ?? item.name,
      child: Chip(
        backgroundColor: color.withValues(alpha: 0.08),
        side: BorderSide(color: color.withValues(alpha: 0.18)),
        label: Text(item.name, style: TextStyle(color: color)),
      ),
    );
  }
}
