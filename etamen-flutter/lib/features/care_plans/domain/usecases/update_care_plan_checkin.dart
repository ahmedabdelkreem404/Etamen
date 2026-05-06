import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/care_plans/data/models/create_care_plan_checkin_request.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_checkin.dart';
import 'package:etamen_app/features/care_plans/domain/repositories/care_plans_repository.dart';

class UpdateCarePlanCheckin {
  const UpdateCarePlanCheckin(this._repository);

  final CarePlansRepository _repository;

  Future<ApiResult<CarePlanCheckin>> call(
    int planId,
    int checkinId,
    CreateCarePlanCheckinRequest request,
  ) {
    return _repository.updateCheckin(planId, checkinId, request);
  }
}
