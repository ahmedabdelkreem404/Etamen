import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/network/api_client.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/care_plans/data/models/care_plan_checkin_model.dart';
import 'package:etamen_app/features/care_plans/data/models/care_plan_day_model.dart';
import 'package:etamen_app/features/care_plans/data/models/care_plan_food_item_model.dart';
import 'package:etamen_app/features/care_plans/data/models/care_plan_instruction_model.dart';
import 'package:etamen_app/features/care_plans/data/models/care_plan_meal_model.dart';
import 'package:etamen_app/features/care_plans/data/models/care_plan_model.dart';
import 'package:etamen_app/features/care_plans/data/models/care_plan_progress_model.dart';
import 'package:etamen_app/features/care_plans/data/models/create_care_plan_checkin_request.dart';
import 'package:etamen_app/features/care_plans/data/models/create_care_plan_request.dart';
import 'package:etamen_app/features/care_plans/data/models/create_meal_log_request.dart';
import 'package:etamen_app/features/care_plans/data/models/meal_log_model.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan.dart';

class CarePlansRemoteDataSource {
  const CarePlansRemoteDataSource(this._client);

  final ApiClient _client;

  Future<ApiResult<List<CarePlanModel>>> getCarePlans({
    CarePlanStatus? status,
  }) {
    return _client.get<List<CarePlanModel>>(
      ApiEndpoints.carePlans,
      queryParameters: {
        'per_page': 20,
        if (status != null && status != CarePlanStatus.unknown)
          'status': status.wireValue,
      },
      parser: (raw) =>
          _parseList(raw).map(CarePlanModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<CarePlanModel>> getCarePlan(int planId) {
    return _client.get<CarePlanModel>(
      ApiEndpoints.carePlan(planId),
      parser: (raw) => CarePlanModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<CarePlanModel>> createCarePlan(
    CreateCarePlanRequest request,
  ) {
    return _client.post<CarePlanModel>(
      ApiEndpoints.carePlans,
      data: request.toJson(),
      parser: (raw) => CarePlanModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<List<CarePlanDayModel>>> getDays(int planId) {
    return _client.get<List<CarePlanDayModel>>(
      ApiEndpoints.carePlanDays(planId),
      parser: (raw) => _parseList(
        raw,
      ).map(CarePlanDayModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<List<CarePlanMealModel>>> getMeals(int planId) {
    return _client.get<List<CarePlanMealModel>>(
      ApiEndpoints.carePlanMeals(planId),
      parser: (raw) => _parseList(
        raw,
      ).map(CarePlanMealModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<List<CarePlanFoodItemModel>>> getFoods(int planId) {
    return _client.get<List<CarePlanFoodItemModel>>(
      ApiEndpoints.carePlanFoods(planId),
      parser: (raw) => _parseList(
        raw,
      ).map(CarePlanFoodItemModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<List<CarePlanInstructionModel>>> getInstructions(
    int planId,
  ) {
    return _client.get<List<CarePlanInstructionModel>>(
      ApiEndpoints.carePlanInstructions(planId),
      parser: (raw) => _parseList(
        raw,
      ).map(CarePlanInstructionModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<List<CarePlanCheckinModel>>> getCheckins(int planId) {
    return _client.get<List<CarePlanCheckinModel>>(
      ApiEndpoints.carePlanCheckins(planId),
      queryParameters: const {'per_page': 20},
      parser: (raw) => _parseList(
        raw,
      ).map(CarePlanCheckinModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<CarePlanCheckinModel>> createCheckin(
    int planId,
    CreateCarePlanCheckinRequest request,
  ) {
    return _client.post<CarePlanCheckinModel>(
      ApiEndpoints.carePlanCheckins(planId),
      data: request.toJson(),
      parser: (raw) => CarePlanCheckinModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<CarePlanCheckinModel>> updateCheckin(
    int planId,
    int checkinId,
    CreateCarePlanCheckinRequest request,
  ) {
    return _client.put<CarePlanCheckinModel>(
      ApiEndpoints.carePlanCheckin(planId, checkinId),
      data: request.toJson(),
      parser: (raw) => CarePlanCheckinModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<List<MealLogModel>>> getMealLogs(int planId) {
    return _client.get<List<MealLogModel>>(
      ApiEndpoints.carePlanMealLogs(planId),
      queryParameters: const {'per_page': 20},
      parser: (raw) =>
          _parseList(raw).map(MealLogModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<MealLogModel>> createMealLog(
    int planId,
    CreateMealLogRequest request,
  ) {
    return _client.post<MealLogModel>(
      ApiEndpoints.carePlanMealLogs(planId),
      data: request.toJson(),
      parser: (raw) => MealLogModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<MealLogModel>> updateMealLog(
    int planId,
    int logId,
    CreateMealLogRequest request,
  ) {
    return _client.put<MealLogModel>(
      ApiEndpoints.carePlanMealLog(planId, logId),
      data: request.toJson(),
      parser: (raw) => MealLogModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<CarePlanProgressModel>> getProgress(int planId) {
    return _client.get<CarePlanProgressModel>(
      ApiEndpoints.carePlanProgress(planId),
      parser: (raw) => CarePlanProgressModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<List<CarePlanModel>>> getSummaryPlans() {
    return _client.get<List<CarePlanModel>>(
      ApiEndpoints.carePlansSummary,
      parser: (raw) {
        final map = _unwrapMap(raw);
        return _parseList(
          map['plans'],
        ).map(CarePlanModel.fromJson).toList(growable: false);
      },
    );
  }

  static List<Map<String, dynamic>> _parseList(Object? raw) {
    final value = _unwrapCollection(raw);
    if (value is! List) return const [];
    return value
        .whereType<Map>()
        .map(
          (item) => item.map((key, value) => MapEntry(key.toString(), value)),
        )
        .toList(growable: false);
  }

  static Object? _unwrapCollection(Object? raw) {
    if (raw is Map) {
      return raw['data'] ??
          raw['items'] ??
          raw['plans'] ??
          raw['days'] ??
          raw['meals'] ??
          raw['foods'] ??
          raw['instructions'] ??
          raw['checkins'] ??
          raw['meal_logs'] ??
          raw['logs'];
    }
    return raw;
  }

  static Map<String, dynamic> _unwrapMap(Object? raw) {
    if (raw is Map<String, dynamic>) {
      final nested =
          raw['data'] ??
          raw['plan'] ??
          raw['care_plan'] ??
          raw['checkin'] ??
          raw['meal_log'] ??
          raw['log'] ??
          raw['progress'];
      if (nested is Map<String, dynamic>) return nested;
      if (nested is Map) {
        return nested.map((key, value) => MapEntry(key.toString(), value));
      }
      return raw;
    }
    if (raw is Map) {
      return raw.map((key, value) => MapEntry(key.toString(), value));
    }
    return const {};
  }
}
