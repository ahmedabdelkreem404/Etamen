import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/features/labs/data/datasources/labs_remote_data_source.dart';
import 'package:etamen_app/features/labs/data/models/create_lab_order_request.dart';
import 'package:etamen_app/features/labs/data/models/lab_model.dart';
import 'package:etamen_app/features/labs/data/models/lab_order_model.dart';
import 'package:etamen_app/features/labs/data/models/lab_package_model.dart';
import 'package:etamen_app/features/labs/data/models/lab_test_model.dart';
import 'package:etamen_app/features/labs/data/repositories/labs_repository_impl.dart';
import 'package:etamen_app/features/labs/domain/entities/lab.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_cart_item.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_order.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_order_payment.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_package.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_result.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_test.dart';
import 'package:etamen_app/features/labs/domain/repositories/labs_repository.dart';
import 'package:etamen_app/features/labs/presentation/providers/labs_providers.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('LabModel parses nullable public lab fields', () {
    final lab = LabModel.fromJson({
      'id': 9,
      'name_ar': 'معمل الشفاء',
      'city': {'name_ar': 'القاهرة'},
      'area': {'name_ar': 'مدينة نصر'},
      'phone': '01000000000',
    });

    expect(lab.id, 9);
    expect(lab.name, 'معمل الشفاء');
    expect(lab.location, 'مدينة نصر - القاهرة');
  });

  test('LabTestModel parses result time and preparation fields', () {
    final test = LabTestModel.fromJson({
      'id': 15,
      'provider_id': 9,
      'name_en': 'CBC',
      'price': '120.00',
      'sample_type': 'blood',
      'preparation_instructions_en': 'No preparation',
      'result_time_hours': 12,
      'is_active': true,
    });

    expect(test.id, 15);
    expect(test.labId, 9);
    expect(test.name, 'CBC');
    expect(test.resultTimeHours, 12);
  });

  test('LabPackageModel parses package tests when provided', () {
    final package = LabPackageModel.fromJson({
      'id': 3,
      'provider_id': 9,
      'name_en': 'Checkup',
      'price': '300.00',
      'tests': [
        {'id': 15, 'name_en': 'CBC', 'price': '120.00'},
      ],
    });

    expect(package.id, 3);
    expect(package.tests, hasLength(1));
  });

  test('LabOrderModel parses status, payment id, items and results safely', () {
    final order = LabOrderModel.fromJson({
      'id': 77,
      'order_number': 'LAB-77',
      'order_status': 'result_ready',
      'payment': {'id': 501},
      'grand_total': '450.00',
      'currency': 'EGP',
      'sample_collection_method': 'home_collection',
      'collection_address': 'Nasr City',
      'items': [
        {
          'item_type': 'test',
          'test_id': 15,
          'item_name': 'CBC',
          'quantity': 1,
          'unit_price': '120.00',
          'line_total': '120.00',
        },
      ],
      'results': [
        {
          'id': 8,
          'order_id': 77,
          'status': 'visible_to_patient',
          'file': {'original_name': 'result.pdf'},
        },
      ],
    });

    expect(order.status, LabOrderStatus.resultReady);
    expect(order.paymentId, 501);
    expect(order.items, hasLength(1));
    expect(order.results.first.fileName, 'result.pdf');
    expect(order.hasResult, true);
  });

  test('LabOrderStatus enum maps backend statuses safely', () {
    expect(LabOrderStatus.fromWire('lab_review'), LabOrderStatus.labReview);
    expect(LabOrderStatus.fromWire('processing'), LabOrderStatus.processing);
    expect(LabOrderStatus.fromWire('not_real'), LabOrderStatus.unknown);
  });

  test('CreateLabOrderRequest excludes trusted price and status fields', () {
    final request = CreateLabOrderRequest(
      labProviderId: 9,
      sampleCollectionMethod: LabSampleCollectionMethod.homeCollection,
      collectionAddress: 'Nasr City',
      items: [
        LabCartItem(
          type: LabCartItemType.test,
          test: const LabTest(
            id: 15,
            labId: 9,
            name: 'CBC',
            price: '120.00',
            currency: 'EGP',
            isActive: true,
          ),
          quantity: 2,
        ),
      ],
    );

    final json = request.toJson();

    expect(json['lab_provider_id'], 9);
    expect(json['sample_collection_method'], 'home_collection');
    expect(json['collection_address'], 'Nasr City');
    expect(json['items'], [
      {'item_type': 'test', 'test_id': 15, 'quantity': 2},
    ]);
    expect(json.containsKey('patient_user_id'), false);
    expect(json.containsKey('unit_price'), false);
    expect(json.containsKey('line_total'), false);
    expect(json.containsKey('subtotal'), false);
    expect(json.containsKey('grand_total'), false);
    expect(json.containsKey('commission'), false);
    expect(json.containsKey('provider_net'), false);
    expect(json.containsKey('order_status'), false);
    expect(json.containsKey('payment_status'), false);
  });

  test(
    'LabCartController supports one lab cart and home address validation',
    () {
      final controller = LabCartController();
      const cbc = LabTest(
        id: 1,
        name: 'CBC',
        price: '100',
        currency: 'EGP',
        isActive: true,
      );
      const checkup = LabPackage(
        id: 2,
        name: 'Checkup',
        price: '300',
        currency: 'EGP',
        isActive: true,
      );

      expect(controller.addTest(cbc, labId: 10), true);
      expect(controller.addPackage(checkup, labId: 11), false);
      expect(controller.state.labId, 10);

      expect(
        controller.addPackage(checkup, labId: 11, clearExisting: true),
        true,
      );
      controller.setSampleMethod(LabSampleCollectionMethod.homeCollection);
      expect(controller.state.requiresHomeAddress, true);
      expect(controller.state.hasHomeAddress, false);
      controller.updateAddress('Nasr City');
      expect(controller.state.hasHomeAddress, true);
    },
  );

  test('labs repository forwards order creation and payment start', () async {
    final remote = FakeLabsRemoteDataSource();
    final repository = LabsRepositoryImpl(remote);
    final request = CreateLabOrderRequest(
      labProviderId: 9,
      sampleCollectionMethod: LabSampleCollectionMethod.branchVisit,
      items: [
        LabCartItem(
          type: LabCartItemType.test,
          test: const LabTest(
            id: 15,
            name: 'CBC',
            price: '120.00',
            currency: 'EGP',
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
  });

  test('lab orders filter logic groups result ready records', () {
    const state = LabOrdersState(
      selectedFilter: LabOrderFilter.resultReady,
      items: [
        LabOrder(id: 1, status: LabOrderStatus.resultReady, items: []),
        LabOrder(id: 2, status: LabOrderStatus.processing, items: []),
      ],
    );

    expect(state.filteredItems, hasLength(1));
    expect(state.filteredItems.first.id, 1);
  });

  test('payment route context supports lab order id', () {
    expect(
      RouteNames.payment(501, labOrderId: 77),
      '/payments/501?labOrderId=77',
    );
    expect(
      RouteNames.paymentStatus(501, labOrderId: 77),
      '/payments/501/status?labOrderId=77',
    );
  });

  test('lab result download uses authorized backend endpoint only', () {
    expect(ApiEndpoints.labResultDownload(8), '/lab/results/8/download');
  });
}

class FakeLabsRemoteDataSource implements LabsRemoteDataSource {
  CreateLabOrderRequest? createdRequest;

  @override
  Future<ApiResult<LabOrderModel>> createOrder(
    CreateLabOrderRequest request,
  ) async {
    createdRequest = request;
    return ApiSuccess(
      LabOrderModel.fromJson({
        'id': 77,
        'order_status': 'lab_review',
        'items': const [],
      }),
    );
  }

  @override
  Future<ApiResult<LabOrderPayment>> createOrderPayment(int orderId) async {
    return ApiSuccess(
      LabOrderPayment(
        paymentId: 501,
        order: LabOrderModel.fromJson({
          'id': orderId,
          'order_status': 'awaiting_payment',
          'payment_id': 501,
          'items': const [],
        }),
      ),
    );
  }

  @override
  Future<ApiResult<LabResultDownload>> downloadResult(int resultId) async {
    return ApiSuccess(
      LabResultDownload(resultId: resultId, localPath: 'tmp/result.pdf'),
    );
  }

  @override
  Future<ApiResult<LabModel>> getLab(int labId) async {
    return ApiSuccess(LabModel(id: labId, name: 'Lab'));
  }

  @override
  Future<ApiResult<List<LabModel>>> getLabs() async {
    return const ApiSuccess([]);
  }

  @override
  Future<ApiResult<LabOrderModel>> getOrderDetails(int orderId) async {
    return ApiSuccess(
      LabOrderModel.fromJson({
        'id': orderId,
        'order_status': 'processing',
        'items': const [],
      }),
    );
  }

  @override
  Future<ApiResult<List<LabPackageModel>>> getPackages(int labId) async {
    return const ApiSuccess([]);
  }

  @override
  Future<ApiResult<List<LabTestModel>>> getTests(int labId) async {
    return const ApiSuccess([]);
  }

  @override
  Future<ApiResult<List<LabOrderModel>>> getMyOrders() async {
    return const ApiSuccess([]);
  }
}

class FailingLabsRepository implements LabsRepository {
  @override
  Future<ApiResult<LabOrder>> createOrder(CreateLabOrderRequest request) async {
    return const ApiFailure(
      ApiError(message: 'Rejected', type: ApiErrorType.validation),
    );
  }

  @override
  Future<ApiResult<LabOrderPayment>> createOrderPayment(int orderId) async {
    return const ApiFailure(
      ApiError(message: 'Not used', type: ApiErrorType.unknown),
    );
  }

  @override
  Future<ApiResult<LabResultDownload>> downloadResult(int resultId) async {
    return const ApiFailure(
      ApiError(message: 'Result unavailable', type: ApiErrorType.unknown),
    );
  }

  @override
  Future<ApiResult<Lab>> getLab(int labId) async {
    return const ApiFailure(
      ApiError(message: 'Not used', type: ApiErrorType.unknown),
    );
  }

  @override
  Future<ApiResult<List<Lab>>> getLabs() async {
    return const ApiFailure(
      ApiError(message: 'Not used', type: ApiErrorType.unknown),
    );
  }

  @override
  Future<ApiResult<LabOrder>> getOrderDetails(int orderId) async {
    return const ApiFailure(
      ApiError(message: 'Not used', type: ApiErrorType.unknown),
    );
  }

  @override
  Future<ApiResult<List<LabPackage>>> getPackages(int labId) async {
    return const ApiFailure(
      ApiError(message: 'Not used', type: ApiErrorType.unknown),
    );
  }

  @override
  Future<ApiResult<List<LabTest>>> getTests(int labId) async {
    return const ApiFailure(
      ApiError(message: 'Not used', type: ApiErrorType.unknown),
    );
  }

  @override
  Future<ApiResult<List<LabOrder>>> getMyOrders() async {
    return const ApiFailure(
      ApiError(message: 'Not used', type: ApiErrorType.unknown),
    );
  }
}
