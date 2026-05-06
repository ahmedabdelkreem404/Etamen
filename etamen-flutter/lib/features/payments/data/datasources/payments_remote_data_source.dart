import 'package:dio/dio.dart';
import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/network/api_client.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/payments/data/models/manual_payment_selection_model.dart';
import 'package:etamen_app/features/payments/data/models/payment_method_model.dart';
import 'package:etamen_app/features/payments/data/models/payment_status_model.dart';
import 'package:etamen_app/features/payments/data/models/paymob_session_model.dart';
import 'package:etamen_app/features/payments/data/models/upload_payment_proof_request.dart';

abstract class PaymentsRemoteDataSource {
  Future<ApiResult<List<PaymentMethodModel>>> getPaymentMethods();

  Future<ApiResult<PaymentStatusModel>> getPaymentStatus(int paymentId);

  Future<ApiResult<ManualPaymentSelectionModel>> selectManualMethod({
    required int paymentId,
    required int paymentMethodId,
  });

  Future<ApiResult<PaymentStatusModel>> uploadProof({
    required int paymentId,
    required UploadPaymentProofRequest request,
  });

  Future<ApiResult<PaymobSessionModel>> createPaymobSession(int paymentId);
}

class PaymentsRemoteDataSourceImpl implements PaymentsRemoteDataSource {
  const PaymentsRemoteDataSourceImpl(this._client);

  final ApiClient _client;

  @override
  Future<ApiResult<List<PaymentMethodModel>>> getPaymentMethods() {
    return _client.get<List<PaymentMethodModel>>(
      ApiEndpoints.paymentMethods,
      parser: (raw) => _parseList(
        raw,
      ).map(PaymentMethodModel.fromJson).toList(growable: false),
    );
  }

  @override
  Future<ApiResult<PaymentStatusModel>> getPaymentStatus(int paymentId) {
    return _client.get<PaymentStatusModel>(
      ApiEndpoints.paymentStatus(paymentId),
      parser: (raw) => PaymentStatusModel.fromJson(_unwrapMap(raw)),
    );
  }

  @override
  Future<ApiResult<ManualPaymentSelectionModel>> selectManualMethod({
    required int paymentId,
    required int paymentMethodId,
  }) {
    return _client.post<ManualPaymentSelectionModel>(
      ApiEndpoints.manualPaymentSelect(paymentId),
      data: {'payment_method_id': paymentMethodId},
      parser: (raw) =>
          ManualPaymentSelectionModel.fromPaymentJson(_unwrapMap(raw)),
    );
  }

  @override
  Future<ApiResult<PaymentStatusModel>> uploadProof({
    required int paymentId,
    required UploadPaymentProofRequest request,
  }) async {
    final formData = FormData.fromMap({
      'file': await MultipartFile.fromFile(
        request.filePath,
        filename: request.fileName,
      ),
      if (request.referenceNumber?.isNotEmpty == true)
        'reference_number': request.referenceNumber,
      if (request.senderPhone?.isNotEmpty == true)
        'sender_phone': request.senderPhone,
      if (request.notes?.isNotEmpty == true) 'notes': request.notes,
    });

    final upload = await _client.multipart<Object?>(
      ApiEndpoints.paymentProofs(paymentId),
      formData: formData,
      parser: (raw) => raw,
    );

    return upload.when(
      success: (_) => getPaymentStatus(paymentId),
      failure: (failure) => ApiFailure<PaymentStatusModel>(failure.error),
    );
  }

  @override
  Future<ApiResult<PaymobSessionModel>> createPaymobSession(int paymentId) {
    return _client.post<PaymobSessionModel>(
      ApiEndpoints.paymobCreateSession(paymentId),
      parser: (raw) => PaymobSessionModel.fromJson(_unwrapMap(raw)),
    );
  }

  static List<Map<String, dynamic>> _parseList(Object? raw) {
    final value = _unwrapCollection(raw);
    if (value is! List) return const [];

    return value
        .whereType<Map>()
        .map(
          (item) => item.map((key, value) => MapEntry(key.toString(), value)),
        )
        .toList(growable: false);
  }

  static Object? _unwrapCollection(Object? raw) {
    if (raw is Map) {
      return raw['data'] ?? raw['items'] ?? raw['methods'] ?? raw['results'];
    }
    return raw;
  }

  static Map<String, dynamic> _unwrapMap(Object? raw) {
    if (raw is Map<String, dynamic>) {
      final nested =
          raw['payment'] ??
          raw['data'] ??
          raw['status'] ??
          raw['session'] ??
          raw['paymob'];
      if (nested is Map<String, dynamic>) return nested;
      if (nested is Map) {
        return nested.map((key, value) => MapEntry(key.toString(), value));
      }
      return raw;
    }
    if (raw is Map) {
      return raw.map((key, value) => MapEntry(key.toString(), value));
    }
    return const {};
  }
}
