import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_meal.dart';
import 'package:etamen_app/features/care_plans/domain/repositories/care_plans_repository.dart';

class GetCarePlanMeals {
  const GetCarePlanMeals(this._repository);

  final CarePlansRepository _repository;

  Future<ApiResult<List<CarePlanMeal>>> call(int planId) {
    return _repository.getMeals(planId);
  }
}
