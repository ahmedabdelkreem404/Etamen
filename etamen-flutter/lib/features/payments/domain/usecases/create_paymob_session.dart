import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/payments/domain/entities/paymob_session.dart';
import 'package:etamen_app/features/payments/domain/repositories/payments_repository.dart';

class CreatePaymobSession {
  const CreatePaymobSession(this._repository);

  final PaymentsRepository _repository;

  Future<ApiResult<PaymobSession>> call(int paymentId) {
    return _repository.createPaymobSession(paymentId);
  }
}
