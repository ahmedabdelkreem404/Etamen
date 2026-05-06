import 'package:etamen_app/core/network/api_result.dart';
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

abstract class CarePlansRepository {
  Future<ApiResult<List<CarePlan>>> getCarePlans({CarePlanStatus? status});

  Future<ApiResult<CarePlan>> getCarePlan(int planId);

  Future<ApiResult<CarePlan>> createCarePlan(CreateCarePlanRequest request);

  Future<ApiResult<List<CarePlanDay>>> getDays(int planId);

  Future<ApiResult<List<CarePlanMeal>>> getMeals(int planId);

  Future<ApiResult<List<CarePlanFoodItem>>> getFoods(int planId);

  Future<ApiResult<List<CarePlanInstruction>>> getInstructions(int planId);

  Future<ApiResult<List<CarePlanCheckin>>> getCheckins(int planId);

  Future<ApiResult<CarePlanCheckin>> createCheckin(
    int planId,
    CreateCarePlanCheckinRequest request,
  );

  Future<ApiResult<CarePlanCheckin>> updateCheckin(
    int planId,
    int checkinId,
    CreateCarePlanCheckinRequest request,
  );

  Future<ApiResult<List<MealLog>>> getMealLogs(int planId);

  Future<ApiResult<MealLog>> createMealLog(
    int planId,
    CreateMealLogRequest request,
  );

  Future<ApiResult<MealLog>> updateMealLog(
    int planId,
    int logId,
    CreateMealLogRequest request,
  );

  Future<ApiResult<CarePlanProgress>> getProgress(int planId);

  Future<ApiResult<List<CarePlan>>> getSummaryPlans();
}
