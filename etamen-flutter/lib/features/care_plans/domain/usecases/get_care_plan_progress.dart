import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_progress.dart';
import 'package:etamen_app/features/care_plans/domain/repositories/care_plans_repository.dart';

class GetCarePlanProgress {
  const GetCarePlanProgress(this._repository);

  final CarePlansRepository _repository;

  Future<ApiResult<CarePlanProgress>> call(int planId) {
    return _repository.getProgress(planId);
  }
}
