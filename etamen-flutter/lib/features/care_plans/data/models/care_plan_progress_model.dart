import 'package:etamen_app/features/care_plans/data/models/care_plan_checkin_model.dart';
import 'package:etamen_app/features/care_plans/data/models/meal_log_model.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_progress.dart';

class CarePlanProgressModel extends CarePlanProgress {
  const CarePlanProgressModel({
    required super.planId,
    super.fromDate,
    super.toDate,
    super.daysCount,
    super.checkinsCount,
    super.averageCommitmentScore,
    super.mealLogsCount,
    super.followedCount,
    super.partiallyFollowedCount,
    super.skippedCount,
    super.replacedCount,
    super.extraMealCount,
    super.adherencePercentage,
    super.disclaimer,
    super.latestCheckin,
    super.latestMealLogs,
  });

  factory CarePlanProgressModel.fromJson(Map<String, dynamic> json) {
    final latestCheckin = _asMap(json['latest_checkin']);
    return CarePlanProgressModel(
      planId: _toInt(json['plan_id']) ?? 0,
      fromDate: _string(json['from'] ?? json['from_date']),
      toDate: _string(json['to'] ?? json['to_date']),
      daysCount: _toInt(json['days_count']) ?? _toInt(json['total_days']) ?? 0,
      checkinsCount: _toInt(json['checkins_count']) ?? 0,
      averageCommitmentScore: _toDouble(json['average_commitment_score']),
      mealLogsCount: _toInt(json['meal_logs_count']) ?? 0,
      followedCount: _toInt(json['followed_count']) ?? 0,
      partiallyFollowedCount: _toInt(json['partially_followed_count']) ?? 0,
      skippedCount: _toInt(json['skipped_count']) ?? 0,
      replacedCount: _toInt(json['replaced_count']) ?? 0,
      extraMealCount: _toInt(json['extra_meal_count']) ?? 0,
      adherencePercentage: _toDouble(json['adherence_percentage']),
      disclaimer:
          _string(json['disclaimer']) ??
          _string(json['safe_disclaimer']) ??
          _string(json['safety_disclaimer']),
      latestCheckin: latestCheckin == null
          ? null
          : CarePlanCheckinModel.fromJson(latestCheckin),
      latestMealLogs: _parseList(
        json['latest_meal_logs'],
      ).map(MealLogModel.fromJson).toList(growable: false),
    );
  }
}

List<Map<String, dynamic>> _parseList(Object? value) {
  if (value is! List) return const [];
  return value
      .whereType<Map>()
      .map((item) => item.map((key, value) => MapEntry(key.toString(), value)))
      .toList(growable: false);
}

int? _toInt(Object? value) {
  if (value == null) return null;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString());
}

double? _toDouble(Object? value) {
  if (value == null) return null;
  if (value is num) return value.toDouble();
  return double.tryParse(value.toString());
}

String? _string(Object? value) {
  if (value == null) return null;
  final text = value.toString();
  return text.isEmpty ? null : text;
}

Map<String, dynamic>? _asMap(Object? value) {
  if (value is Map<String, dynamic>) return value;
  if (value is Map) {
    return value.map((key, item) => MapEntry(key.toString(), item));
  }
  return null;
}
