import 'dart:io';

import 'package:dio/dio.dart';
import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/network/api_client.dart';
import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/labs/data/models/create_lab_order_request.dart';
import 'package:etamen_app/features/labs/data/models/lab_model.dart';
import 'package:etamen_app/features/labs/data/models/lab_order_model.dart';
import 'package:etamen_app/features/labs/data/models/lab_package_model.dart';
import 'package:etamen_app/features/labs/data/models/lab_test_model.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_order_payment.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_result.dart';

class LabsRemoteDataSource {
  const LabsRemoteDataSource(this._client);

  final ApiClient _client;

  Future<ApiResult<List<LabModel>>> getLabs() {
    return _client.get<List<LabModel>>(
      ApiEndpoints.labs,
      queryParameters: const {'per_page': 20},
      parser: (raw) =>
          _parseList(raw).map(LabModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<LabModel>> getLab(int labId) {
    return _client.get<LabModel>(
      ApiEndpoints.lab(labId),
      parser: (raw) => LabModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<List<LabTestModel>>> getTests(int labId) {
    return _client.get<List<LabTestModel>>(
      ApiEndpoints.labTests(labId),
      queryParameters: const {'per_page': 50},
      parser: (raw) => _parseList(raw)
          .map(LabTestModel.fromJson)
          .where((item) => item.isActive)
          .toList(growable: false),
    );
  }

  Future<ApiResult<List<LabPackageModel>>> getPackages(int labId) {
    return _client.get<List<LabPackageModel>>(
      ApiEndpoints.labPackages(labId),
      queryParameters: const {'per_page': 50},
      parser: (raw) => _parseList(raw)
          .map(LabPackageModel.fromJson)
          .where((item) => item.isActive)
          .toList(growable: false),
    );
  }

  Future<ApiResult<LabOrderModel>> createOrder(CreateLabOrderRequest request) {
    return _client.post<LabOrderModel>(
      ApiEndpoints.labOrders,
      data: request.toJson(),
      parser: (raw) => LabOrderModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<List<LabOrderModel>>> getMyOrders() {
    return _client.get<List<LabOrderModel>>(
      ApiEndpoints.labOrders,
      queryParameters: const {'per_page': 20},
      parser: (raw) =>
          _parseList(raw).map(LabOrderModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<LabOrderModel>> getOrderDetails(int orderId) {
    return _client.get<LabOrderModel>(
      ApiEndpoints.labOrder(orderId),
      parser: (raw) => LabOrderModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<LabOrderPayment>> createOrderPayment(int orderId) {
    return _client.post<LabOrderPayment>(
      ApiEndpoints.labOrderPay(orderId),
      parser: (raw) {
        final map = _asMap(raw) ?? _unwrapMap(raw);
        final orderMap =
            _asMap(map['order']) ?? _asMap(map['lab_order']) ?? map;
        final payment = _asMap(map['payment']);
        final order = LabOrderModel.fromJson(orderMap);
        final paymentId =
            _toInt(map['payment_id'] ?? payment?['id']) ?? order.paymentId;
        return LabOrderPayment(order: order, paymentId: paymentId);
      },
    );
  }

  Future<ApiResult<LabResultDownload>> downloadResult(int resultId) async {
    try {
      final response = await _client.rawDio.get<List<int>>(
        ApiEndpoints.labResultDownload(resultId),
        options: Options(responseType: ResponseType.bytes),
      );
      final bytes = response.data;
      if (bytes == null || bytes.isEmpty) {
        return const ApiFailure(
          ApiError(
            message: 'النتيجة غير متاحة حاليًا',
            type: ApiErrorType.unknown,
          ),
        );
      }

      final directory = await Directory.systemTemp.createTemp(
        'etamen_lab_results_',
      );
      final file = File(
        '${directory.path}${Platform.pathSeparator}lab_result_$resultId.pdf',
      );
      await file.writeAsBytes(bytes, flush: true);
      return ApiSuccess(
        LabResultDownload(resultId: resultId, localPath: file.path),
      );
    } catch (_) {
      return const ApiFailure(
        ApiError(
          message: 'تعذر تحميل النتيجة الآن',
          type: ApiErrorType.network,
        ),
      );
    }
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
          raw['data'] ?? raw['lab'] ?? raw['order'] ?? raw['lab_order'];
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
