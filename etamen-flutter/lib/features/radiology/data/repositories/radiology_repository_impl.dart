import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/radiology/data/datasources/radiology_remote_data_source.dart';
import 'package:etamen_app/features/radiology/data/models/create_radiology_order_request.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_order.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_result.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_scan.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_scan_category.dart';
import 'package:etamen_app/features/radiology/domain/repositories/radiology_repository.dart';

class RadiologyRepositoryImpl implements RadiologyRepository {
  const RadiologyRepositoryImpl(this._remoteDataSource);

  final RadiologyRemoteDataSource _remoteDataSource;

  @override
  Future<ApiResult<List<RadiologyScanCategory>>> getCategories() {
    return _remoteDataSource.getCategories();
  }

  @override
  Future<ApiResult<List<RadiologyScan>>> getScans({int? categoryId}) {
    return _remoteDataSource.getScans(categoryId: categoryId);
  }

  @override
  Future<ApiResult<RadiologyOrder>> createOrder(
    CreateRadiologyOrderRequest request,
  ) {
    return _remoteDataSource.createOrder(request);
  }

  @override
  Future<ApiResult<List<RadiologyOrder>>> getMyOrders() {
    return _remoteDataSource.getMyOrders();
  }

  @override
  Future<ApiResult<RadiologyOrder>> getOrderDetails(int orderId) {
    return _remoteDataSource.getOrderDetails(orderId);
  }

  @override
  Future<ApiResult<List<RadiologyResult>>> getOrderResults(int orderId) {
    return _remoteDataSource.getOrderResults(orderId);
  }

  @override
  Future<ApiResult<RadiologyResultDownload>> downloadResult(int resultId) {
    return _remoteDataSource.downloadResult(resultId);
  }
}
