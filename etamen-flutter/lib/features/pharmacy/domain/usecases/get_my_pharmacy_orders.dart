import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_order.dart';
import 'package:etamen_app/features/pharmacy/domain/repositories/pharmacy_repository.dart';

class GetMyPharmacyOrders {
  const GetMyPharmacyOrders(this._repository);

  final PharmacyRepository _repository;

  Future<ApiResult<List<PharmacyOrder>>> call() {
    return _repository.getMyOrders();
  }
}
