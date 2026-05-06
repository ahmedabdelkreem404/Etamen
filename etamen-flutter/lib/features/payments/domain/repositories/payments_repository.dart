import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/payments/data/models/upload_payment_proof_request.dart';
import 'package:etamen_app/features/payments/domain/entities/manual_payment_selection.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_method.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/payments/domain/entities/paymob_session.dart';

abstract class PaymentsRepository {
  Future<ApiResult<List<PaymentMethod>>> getPaymentMethods();

  Future<ApiResult<PaymentStatusDetails>> getPaymentStatus(int paymentId);

  Future<ApiResult<ManualPaymentSelection>> selectManualMethod({
    required int paymentId,
    required int paymentMethodId,
  });

  Future<ApiResult<PaymentStatusDetails>> uploadProof({
    required int paymentId,
    required UploadPaymentProofRequest request,
  });

  Future<ApiResult<PaymobSession>> createPaymobSession(int paymentId);
}
