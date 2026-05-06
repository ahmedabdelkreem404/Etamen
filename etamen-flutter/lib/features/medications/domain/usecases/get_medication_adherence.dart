import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_adherence.dart';
import 'package:etamen_app/features/medications/domain/repositories/medications_repository.dart';

class GetMedicationAdherence {
  const GetMedicationAdherence(this._repository);

  final MedicationsRepository _repository;

  Future<ApiResult<MedicationAdherence>> call() {
    return _repository.getAdherence();
  }
}
