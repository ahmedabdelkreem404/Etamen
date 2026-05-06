import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/care_plans/domain/entities/meal_log.dart';
import 'package:etamen_app/features/care_plans/domain/repositories/care_plans_repository.dart';

class GetMealLogs {
  const GetMealLogs(this._repository);

  final CarePlansRepository _repository;

  Future<ApiResult<List<MealLog>>> call(int planId) {
    return _repository.getMealLogs(planId);
  }
}
