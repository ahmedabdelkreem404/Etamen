import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/payments/data/datasources/payments_remote_data_source.dart';
import 'package:etamen_app/features/payments/data/models/upload_payment_proof_request.dart';
import 'package:etamen_app/features/payments/domain/entities/manual_payment_selection.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_method.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/payments/domain/entities/paymob_session.dart';
import 'package:etamen_app/features/payments/domain/repositories/payments_repository.dart';

class PaymentsRepositoryImpl implements PaymentsRepository {
  const PaymentsRepositoryImpl(this._remoteDataSource);

  final PaymentsRemoteDataSource _remoteDataSource;

  @override
  Future<ApiResult<List<PaymentMethod>>> getPaymentMethods() {
    return _remoteDataSource.getPaymentMethods();
  }

  @override
  Future<ApiResult<PaymentStatusDetails>> getPaymentStatus(int paymentId) {
    return _remoteDataSource.getPaymentStatus(paymentId);
  }

  @override
  Future<ApiResult<ManualPaymentSelection>> selectManualMethod({
    required int paymentId,
    required int paymentMethodId,
  }) {
    return _remoteDataSource.selectManualMethod(
      paymentId: paymentId,
      paymentMethodId: paymentMethodId,
    );
  }

  @override
  Future<ApiResult<PaymentStatusDetails>> uploadProof({
    required int paymentId,
    required UploadPaymentProofRequest request,
  }) {
    return _remoteDataSource.uploadProof(
      paymentId: paymentId,
      request: request,
    );
  }

  @override
  Future<ApiResult<PaymobSession>> createPaymobSession(int paymentId) {
    return _remoteDataSource.createPaymobSession(paymentId);
  }
}
