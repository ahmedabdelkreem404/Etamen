import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/pharmacy/data/models/create_pharmacy_order_request.dart';
import 'package:etamen_app/features/pharmacy/data/models/upload_prescription_request.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_order.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_order_payment.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_prescription.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_product.dart';

abstract class PharmacyRepository {
  Future<ApiResult<List<Pharmacy>>> getPharmacies();

  Future<ApiResult<Pharmacy>> getPharmacy(int pharmacyId);

  Future<ApiResult<List<PharmacyProduct>>> getProducts(int pharmacyId);

  Future<ApiResult<PharmacyPrescription>> uploadPrescription(
    UploadPrescriptionRequest request,
  );

  Future<ApiResult<PharmacyOrder>> createOrder(
    CreatePharmacyOrderRequest request,
  );

  Future<ApiResult<List<PharmacyOrder>>> getMyOrders();

  Future<ApiResult<PharmacyOrder>> getOrderDetails(int orderId);

  Future<ApiResult<PharmacyOrderPayment>> createOrderPayment(int orderId);

  Future<ApiResult<PharmacyOrder>> cancelOrder(int orderId, {String? reason});
}
