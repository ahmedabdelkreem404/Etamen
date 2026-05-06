import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_checkin.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_food_item.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_instruction.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_meal.dart';
import 'package:etamen_app/features/care_plans/domain/entities/meal_log.dart';
import 'package:flutter/widgets.dart';

String carePlanTypeLabel(BuildContext context, CarePlanType type) {
  final l10n = AppLocalizations.of(context);
  return switch (type) {
    CarePlanType.nutrition => l10n.get('nutritionPlan'),
    CarePlanType.generalCare => l10n.get('generalCarePlan'),
    CarePlanType.weightManagement => l10n.get('weightManagement'),
    CarePlanType.diabetesFollowup => l10n.get('diabetesFollowup'),
    CarePlanType.bloodPressureFollowup => l10n.get('bloodPressureFollowup'),
    CarePlanType.fitnessFollowup => l10n.get('fitnessFollowup'),
    CarePlanType.recoveryFollowup => l10n.get('recoveryFollowup'),
    CarePlanType.other => l10n.get('other'),
    CarePlanType.unknown => l10n.get('unknown'),
  };
}

String carePlanStatusLabel(BuildContext context, CarePlanStatus status) {
  final l10n = AppLocalizations.of(context);
  return switch (status) {
    CarePlanStatus.draft => l10n.get('draft'),
    CarePlanStatus.active => l10n.get('active'),
    CarePlanStatus.paused => l10n.get('paused'),
    CarePlanStatus.completed => l10n.get('completed'),
    CarePlanStatus.cancelled => l10n.get('cancelled'),
    CarePlanStatus.unknown => l10n.get('unknown'),
  };
}

String mealTypeLabel(BuildContext context, MealType type) {
  final l10n = AppLocalizations.of(context);
  return switch (type) {
    MealType.breakfast => l10n.get('breakfast'),
    MealType.snack1 => l10n.get('snack1'),
    MealType.lunch => l10n.get('lunch'),
    MealType.snack2 => l10n.get('snack2'),
    MealType.dinner => l10n.get('dinner'),
    MealType.snack3 => l10n.get('snack3'),
    MealType.other => l10n.get('other'),
    MealType.unknown => l10n.get('unknown'),
  };
}

String foodCategoryLabel(BuildContext context, FoodCategory category) {
  final l10n = AppLocalizations.of(context);
  return switch (category) {
    FoodCategory.allowed => l10n.get('allowedFood'),
    FoodCategory.forbidden => l10n.get('avoidByPlan'),
    FoodCategory.limited => l10n.get('limitedFood'),
    FoodCategory.recommended => l10n.get('recommendedFood'),
    FoodCategory.unknown => l10n.get('unknown'),
  };
}

String instructionTypeLabel(BuildContext context, InstructionType type) {
  final l10n = AppLocalizations.of(context);
  return switch (type) {
    InstructionType.general => l10n.get('general'),
    InstructionType.hydration => l10n.get('hydration'),
    InstructionType.sleep => l10n.get('sleep'),
    InstructionType.activity => l10n.get('activity'),
    InstructionType.nutrition => l10n.get('nutrition'),
    InstructionType.warning => l10n.get('warning'),
    InstructionType.providerNote => l10n.get('providerNote'),
    InstructionType.unknown => l10n.get('unknown'),
  };
}

String mealLogStatusLabel(BuildContext context, MealLogStatus status) {
  final l10n = AppLocalizations.of(context);
  return switch (status) {
    MealLogStatus.followed => l10n.get('mealFollowed'),
    MealLogStatus.partiallyFollowed => l10n.get('mealPartiallyFollowed'),
    MealLogStatus.skipped => l10n.get('mealSkipped'),
    MealLogStatus.replaced => l10n.get('mealReplaced'),
    MealLogStatus.extraMeal => l10n.get('extraMeal'),
    MealLogStatus.unknown => l10n.get('unknown'),
  };
}

String checkinMoodLabel(BuildContext context, CheckinMood mood) {
  final l10n = AppLocalizations.of(context);
  return switch (mood) {
    CheckinMood.veryBad => l10n.get('veryBad'),
    CheckinMood.bad => l10n.get('bad'),
    CheckinMood.neutral => l10n.get('neutral'),
    CheckinMood.good => l10n.get('good'),
    CheckinMood.veryGood => l10n.get('veryGood'),
    CheckinMood.unknown => l10n.get('unknown'),
  };
}
