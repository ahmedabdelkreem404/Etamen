import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_method.dart';
import 'package:etamen_app/features/payments/domain/repositories/payments_repository.dart';

class GetPaymentMethods {
  const GetPaymentMethods(this._repository);

  final PaymentsRepository _repository;

  Future<ApiResult<List<PaymentMethod>>> call() {
    return _repository.getPaymentMethods();
  }
}
