import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/pharmacy/data/datasources/pharmacy_remote_data_source.dart';
import 'package:etamen_app/features/pharmacy/data/models/create_pharmacy_order_request.dart';
import 'package:etamen_app/features/pharmacy/data/models/upload_prescription_request.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_order.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_order_payment.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_prescription.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_product.dart';
import 'package:etamen_app/features/pharmacy/domain/repositories/pharmacy_repository.dart';

class PharmacyRepositoryImpl implements PharmacyRepository {
  const PharmacyRepositoryImpl(this._remoteDataSource);

  final PharmacyRemoteDataSource _remoteDataSource;

  @override
  Future<ApiResult<List<Pharmacy>>> getPharmacies() {
    return _remoteDataSource.getPharmacies();
  }

  @override
  Future<ApiResult<Pharmacy>> getPharmacy(int pharmacyId) {
    return _remoteDataSource.getPharmacy(pharmacyId);
  }

  @override
  Future<ApiResult<List<PharmacyProduct>>> getProducts(int pharmacyId) {
    return _remoteDataSource.getProducts(pharmacyId);
  }

  @override
  Future<ApiResult<PharmacyPrescription>> uploadPrescription(
    UploadPrescriptionRequest request,
  ) {
    return _remoteDataSource.uploadPrescription(request);
  }

  @override
  Future<ApiResult<PharmacyOrder>> createOrder(
    CreatePharmacyOrderRequest request,
  ) {
    return _remoteDataSource.createOrder(request);
  }

  @override
  Future<ApiResult<List<PharmacyOrder>>> getMyOrders() {
    return _remoteDataSource.getMyOrders();
  }

  @override
  Future<ApiResult<PharmacyOrder>> getOrderDetails(int orderId) {
    return _remoteDataSource.getOrderDetails(orderId);
  }

  @override
  Future<ApiResult<PharmacyOrderPayment>> createOrderPayment(int orderId) {
    return _remoteDataSource.createOrderPayment(orderId);
  }
}
