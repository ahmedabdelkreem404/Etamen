import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/providers/core_providers.dart';
import 'package:etamen_app/features/payments/data/datasources/payments_remote_data_source.dart';
import 'package:etamen_app/features/payments/data/models/upload_payment_proof_request.dart';
import 'package:etamen_app/features/payments/data/repositories/payments_repository_impl.dart';
import 'package:etamen_app/features/payments/domain/entities/manual_payment_selection.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_method.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/payments/domain/entities/paymob_session.dart';
import 'package:etamen_app/features/payments/domain/repositories/payments_repository.dart';
import 'package:etamen_app/features/payments/domain/usecases/create_paymob_session.dart';
import 'package:etamen_app/features/payments/domain/usecases/get_payment_methods.dart';
import 'package:etamen_app/features/payments/domain/usecases/select_manual_payment_method.dart';
import 'package:etamen_app/features/payments/domain/usecases/upload_payment_proof.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final paymentsRemoteDataSourceProvider = Provider<PaymentsRemoteDataSource>((
  ref,
) {
  return PaymentsRemoteDataSourceImpl(ref.watch(apiClientProvider));
});

final paymentsRepositoryProvider = Provider<PaymentsRepository>((ref) {
  return PaymentsRepositoryImpl(ref.watch(paymentsRemoteDataSourceProvider));
});

class PaymentControllerState {
  const PaymentControllerState({
    this.methods = const [],
    this.isLoading = false,
    this.isSelectingMethod = false,
    this.isUploadingProof = false,
    this.isCreatingPaymobSession = false,
    this.selection,
    this.uploadedStatus,
    this.paymobSession,
    this.error,
  });

  final List<PaymentMethod> methods;
  final bool isLoading;
  final bool isSelectingMethod;
  final bool isUploadingProof;
  final bool isCreatingPaymobSession;
  final ManualPaymentSelection? selection;
  final PaymentStatusDetails? uploadedStatus;
  final PaymobSession? paymobSession;
  final ApiError? error;

  PaymentControllerState copyWith({
    List<PaymentMethod>? methods,
    bool? isLoading,
    bool? isSelectingMethod,
    bool? isUploadingProof,
    bool? isCreatingPaymobSession,
    ManualPaymentSelection? selection,
    PaymentStatusDetails? uploadedStatus,
    PaymobSession? paymobSession,
    ApiError? error,
    bool clearError = false,
  }) {
    return PaymentControllerState(
      methods: methods ?? this.methods,
      isLoading: isLoading ?? this.isLoading,
      isSelectingMethod: isSelectingMethod ?? this.isSelectingMethod,
      isUploadingProof: isUploadingProof ?? this.isUploadingProof,
      isCreatingPaymobSession:
          isCreatingPaymobSession ?? this.isCreatingPaymobSession,
      selection: selection ?? this.selection,
      uploadedStatus: uploadedStatus ?? this.uploadedStatus,
      paymobSession: paymobSession ?? this.paymobSession,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final paymentControllerProvider = StateNotifierProvider.autoDispose
    .family<PaymentController, PaymentControllerState, int>((ref, id) {
      return PaymentController(ref.watch(paymentsRepositoryProvider))
        ..loadMethods();
    });

class PaymentController extends StateNotifier<PaymentControllerState> {
  PaymentController(PaymentsRepository repository)
    : _getPaymentMethods = GetPaymentMethods(repository),
      _selectManualPaymentMethod = SelectManualPaymentMethod(repository),
      _uploadPaymentProof = UploadPaymentProof(repository),
      _createPaymobSession = CreatePaymobSession(repository),
      super(const PaymentControllerState());

  final GetPaymentMethods _getPaymentMethods;
  final SelectManualPaymentMethod _selectManualPaymentMethod;
  final UploadPaymentProof _uploadPaymentProof;
  final CreatePaymobSession _createPaymobSession;

  Future<void> loadMethods() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _getPaymentMethods();
    state = result.when(
      success: (methods) => state.copyWith(
        methods: methods.where((method) => method.isActive).toList(),
        isLoading: false,
      ),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  Future<ManualPaymentSelection?> selectManualMethod({
    required int paymentId,
    required int paymentMethodId,
  }) async {
    state = state.copyWith(isSelectingMethod: true, clearError: true);
    final result = await _selectManualPaymentMethod(
      paymentId: paymentId,
      paymentMethodId: paymentMethodId,
    );

    return result.when(
      success: (selection) {
        state = state.copyWith(
          isSelectingMethod: false,
          selection: selection,
          clearError: true,
        );
        return selection;
      },
      failure: (failure) {
        state = state.copyWith(isSelectingMethod: false, error: failure.error);
        return null;
      },
    );
  }

  Future<PaymentStatusDetails?> uploadProof({
    required int paymentId,
    required UploadPaymentProofRequest request,
  }) async {
    state = state.copyWith(isUploadingProof: true, clearError: true);
    final result = await _uploadPaymentProof(
      paymentId: paymentId,
      request: request,
    );

    return result.when(
      success: (status) {
        state = state.copyWith(
          isUploadingProof: false,
          uploadedStatus: status,
          clearError: true,
        );
        return status;
      },
      failure: (failure) {
        state = state.copyWith(isUploadingProof: false, error: failure.error);
        return null;
      },
    );
  }

  Future<PaymobSession?> createPaymobSession(int paymentId) async {
    state = state.copyWith(isCreatingPaymobSession: true, clearError: true);
    final result = await _createPaymobSession(paymentId);
    return result.when(
      success: (session) {
        state = state.copyWith(
          isCreatingPaymobSession: false,
          paymobSession: session,
          clearError: true,
        );
        return session;
      },
      failure: (failure) {
        state = state.copyWith(
          isCreatingPaymobSession: false,
          error: failure.error,
        );
        return null;
      },
    );
  }
}
