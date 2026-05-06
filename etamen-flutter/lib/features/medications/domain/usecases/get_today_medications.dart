import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_schedule_item.dart';
import 'package:etamen_app/features/medications/domain/repositories/medications_repository.dart';

class GetTodayMedications {
  const GetTodayMedications(this._repository);

  final MedicationsRepository _repository;

  Future<ApiResult<List<MedicationScheduleItem>>> call() {
    return _repository.getToday();
  }
}
