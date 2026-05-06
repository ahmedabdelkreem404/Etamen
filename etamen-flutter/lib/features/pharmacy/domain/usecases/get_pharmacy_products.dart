import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_product.dart';
import 'package:etamen_app/features/pharmacy/domain/repositories/pharmacy_repository.dart';

class GetPharmacyProducts {
  const GetPharmacyProducts(this._repository);

  final PharmacyRepository _repository;

  Future<ApiResult<List<PharmacyProduct>>> call(int pharmacyId) {
    return _repository.getProducts(pharmacyId);
  }
}
