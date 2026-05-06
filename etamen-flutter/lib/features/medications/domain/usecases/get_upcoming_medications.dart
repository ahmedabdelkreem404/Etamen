import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_schedule_item.dart';
import 'package:etamen_app/features/medications/domain/repositories/medications_repository.dart';

class GetUpcomingMedications {
  const GetUpcomingMedications(this._repository);

  final MedicationsRepository _repository;

  Future<ApiResult<List<MedicationScheduleItem>>> call({int days = 7}) {
    return _repository.getUpcoming(days: days);
  }
}
