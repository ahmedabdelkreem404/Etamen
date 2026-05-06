import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/payments/data/models/upload_payment_proof_request.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/payments/domain/repositories/payments_repository.dart';

class UploadPaymentProof {
  const UploadPaymentProof(this._repository);

  final PaymentsRepository _repository;

  Future<ApiResult<PaymentStatusDetails>> call({
    required int paymentId,
    required UploadPaymentProofRequest request,
  }) {
    return _repository.uploadProof(paymentId: paymentId, request: request);
  }
}
