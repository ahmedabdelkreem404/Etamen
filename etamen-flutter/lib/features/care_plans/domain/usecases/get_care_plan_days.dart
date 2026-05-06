import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_day.dart';
import 'package:etamen_app/features/care_plans/domain/repositories/care_plans_repository.dart';

class GetCarePlanDays {
  const GetCarePlanDays(this._repository);

  final CarePlansRepository _repository;

  Future<ApiResult<List<CarePlanDay>>> call(int planId) {
    return _repository.getDays(planId);
  }
}
