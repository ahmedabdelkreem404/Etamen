import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan.dart';
import 'package:etamen_app/features/care_plans/domain/repositories/care_plans_repository.dart';

class GetCarePlansSummary {
  const GetCarePlansSummary(this._repository);

  final CarePlansRepository _repository;

  Future<ApiResult<List<CarePlan>>> call() {
    return _repository.getSummaryPlans();
  }
}
