import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/features/pharmacy/data/datasources/pharmacy_remote_data_source.dart';
import 'package:etamen_app/features/pharmacy/data/models/create_pharmacy_order_request.dart';
import 'package:etamen_app/features/pharmacy/data/models/pharmacy_model.dart';
import 'package:etamen_app/features/pharmacy/data/models/pharmacy_order_model.dart';
import 'package:etamen_app/features/pharmacy/data/models/pharmacy_prescription_model.dart';
import 'package:etamen_app/features/pharmacy/data/models/pharmacy_product_model.dart';
import 'package:etamen_app/features/pharmacy/data/models/upload_prescription_request.dart';
import 'package:etamen_app/features/pharmacy/data/repositories/pharmacy_repository_impl.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_cart_item.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_order.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_order_payment.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_prescription.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_product.dart';
import 'package:etamen_app/features/pharmacy/domain/repositories/pharmacy_repository.dart';
import 'package:etamen_app/features/pharmacy/presentation/providers/pharmacy_providers.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('PharmacyModel parses nullable public pharmacy fields', () {
    final pharmacy = PharmacyModel.fromJson({
      'id': 9,
      'name_ar': 'صيدلية الشفاء',
      'city': {'name_ar': 'القاهرة'},
      'area': {'name_ar': 'مدينة نصر'},
      'phone': '01000000000',
      'rating_average': '4.8',
    });

    expect(pharmacy.id, 9);
    expect(pharmacy.name, 'صيدلية الشفاء');
    expect(pharmacy.location, 'مدينة نصر - القاهرة');
    expect(pharmacy.rating, '4.8');
  });

  test('PharmacyProductModel parses product and prescription flag', () {
    final product = PharmacyProductModel.fromJson({
      'id': 15,
      'pharmacy_provider_id': 9,
      'name_en': 'Medicine',
      'price': '42.50',
      'currency': 'EGP',
      'requires_prescription': true,
      'is_active': true,
    });

    expect(product.id, 15);
    expect(product.pharmacyId, 9);
    expect(product.requiresPrescription, true);
    expect(product.price, '42.50');
  });

  test('PharmacyOrderModel parses status, payment id and items safely', () {
    final order = PharmacyOrderModel.fromJson({
      'id': 77,
      'order_number': 'PH-77',
      'order_status': 'awaiting_payment',
      'payment': {'id': 501},
      'grand_total': '120.00',
      'currency': 'EGP',
      'provider': {'name_ar': 'صيدلية'},
      'items': [
        {
          'product_id': 15,
          'product_name': 'Medicine',
          'quantity': 2,
          'unit_price': '60.00',
          'line_total': '120.00',
        },
      ],
    });

    expect(order.status, PharmacyOrderStatus.awaitingPayment);
    expect(order.paymentId, 501);
    expect(order.items, hasLength(1));
    expect(order.canPay, true);
  });

  test('PharmacyOrderStatus enum maps backend statuses safely', () {
    expect(
      PharmacyOrderStatus.fromWire('pharmacy_review'),
      PharmacyOrderStatus.pharmacyReview,
    );
    expect(
      PharmacyOrderStatus.fromWire('delivered'),
      PharmacyOrderStatus.delivered,
    );
    expect(
      PharmacyOrderStatus.fromWire('not_real'),
      PharmacyOrderStatus.unknown,
    );
  });

  test(
    'CreatePharmacyOrderRequest excludes trusted price and status fields',
    () {
      final request = CreatePharmacyOrderRequest(
        pharmacyProviderId: 9,
        items: [
          PharmacyCartItem(
            product: const PharmacyProduct(
              id: 15,
              pharmacyId: 9,
              name: 'Medicine',
              price: '42.50',
              currency: 'EGP',
              requiresPrescription: true,
              isActive: true,
            ),
            quantity: 2,
          ),
        ],
        prescriptionId: 3,
        notes: 'Please review',
      );

      final json = request.toJson();

      expect(json['pharmacy_provider_id'], 9);
      expect(json['items'], [
        {'product_id': 15, 'quantity': 2},
      ]);
      expect(json.containsKey('patient_user_id'), false);
      expect(json.containsKey('unit_price'), false);
      expect(json.containsKey('line_total'), false);
      expect(json.containsKey('subtotal'), false);
      expect(json.containsKey('grand_total'), false);
      expect(json.containsKey('order_status'), false);
      expect(json.containsKey('payment_status'), false);
    },
  );

  test('PharmacyCartController keeps one pharmacy cart at a time', () {
    final controller = PharmacyCartController();
    const productA = PharmacyProduct(
      id: 1,
      name: 'A',
      price: '10',
      currency: 'EGP',
      requiresPrescription: false,
      isActive: true,
    );
    const productB = PharmacyProduct(
      id: 2,
      name: 'B',
      price: '20',
      currency: 'EGP',
      requiresPrescription: true,
      isActive: true,
    );

    expect(controller.addProduct(productA, pharmacyId: 10), true);
    expect(controller.state.itemCount, 1);
    expect(controller.addProduct(productB, pharmacyId: 11), false);
    expect(controller.state.pharmacyId, 10);

    expect(
      controller.addProduct(productB, pharmacyId: 11, clearExisting: true),
      true,
    );
    expect(controller.state.pharmacyId, 11);
    expect(controller.state.requiresPrescription, true);
  });

  test(
    'pharmacy repository forwards order creation and payment start',
    () async {
      final remote = FakePharmacyRemoteDataSource();
      final repository = PharmacyRepositoryImpl(remote);
      final request = CreatePharmacyOrderRequest(
        pharmacyProviderId: 9,
        items: [
          PharmacyCartItem(
            product: const PharmacyProduct(
              id: 15,
              name: 'Medicine',
              price: '42.50',
              currency: 'EGP',
              requiresPrescription: false,
              isActive: true,
            ),
            quantity: 1,
          ),
        ],
      );

      final orderResult = await repository.createOrder(request);
      final paymentResult = await repository.createOrderPayment(77);

      expect(orderResult, isA<ApiSuccess>());
      expect(remote.createdRequest, request);
      paymentResult.when(
        success: (payment) => expect(payment.paymentId, 501),
        failure: (_) => fail('Expected payment start success'),
      );
    },
  );

  test('pharmacy orders filter logic groups payment and preparing states', () {
    const state = PharmacyOrdersState(
      selectedFilter: PharmacyOrderFilter.awaitingPayment,
      items: [
        PharmacyOrder(
          id: 1,
          status: PharmacyOrderStatus.awaitingPayment,
          paymentId: 10,
          items: [],
        ),
        PharmacyOrder(id: 2, status: PharmacyOrderStatus.delivered, items: []),
      ],
    );

    expect(state.filteredItems, hasLength(1));
    expect(state.filteredItems.first.id, 1);
  });

  test('payment route context supports pharmacy order id', () {
    expect(
      RouteNames.payment(501, pharmacyOrderId: 77),
      '/payments/501?pharmacyOrderId=77',
    );
    expect(
      RouteNames.paymentStatus(501, pharmacyOrderId: 77),
      '/payments/501/status?pharmacyOrderId=77',
    );
  });
}

class FakePharmacyRemoteDataSource implements PharmacyRemoteDataSource {
  CreatePharmacyOrderRequest? createdRequest;

  @override
  Future<ApiResult<PharmacyOrderModel>> createOrder(
    CreatePharmacyOrderRequest request,
  ) async {
    createdRequest = request;
    return ApiSuccess(
      PharmacyOrderModel.fromJson({
        'id': 77,
        'order_status': 'pharmacy_review',
        'items': const [],
      }),
    );
  }

  @override
  Future<ApiResult<PharmacyOrderPayment>> createOrderPayment(
    int orderId,
  ) async {
    return ApiSuccess(
      PharmacyOrderPayment(
        paymentId: 501,
        order: PharmacyOrderModel.fromJson({
          'id': orderId,
          'order_status': 'awaiting_payment',
          'payment_id': 501,
          'items': const [],
        }),
      ),
    );
  }

  @override
  Future<ApiResult<List<PharmacyModel>>> getPharmacies() async {
    return const ApiSuccess([]);
  }

  @override
  Future<ApiResult<PharmacyModel>> getPharmacy(int pharmacyId) async {
    return ApiSuccess(PharmacyModel(id: pharmacyId, name: 'Pharmacy'));
  }

  @override
  Future<ApiResult<PharmacyOrderModel>> getOrderDetails(int orderId) async {
    return ApiSuccess(
      PharmacyOrderModel.fromJson({
        'id': orderId,
        'order_status': 'awaiting_payment',
        'items': const [],
      }),
    );
  }

  @override
  Future<ApiResult<List<PharmacyOrderModel>>> getMyOrders() async {
    return const ApiSuccess([]);
  }

  @override
  Future<ApiResult<List<PharmacyProductModel>>> getProducts(
    int pharmacyId,
  ) async {
    return const ApiSuccess([]);
  }

  @override
  Future<ApiResult<PharmacyPrescriptionModel>> uploadPrescription(
    UploadPrescriptionRequest request,
  ) async {
    return ApiSuccess(
      PharmacyPrescriptionModel.fromJson({
        'id': 3,
        'file_name': request.fileName,
      }),
    );
  }
}

class FailingPharmacyRepository implements PharmacyRepository {
  @override
  Future<ApiResult<PharmacyOrder>> createOrder(
    CreatePharmacyOrderRequest request,
  ) async {
    return const ApiFailure(
      ApiError(message: 'Rejected', type: ApiErrorType.validation),
    );
  }

  @override
  Future<ApiResult<PharmacyOrderPayment>> createOrderPayment(
    int orderId,
  ) async {
    return const ApiFailure(
      ApiError(message: 'Not used', type: ApiErrorType.unknown),
    );
  }

  @override
  Future<ApiResult<List<Pharmacy>>> getPharmacies() async {
    return const ApiFailure(
      ApiError(message: 'Not used', type: ApiErrorType.unknown),
    );
  }

  @override
  Future<ApiResult<Pharmacy>> getPharmacy(int pharmacyId) async {
    return const ApiFailure(
      ApiError(message: 'Not used', type: ApiErrorType.unknown),
    );
  }

  @override
  Future<ApiResult<PharmacyOrder>> getOrderDetails(int orderId) async {
    return const ApiFailure(
      ApiError(message: 'Not used', type: ApiErrorType.unknown),
    );
  }

  @override
  Future<ApiResult<List<PharmacyOrder>>> getMyOrders() async {
    return const ApiFailure(
      ApiError(message: 'Not used', type: ApiErrorType.unknown),
    );
  }

  @override
  Future<ApiResult<List<PharmacyProduct>>> getProducts(int pharmacyId) async {
    return const ApiFailure(
      ApiError(message: 'Not used', type: ApiErrorType.unknown),
    );
  }

  @override
  Future<ApiResult<PharmacyPrescription>> uploadPrescription(
    UploadPrescriptionRequest request,
  ) async {
    return const ApiFailure(
      ApiError(message: 'Not used', type: ApiErrorType.unknown),
    );
  }
}
