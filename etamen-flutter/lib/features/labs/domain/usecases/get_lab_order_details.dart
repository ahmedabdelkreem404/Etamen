import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_order.dart';
import 'package:etamen_app/features/labs/domain/repositories/labs_repository.dart';

class GetLabOrderDetails {
  const GetLabOrderDetails(this._repository);

  final LabsRepository _repository;

  Future<ApiResult<LabOrder>> call(int orderId) {
    return _repository.getOrderDetails(orderId);
  }
}
