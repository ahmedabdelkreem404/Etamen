import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_refill_event.dart';
import 'package:etamen_app/features/medications/domain/repositories/medications_repository.dart';

class GetRefillEvents {
  const GetRefillEvents(this._repository);

  final MedicationsRepository _repository;

  Future<ApiResult<List<MedicationRefillEvent>>> call() {
    return _repository.getRefills();
  }
}
