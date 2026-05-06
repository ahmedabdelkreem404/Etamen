import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/labs/data/datasources/labs_remote_data_source.dart';
import 'package:etamen_app/features/labs/data/models/create_lab_order_request.dart';
import 'package:etamen_app/features/labs/domain/entities/lab.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_order.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_order_payment.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_package.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_result.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_test.dart';
import 'package:etamen_app/features/labs/domain/repositories/labs_repository.dart';

class LabsRepositoryImpl implements LabsRepository {
  const LabsRepositoryImpl(this._remoteDataSource);

  final LabsRemoteDataSource _remoteDataSource;

  @override
  Future<ApiResult<List<Lab>>> getLabs() => _remoteDataSource.getLabs();

  @override
  Future<ApiResult<Lab>> getLab(int labId) => _remoteDataSource.getLab(labId);

  @override
  Future<ApiResult<List<LabTest>>> getTests(int labId) {
    return _remoteDataSource.getTests(labId);
  }

  @override
  Future<ApiResult<List<LabPackage>>> getPackages(int labId) {
    return _remoteDataSource.getPackages(labId);
  }

  @override
  Future<ApiResult<LabOrder>> createOrder(CreateLabOrderRequest request) {
    return _remoteDataSource.createOrder(request);
  }

  @override
  Future<ApiResult<List<LabOrder>>> getMyOrders() {
    return _remoteDataSource.getMyOrders();
  }

  @override
  Future<ApiResult<LabOrder>> getOrderDetails(int orderId) {
    return _remoteDataSource.getOrderDetails(orderId);
  }

  @override
  Future<ApiResult<LabOrderPayment>> createOrderPayment(int orderId) {
    return _remoteDataSource.createOrderPayment(orderId);
  }

  @override
  Future<ApiResult<LabResultDownload>> downloadResult(int resultId) {
    return _remoteDataSource.downloadResult(resultId);
  }
}
