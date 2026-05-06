import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/labs/data/models/create_lab_order_request.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_order.dart';
import 'package:etamen_app/features/labs/domain/repositories/labs_repository.dart';

class CreateLabOrder {
  const CreateLabOrder(this._repository);

  final LabsRepository _repository;

  Future<ApiResult<LabOrder>> call(CreateLabOrderRequest request) {
    return _repository.createOrder(request);
  }
}
