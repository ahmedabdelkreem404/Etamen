import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_checkin.dart';
import 'package:etamen_app/features/care_plans/domain/repositories/care_plans_repository.dart';

class GetCarePlanCheckins {
  const GetCarePlanCheckins(this._repository);

  final CarePlansRepository _repository;

  Future<ApiResult<List<CarePlanCheckin>>> call(int planId) {
    return _repository.getCheckins(planId);
  }
}
