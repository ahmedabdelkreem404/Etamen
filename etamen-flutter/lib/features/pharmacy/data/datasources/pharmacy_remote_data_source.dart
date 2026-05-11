import 'package:dio/dio.dart';
import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/network/api_client.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/pharmacy/data/models/create_pharmacy_order_request.dart';
import 'package:etamen_app/features/pharmacy/data/models/pharmacy_model.dart';
import 'package:etamen_app/features/pharmacy/data/models/pharmacy_order_model.dart';
import 'package:etamen_app/features/pharmacy/data/models/pharmacy_prescription_model.dart';
import 'package:etamen_app/features/pharmacy/data/models/pharmacy_product_model.dart';
import 'package:etamen_app/features/pharmacy/data/models/upload_prescription_request.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_order_payment.dart';

class PharmacyRemoteDataSource {
  const PharmacyRemoteDataSource(this._client);

  final ApiClient _client;

  Future<ApiResult<List<PharmacyModel>>> getPharmacies() {
    return _client.get<List<PharmacyModel>>(
      ApiEndpoints.pharmacies,
      queryParameters: const {'per_page': 20},
      parser: (raw) =>
          _parseList(raw).map(PharmacyModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<PharmacyModel>> getPharmacy(int pharmacyId) {
    return _client.get<PharmacyModel>(
      ApiEndpoints.pharmacy(pharmacyId),
      parser: (raw) => PharmacyModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<List<PharmacyProductModel>>> getProducts(int pharmacyId) {
    return _client.get<List<PharmacyProductModel>>(
      ApiEndpoints.pharmacyProducts(pharmacyId),
      queryParameters: const {'per_page': 50},
      parser: (raw) => _parseList(raw)
          .map(PharmacyProductModel.fromJson)
          .where((product) => product.isActive)
          .toList(growable: false),
    );
  }

  Future<ApiResult<PharmacyPrescriptionModel>> uploadPrescription(
    UploadPrescriptionRequest request,
  ) async {
    final formData = FormData.fromMap({
      'file': await MultipartFile.fromFile(
        request.filePath,
        filename: request.fileName,
      ),
      if (request.pharmacyId != null)
        'pharmacy_provider_id': request.pharmacyId,
      if (request.notes?.trim().isNotEmpty == true)
        'notes': request.notes!.trim(),
    });

    return _client.multipart<PharmacyPrescriptionModel>(
      ApiEndpoints.pharmacyPrescriptions,
      formData: formData,
      parser: (raw) => PharmacyPrescriptionModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<PharmacyOrderModel>> createOrder(
    CreatePharmacyOrderRequest request,
  ) {
    return _client.post<PharmacyOrderModel>(
      ApiEndpoints.pharmacyOrders,
      data: request.toJson(),
      parser: (raw) => PharmacyOrderModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<List<PharmacyOrderModel>>> getMyOrders() {
    return _client.get<List<PharmacyOrderModel>>(
      ApiEndpoints.pharmacyOrders,
      queryParameters: const {'per_page': 20},
      parser: (raw) => _parseList(
        raw,
      ).map(PharmacyOrderModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<PharmacyOrderModel>> getOrderDetails(int orderId) {
    return _client.get<PharmacyOrderModel>(
      ApiEndpoints.pharmacyOrder(orderId),
      parser: (raw) => PharmacyOrderModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<PharmacyOrderPayment>> createOrderPayment(int orderId) {
    return _client.post<PharmacyOrderPayment>(
      ApiEndpoints.pharmacyOrderPay(orderId),
      parser: (raw) {
        final map = _asMap(raw) ?? _unwrapMap(raw);
        final orderMap =
            _asMap(map['order']) ?? _asMap(map['pharmacy_order']) ?? map;
        final payment = _asMap(map['payment']);
        final order = PharmacyOrderModel.fromJson(orderMap);
        final paymentId =
            _toInt(map['payment_id'] ?? payment?['id']) ?? order.paymentId;
        return PharmacyOrderPayment(order: order, paymentId: paymentId);
      },
    );
  }

  Future<ApiResult<PharmacyOrderModel>> cancelOrder(
    int orderId, {
    String? reason,
  }) {
    final trimmedReason = reason?.trim();
    return _client.post<PharmacyOrderModel>(
      ApiEndpoints.pharmacyOrderCancel(orderId),
      data: {if (trimmedReason?.isNotEmpty == true) 'reason': trimmedReason},
      parser: (raw) => PharmacyOrderModel.fromJson(_unwrapMap(raw)),
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
      return raw['data'] ?? raw['items'] ?? raw['orders'] ?? raw['results'];
    }
    return raw;
  }

  static Map<String, dynamic> _unwrapMap(Object? raw) {
    if (raw is Map<String, dynamic>) {
      final nested =
          raw['data'] ?? raw['pharmacy'] ?? raw['order'] ?? raw['prescription'];
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

  static Map<String, dynamic>? _asMap(Object? value) {
    if (value is Map<String, dynamic>) return value;
    if (value is Map) {
      return value.map((key, item) => MapEntry(key.toString(), item));
    }
    return null;
  }

  static int? _toInt(Object? value) {
    if (value == null) return null;
    if (value is num) return value.toInt();
    return int.tryParse(value.toString());
  }
}
