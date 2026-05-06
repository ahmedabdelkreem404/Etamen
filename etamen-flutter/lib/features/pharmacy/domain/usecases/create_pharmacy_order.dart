import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/pharmacy/data/models/create_pharmacy_order_request.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_order.dart';
import 'package:etamen_app/features/pharmacy/domain/repositories/pharmacy_repository.dart';

class CreatePharmacyOrder {
  const CreatePharmacyOrder(this._repository);

  final PharmacyRepository _repository;

  Future<ApiResult<PharmacyOrder>> call(CreatePharmacyOrderRequest request) {
    return _repository.createOrder(request);
  }
}
