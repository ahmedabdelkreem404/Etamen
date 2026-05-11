import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/labs/data/models/create_lab_order_request.dart';
import 'package:etamen_app/features/labs/domain/entities/lab.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_order.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_order_payment.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_package.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_result.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_test.dart';

abstract class LabsRepository {
  Future<ApiResult<List<Lab>>> getLabs();

  Future<ApiResult<Lab>> getLab(int labId);

  Future<ApiResult<List<LabTest>>> getTests(int labId);

  Future<ApiResult<List<LabPackage>>> getPackages(int labId);

  Future<ApiResult<LabOrder>> createOrder(CreateLabOrderRequest request);

  Future<ApiResult<List<LabOrder>>> getMyOrders();

  Future<ApiResult<LabOrder>> getOrderDetails(int orderId);

  Future<ApiResult<LabOrderPayment>> createOrderPayment(int orderId);

  Future<ApiResult<LabOrder>> cancelOrder(int orderId, {String? reason});

  Future<ApiResult<LabResultDownload>> downloadResult(int resultId);
}
