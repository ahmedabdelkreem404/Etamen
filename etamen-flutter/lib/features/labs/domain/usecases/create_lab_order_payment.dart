import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_order_payment.dart';
import 'package:etamen_app/features/labs/domain/repositories/labs_repository.dart';

class CreateLabOrderPayment {
  const CreateLabOrderPayment(this._repository);

  final LabsRepository _repository;

  Future<ApiResult<LabOrderPayment>> call(int orderId) {
    return _repository.createOrderPayment(orderId);
  }
}
