import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_instruction.dart';
import 'package:etamen_app/features/care_plans/domain/repositories/care_plans_repository.dart';

class GetCarePlanInstructions {
  const GetCarePlanInstructions(this._repository);

  final CarePlansRepository _repository;

  Future<ApiResult<List<CarePlanInstruction>>> call(int planId) {
    return _repository.getInstructions(planId);
  }
}
