import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/payments/data/models/manual_payment_selection_model.dart';
import 'package:etamen_app/features/payments/data/models/payment_method_model.dart';
import 'package:etamen_app/features/payments/data/models/payment_status_model.dart';
import 'package:etamen_app/features/payments/data/models/paymob_session_model.dart';
import 'package:etamen_app/features/payments/data/models/upload_payment_proof_request.dart';
import 'package:etamen_app/features/payments/data/repositories/payments_repository_impl.dart';
import 'package:etamen_app/features/payments/data/datasources/payments_remote_data_source.dart';
import 'package:etamen_app/features/payments/domain/entities/manual_payment_selection.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_method.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/payments/domain/entities/paymob_session.dart';
import 'package:etamen_app/features/payments/domain/repositories/payments_repository.dart';
import 'package:etamen_app/features/payments/presentation/providers/payment_status_controller.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('PaymentMethod parsing maps allowed method types', () {
    final method = PaymentMethodModel.fromJson({
      'id': 1,
      'type': 'manual_vodafone_cash',
      'name_ar': 'فودافون كاش',
      'name_en': 'Vodafone Cash',
      'instructions_ar': 'حوّل ثم ارفع الإثبات',
      'is_active': true,
    });

    expect(method.id, 1);
    expect(method.type, PaymentMethodType.manualVodafoneCash);
    expect(method.type.isManual, true);
    expect(method.displayName(true), 'فودافون كاش');
  });

  test(
    'PaymentStatus parsing hides raw gateway data and maps appointment state',
    () {
      final status = PaymentStatusModel.fromJson({
        'id': 200,
        'method_type': 'manual_instapay',
        'amount': '250.00',
        'currency': 'EGP',
        'status': 'pending_review',
        'payable': {'type': 'appointment', 'id': 55},
        'appointment': {'id': 55, 'status': 'pending_payment_review'},
        'invoice': null,
        'updated_at': '2026-05-05T10:00:00.000000Z',
      });

      expect(status.id, 200);
      expect(status.status, PaymentStatusEnum.pendingReview);
      expect(status.status.shouldPoll, true);
      expect(status.payableType, 'appointment');
      expect(status.payableId, 55);
      expect(status.appointmentStatus, 'pending_payment_review');
    },
  );

  test('Payment enum mapping treats duplicate verified status as terminal', () {
    expect(PaymentStatusEnum.fromWire('verified'), PaymentStatusEnum.verified);
    expect(PaymentStatusEnum.verified.isTerminal, true);
    expect(PaymentStatusEnum.verified.shouldPoll, false);
    expect(PaymentStatusEnum.fromWire('not_real'), PaymentStatusEnum.unknown);
  });

  test(
    'manual select repository success returns backend instructions',
    () async {
      final remote = FakePaymentsRemoteDataSource();
      final repository = PaymentsRepositoryImpl(remote);

      final result = await repository.selectManualMethod(
        paymentId: 200,
        paymentMethodId: 2,
      );

      expect(result, isA<ApiSuccess>());
      expect(remote.selectedPaymentMethodId, 2);
      result.when(
        success: (selection) {
          expect(selection.paymentId, 200);
          expect(selection.methodType, PaymentMethodType.manualInstapay);
          expect(selection.instructions(true), 'حوّل على إنستاباي');
        },
        failure: (_) => fail('Expected success'),
      );
    },
  );

  test('proof upload repository forwards only proof request data', () async {
    final remote = FakePaymentsRemoteDataSource();
    final repository = PaymentsRepositoryImpl(remote);
    const request = UploadPaymentProofRequest(
      filePath: '/tmp/proof.png',
      fileName: 'proof.png',
      referenceNumber: 'REF123',
      senderPhone: '01012345678',
      notes: 'Paid',
    );

    final result = await repository.uploadProof(
      paymentId: 200,
      request: request,
    );

    expect(result, isA<ApiSuccess>());
    expect(remote.uploadedRequest, request);
    expect(remote.uploadedPaymentId, 200);
  });

  test('Paymob session parsing keeps only safe checkout fields', () {
    final session = PaymobSessionModel.fromJson({
      'checkout_url': 'https://accept.paymob.com/checkout',
      'client_secret': 'client-only',
      'gateway_reference': 'gw-123',
      'raw_payload': {'secret': 'must not be modelled'},
    });

    expect(session.checkoutUrl, 'https://accept.paymob.com/checkout');
    expect(session.clientSecret, 'client-only');
    expect(session.gatewayReference, 'gw-123');
  });

  test('Payment status polling stops on terminal status', () async {
    final repository = FakePaymentsRepository(
      statuses: [
        const PaymentStatusDetails(
          id: 200,
          status: PaymentStatusEnum.pendingReview,
          amount: '250.00',
          currency: 'EGP',
          payableType: 'appointment',
          payableId: 55,
        ),
        const PaymentStatusDetails(
          id: 200,
          status: PaymentStatusEnum.verified,
          amount: '250.00',
          currency: 'EGP',
          payableType: 'appointment',
          payableId: 55,
        ),
      ],
    );
    final controller = PaymentStatusController(200, repository);

    controller.startPolling();
    expect(controller.state.isPolling, true);

    await controller.loadStatus();
    expect(controller.state.isPolling, true);

    await controller.loadStatus();
    expect(controller.state.status?.status, PaymentStatusEnum.verified);
    expect(controller.state.isPolling, false);

    controller.dispose();
  });
}

class FakePaymentsRemoteDataSource implements PaymentsRemoteDataSource {
  int? selectedPaymentMethodId;
  int? uploadedPaymentId;
  UploadPaymentProofRequest? uploadedRequest;

  @override
  Future<ApiResult<List<PaymentMethodModel>>> getPaymentMethods() async {
    return const ApiSuccess([]);
  }

  @override
  Future<ApiResult<PaymentStatusModel>> getPaymentStatus(int paymentId) async {
    return ApiSuccess(
      PaymentStatusModel.fromJson({
        'id': paymentId,
        'status': 'pending_review',
        'amount': '250.00',
        'currency': 'EGP',
        'payable_type': 'appointment',
        'payable_id': 55,
      }),
    );
  }

  @override
  Future<ApiResult<ManualPaymentSelectionModel>> selectManualMethod({
    required int paymentId,
    required int paymentMethodId,
  }) async {
    selectedPaymentMethodId = paymentMethodId;
    return ApiSuccess(
      ManualPaymentSelectionModel.fromPaymentJson({
        'id': paymentId,
        'status': 'awaiting_proof',
        'method_type': 'manual_instapay',
        'instructions_ar': 'حوّل على إنستاباي',
      }),
    );
  }

  @override
  Future<ApiResult<PaymentStatusModel>> uploadProof({
    required int paymentId,
    required UploadPaymentProofRequest request,
  }) async {
    uploadedPaymentId = paymentId;
    uploadedRequest = request;
    return getPaymentStatus(paymentId);
  }

  @override
  Future<ApiResult<PaymobSessionModel>> createPaymobSession(
    int paymentId,
  ) async {
    return const ApiSuccess(
      PaymobSessionModel(checkoutUrl: 'https://accept.paymob.com/checkout'),
    );
  }
}

class FakePaymentsRepository implements PaymentsRepository {
  FakePaymentsRepository({required List<PaymentStatusDetails> statuses})
    : _statuses = List.of(statuses);

  final List<PaymentStatusDetails> _statuses;

  @override
  Future<ApiResult<PaymobSession>> createPaymobSession(int paymentId) async {
    return const ApiFailure(
      ApiError(message: 'Not used', type: ApiErrorType.unknown),
    );
  }

  @override
  Future<ApiResult<List<PaymentMethod>>> getPaymentMethods() async {
    return const ApiSuccess([]);
  }

  @override
  Future<ApiResult<PaymentStatusDetails>> getPaymentStatus(
    int paymentId,
  ) async {
    return ApiSuccess(_statuses.removeAt(0));
  }

  @override
  Future<ApiResult<ManualPaymentSelection>> selectManualMethod({
    required int paymentId,
    required int paymentMethodId,
  }) async {
    return const ApiFailure(
      ApiError(message: 'Not used', type: ApiErrorType.unknown),
    );
  }

  @override
  Future<ApiResult<PaymentStatusDetails>> uploadProof({
    required int paymentId,
    required UploadPaymentProofRequest request,
  }) async {
    return const ApiFailure(
      ApiError(message: 'Not used', type: ApiErrorType.unknown),
    );
  }
}
