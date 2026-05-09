import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/radiology/data/models/create_radiology_order_request.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_order.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_result.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_scan.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_scan_category.dart';

abstract class RadiologyRepository {
  Future<ApiResult<List<RadiologyScanCategory>>> getCategories();

  Future<ApiResult<List<RadiologyScan>>> getScans({int? categoryId});

  Future<ApiResult<RadiologyOrder>> createOrder(
    CreateRadiologyOrderRequest request,
  );

  Future<ApiResult<List<RadiologyOrder>>> getMyOrders();

  Future<ApiResult<RadiologyOrder>> getOrderDetails(int orderId);

  Future<ApiResult<List<RadiologyResult>>> getOrderResults(int orderId);

  Future<ApiResult<RadiologyResultDownload>> downloadResult(int resultId);
}
