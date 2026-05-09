import 'dart:io';

import 'package:dio/dio.dart';
import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/network/api_client.dart';
import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/radiology/data/models/create_radiology_order_request.dart';
import 'package:etamen_app/features/radiology/data/models/radiology_json_helpers.dart';
import 'package:etamen_app/features/radiology/data/models/radiology_order_model.dart';
import 'package:etamen_app/features/radiology/data/models/radiology_result_model.dart';
import 'package:etamen_app/features/radiology/data/models/radiology_scan_category_model.dart';
import 'package:etamen_app/features/radiology/data/models/radiology_scan_model.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_result.dart';

class RadiologyRemoteDataSource {
  const RadiologyRemoteDataSource(this._client);

  final ApiClient _client;

  Future<ApiResult<List<RadiologyScanCategoryModel>>> getCategories() {
    return _client.get<List<RadiologyScanCategoryModel>>(
      ApiEndpoints.radiologyScanCategories,
      queryParameters: const {'per_page': 100},
      parser: (raw) => radiologyList(raw)
          .map(RadiologyScanCategoryModel.fromJson)
          .where((item) => item.isActive)
          .toList(growable: false),
    );
  }

  Future<ApiResult<List<RadiologyScanModel>>> getScans({int? categoryId}) {
    return _client.get<List<RadiologyScanModel>>(
      ApiEndpoints.radiologyScans,
      queryParameters: {
        'per_page': 50,
        if (categoryId != null) 'category_id': categoryId,
      },
      parser: (raw) => radiologyList(raw)
          .map(RadiologyScanModel.fromJson)
          .where((item) => item.isActive)
          .toList(growable: false),
    );
  }

  Future<ApiResult<RadiologyOrderModel>> createOrder(
    CreateRadiologyOrderRequest request,
  ) {
    return _client.post<RadiologyOrderModel>(
      ApiEndpoints.radiologyOrders,
      data: request.toJson(),
      parser: (raw) => RadiologyOrderModel.fromJson(unwrapRadiologyMap(raw)),
    );
  }

  Future<ApiResult<List<RadiologyOrderModel>>> getMyOrders() {
    return _client.get<List<RadiologyOrderModel>>(
      ApiEndpoints.radiologyOrders,
      queryParameters: const {'per_page': 20},
      parser: (raw) => radiologyList(
        raw,
      ).map(RadiologyOrderModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<RadiologyOrderModel>> getOrderDetails(int orderId) {
    return _client.get<RadiologyOrderModel>(
      ApiEndpoints.radiologyOrder(orderId),
      parser: (raw) => RadiologyOrderModel.fromJson(unwrapRadiologyMap(raw)),
    );
  }

  Future<ApiResult<List<RadiologyResultModel>>> getOrderResults(int orderId) {
    return _client.get<List<RadiologyResultModel>>(
      ApiEndpoints.radiologyOrderResults(orderId),
      parser: (raw) => radiologyList(raw)
          .map(RadiologyResultModel.fromJson)
          .where((item) => item.isVisibleToPatient)
          .toList(growable: false),
    );
  }

  Future<ApiResult<RadiologyResultDownload>> downloadResult(
    int resultId,
  ) async {
    try {
      final response = await _client.rawDio.get<List<int>>(
        ApiEndpoints.radiologyResultDownload(resultId),
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
        'etamen_radiology_results_',
      );
      final file = File(
        '${directory.path}${Platform.pathSeparator}radiology_result_$resultId.pdf',
      );
      await file.writeAsBytes(bytes, flush: true);
      return ApiSuccess(
        RadiologyResultDownload(resultId: resultId, localPath: file.path),
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
}
