import 'package:etamen_app/features/care_plans/domain/entities/care_plan.dart';

class CarePlanModel extends CarePlan {
  const CarePlanModel({
    required super.id,
    required super.title,
    required super.planType,
    required super.status,
    super.description,
    super.goalText,
    super.startDate,
    super.endDate,
    super.visibility,
    super.source,
    super.providerName,
    super.assignedByName,
    super.safetyDisclaimer,
    super.checkinsCount,
    super.mealLogsCount,
    super.createdAt,
    super.updatedAt,
  });

  factory CarePlanModel.fromJson(Map<String, dynamic> json) {
    return CarePlanModel(
      id: _toInt(json['id']) ?? 0,
      title: _string(json['title']) ?? '',
      planType: CarePlanType.fromWire(json['plan_type']),
      status: CarePlanStatus.fromWire(json['status']),
      description: _string(json['description']),
      goalText: _string(json['goal_text']),
      startDate: _string(json['start_date']),
      endDate: _string(json['end_date']),
      visibility: _string(json['visibility']),
      source: _string(json['source']),
      providerName:
          _string(json['provider_name']) ??
          _string(_asMap(json['provider'])?['name_ar']) ??
          _string(_asMap(json['provider'])?['name']),
      assignedByName:
          _string(json['assigned_by_name']) ??
          _string(_asMap(json['assigned_by'])?['name']),
      safetyDisclaimer:
          _string(json['safety_disclaimer']) ??
          _string(json['safe_disclaimer']),
      checkinsCount: _toInt(json['checkins_count']),
      mealLogsCount: _toInt(json['meal_logs_count']),
      createdAt: _toDateTime(json['created_at']),
      updatedAt: _toDateTime(json['updated_at']),
    );
  }
}

int? _toInt(Object? value) {
  if (value == null) return null;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString());
}

String? _string(Object? value) {
  if (value == null) return null;
  final text = value.toString();
  return text.isEmpty ? null : text;
}

DateTime? _toDateTime(Object? value) {
  final text = _string(value);
  return text == null ? null : DateTime.tryParse(text);
}

Map<String, dynamic>? _asMap(Object? value) {
  if (value is Map<String, dynamic>) return value;
  if (value is Map) {
    return value.map((key, item) => MapEntry(key.toString(), item));
  }
  return null;
}
