import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy.dart';
import 'package:etamen_app/features/pharmacy/domain/repositories/pharmacy_repository.dart';

class GetPharmacies {
  const GetPharmacies(this._repository);

  final PharmacyRepository _repository;

  Future<ApiResult<List<Pharmacy>>> call() {
    return _repository.getPharmacies();
  }
}
