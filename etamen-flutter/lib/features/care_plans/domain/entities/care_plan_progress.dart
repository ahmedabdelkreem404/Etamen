import 'package:etamen_app/features/care_plans/domain/entities/care_plan_checkin.dart';
import 'package:etamen_app/features/care_plans/domain/entities/meal_log.dart';

class CarePlanProgress {
  const CarePlanProgress({
    required this.planId,
    this.fromDate,
    this.toDate,
    this.daysCount = 0,
    this.checkinsCount = 0,
    this.averageCommitmentScore,
    this.mealLogsCount = 0,
    this.followedCount = 0,
    this.partiallyFollowedCount = 0,
    this.skippedCount = 0,
    this.replacedCount = 0,
    this.extraMealCount = 0,
    this.adherencePercentage,
    this.disclaimer,
    this.latestCheckin,
    this.latestMealLogs = const [],
  });

  final int planId;
  final String? fromDate;
  final String? toDate;
  final int daysCount;
  final int checkinsCount;
  final double? averageCommitmentScore;
  final int mealLogsCount;
  final int followedCount;
  final int partiallyFollowedCount;
  final int skippedCount;
  final int replacedCount;
  final int extraMealCount;
  final double? adherencePercentage;
  final String? disclaimer;
  final CarePlanCheckin? latestCheckin;
  final List<MealLog> latestMealLogs;
}
