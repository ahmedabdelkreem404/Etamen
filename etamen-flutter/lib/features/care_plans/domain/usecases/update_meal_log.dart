import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/care_plans/data/models/create_meal_log_request.dart';
import 'package:etamen_app/features/care_plans/domain/entities/meal_log.dart';
import 'package:etamen_app/features/care_plans/domain/repositories/care_plans_repository.dart';

class UpdateMealLog {
  const UpdateMealLog(this._repository);

  final CarePlansRepository _repository;

  Future<ApiResult<MealLog>> call(
    int planId,
    int logId,
    CreateMealLogRequest request,
  ) {
    return _repository.updateMealLog(planId, logId, request);
  }
}
