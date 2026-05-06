import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/care_plans/data/models/create_meal_log_request.dart';
import 'package:etamen_app/features/care_plans/domain/entities/meal_log.dart';
import 'package:etamen_app/features/care_plans/domain/repositories/care_plans_repository.dart';

class CreateMealLog {
  const CreateMealLog(this._repository);

  final CarePlansRepository _repository;

  Future<ApiResult<MealLog>> call(int planId, CreateMealLogRequest request) {
    return _repository.createMealLog(planId, request);
  }
}
