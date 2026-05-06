import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan.dart';
import 'package:etamen_app/features/care_plans/domain/repositories/care_plans_repository.dart';

class GetCarePlanDetails {
  const GetCarePlanDetails(this._repository);

  final CarePlansRepository _repository;

  Future<ApiResult<CarePlan>> call(int planId) {
    return _repository.getCarePlan(planId);
  }
}
