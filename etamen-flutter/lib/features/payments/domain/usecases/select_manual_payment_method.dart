import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/payments/domain/entities/manual_payment_selection.dart';
import 'package:etamen_app/features/payments/domain/repositories/payments_repository.dart';

class SelectManualPaymentMethod {
  const SelectManualPaymentMethod(this._repository);

  final PaymentsRepository _repository;

  Future<ApiResult<ManualPaymentSelection>> call({
    required int paymentId,
    required int paymentMethodId,
  }) {
    return _repository.selectManualMethod(
      paymentId: paymentId,
      paymentMethodId: paymentMethodId,
    );
  }
}
