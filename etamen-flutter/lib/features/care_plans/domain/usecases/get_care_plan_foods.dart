import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_food_item.dart';
import 'package:etamen_app/features/care_plans/domain/repositories/care_plans_repository.dart';

class GetCarePlanFoods {
  const GetCarePlanFoods(this._repository);

  final CarePlansRepository _repository;

  Future<ApiResult<List<CarePlanFoodItem>>> call(int planId) {
    return _repository.getFoods(planId);
  }
}
