import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/care_plans/data/datasources/care_plans_remote_data_source.dart';
import 'package:etamen_app/features/care_plans/data/models/create_care_plan_checkin_request.dart';
import 'package:etamen_app/features/care_plans/data/models/create_care_plan_request.dart';
import 'package:etamen_app/features/care_plans/data/models/create_meal_log_request.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_checkin.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_day.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_food_item.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_instruction.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_meal.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_progress.dart';
import 'package:etamen_app/features/care_plans/domain/entities/meal_log.dart';
import 'package:etamen_app/features/care_plans/domain/repositories/care_plans_repository.dart';

class CarePlansRepositoryImpl implements CarePlansRepository {
  const CarePlansRepositoryImpl(this._remote);

  final CarePlansRemoteDataSource _remote;

  @override
  Future<ApiResult<List<CarePlan>>> getCarePlans({CarePlanStatus? status}) {
    return _remote.getCarePlans(status: status);
  }

  @override
  Future<ApiResult<CarePlan>> getCarePlan(int planId) {
    return _remote.getCarePlan(planId);
  }

  @override
  Future<ApiResult<CarePlan>> createCarePlan(CreateCarePlanRequest request) {
    return _remote.createCarePlan(request);
  }

  @override
  Future<ApiResult<List<CarePlanDay>>> getDays(int planId) {
    return _remote.getDays(planId);
  }

  @override
  Future<ApiResult<List<CarePlanMeal>>> getMeals(int planId) {
    return _remote.getMeals(planId);
  }

  @override
  Future<ApiResult<List<CarePlanFoodItem>>> getFoods(int planId) {
    return _remote.getFoods(planId);
  }

  @override
  Future<ApiResult<List<CarePlanInstruction>>> getInstructions(int planId) {
    return _remote.getInstructions(planId);
  }

  @override
  Future<ApiResult<List<CarePlanCheckin>>> getCheckins(int planId) {
    return _remote.getCheckins(planId);
  }

  @override
  Future<ApiResult<CarePlanCheckin>> createCheckin(
    int planId,
    CreateCarePlanCheckinRequest request,
  ) {
    return _remote.createCheckin(planId, request);
  }

  @override
  Future<ApiResult<CarePlanCheckin>> updateCheckin(
    int planId,
    int checkinId,
    CreateCarePlanCheckinRequest request,
  ) {
    return _remote.updateCheckin(planId, checkinId, request);
  }

  @override
  Future<ApiResult<List<MealLog>>> getMealLogs(int planId) {
    return _remote.getMealLogs(planId);
  }

  @override
  Future<ApiResult<MealLog>> createMealLog(
    int planId,
    CreateMealLogRequest request,
  ) {
    return _remote.createMealLog(planId, request);
  }

  @override
  Future<ApiResult<MealLog>> updateMealLog(
    int planId,
    int logId,
    CreateMealLogRequest request,
  ) {
    return _remote.updateMealLog(planId, logId, request);
  }

  @override
  Future<ApiResult<CarePlanProgress>> getProgress(int planId) {
    return _remote.getProgress(planId);
  }

  @override
  Future<ApiResult<List<CarePlan>>> getSummaryPlans() {
    return _remote.getSummaryPlans();
  }
}
