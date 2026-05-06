import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_order_payment.dart';
import 'package:etamen_app/features/pharmacy/domain/repositories/pharmacy_repository.dart';

class CreatePharmacyOrderPayment {
  const CreatePharmacyOrderPayment(this._repository);

  final PharmacyRepository _repository;

  Future<ApiResult<PharmacyOrderPayment>> call(int orderId) {
    return _repository.createOrderPayment(orderId);
  }
}
