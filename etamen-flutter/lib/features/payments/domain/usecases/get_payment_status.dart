import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/payments/domain/repositories/payments_repository.dart';

class GetPaymentStatus {
  const GetPaymentStatus(this._repository);

  final PaymentsRepository _repository;

  Future<ApiResult<PaymentStatusDetails>> call(int paymentId) {
    return _repository.getPaymentStatus(paymentId);
  }
}
