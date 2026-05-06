import 'dart:async';

import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/payments/domain/repositories/payments_repository.dart';
import 'package:etamen_app/features/payments/domain/usecases/get_payment_status.dart';
import 'package:etamen_app/features/payments/presentation/providers/payment_controller.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class PaymentStatusState {
  const PaymentStatusState({
    this.isLoading = false,
    this.isPolling = false,
    this.status,
    this.error,
  });

  final bool isLoading;
  final bool isPolling;
  final PaymentStatusDetails? status;
  final ApiError? error;

  PaymentStatusState copyWith({
    bool? isLoading,
    bool? isPolling,
    PaymentStatusDetails? status,
    ApiError? error,
    bool clearError = false,
  }) {
    return PaymentStatusState(
      isLoading: isLoading ?? this.isLoading,
      isPolling: isPolling ?? this.isPolling,
      status: status ?? this.status,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final paymentStatusControllerProvider = StateNotifierProvider.autoDispose
    .family<PaymentStatusController, PaymentStatusState, int>((ref, id) {
      final controller = PaymentStatusController(
        id,
        ref.watch(paymentsRepositoryProvider),
      );
      ref.onDispose(controller.stopPolling);
      return controller..loadStatus();
    });

class PaymentStatusController extends StateNotifier<PaymentStatusState> {
  PaymentStatusController(this.paymentId, PaymentsRepository repository)
    : _getPaymentStatus = GetPaymentStatus(repository),
      super(const PaymentStatusState());

  final int paymentId;
  final GetPaymentStatus _getPaymentStatus;
  Timer? _timer;

  Future<PaymentStatusDetails?> loadStatus() async {
    state = state.copyWith(isLoading: state.status == null, clearError: true);
    final result = await _getPaymentStatus(paymentId);

    return result.when(
      success: (status) {
        state = state.copyWith(isLoading: false, status: status);
        if (!status.status.shouldPoll) stopPolling();
        return status;
      },
      failure: (failure) {
        state = state.copyWith(isLoading: false, error: failure.error);
        if (failure.error.statusCode == 429) stopPolling();
        return null;
      },
    );
  }

  void startPolling() {
    if (_timer != null) return;
    state = state.copyWith(isPolling: true);
    _timer = Timer.periodic(const Duration(seconds: 7), (_) async {
      final status = await loadStatus();
      if (status == null || !status.status.shouldPoll) stopPolling();
    });
  }

  void stopPolling() {
    _timer?.cancel();
    _timer = null;
    if (mounted) {
      state = state.copyWith(isPolling: false);
    }
  }

  @override
  void dispose() {
    _timer?.cancel();
    _timer = null;
    super.dispose();
  }
}
