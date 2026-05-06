import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/features/appointments/data/models/cancel_appointment_request.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment_details.dart';
import 'package:etamen_app/features/appointments/domain/repositories/appointments_repository.dart';
import 'package:etamen_app/features/appointments/domain/usecases/cancel_appointment.dart';
import 'package:etamen_app/features/appointments/domain/usecases/get_appointment_details.dart';
import 'package:etamen_app/features/appointments/presentation/providers/appointment_booking_controller.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/payments/domain/repositories/payments_repository.dart';
import 'package:etamen_app/features/payments/domain/usecases/get_payment_status.dart';
import 'package:etamen_app/features/payments/presentation/providers/payment_controller.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class AppointmentDetailsState {
  const AppointmentDetailsState({
    this.isLoading = false,
    this.isCancelling = false,
    this.details,
    this.paymentStatus,
    this.error,
  });

  final bool isLoading;
  final bool isCancelling;
  final AppointmentDetails? details;
  final PaymentStatusDetails? paymentStatus;
  final ApiError? error;

  AppointmentDetailsState copyWith({
    bool? isLoading,
    bool? isCancelling,
    AppointmentDetails? details,
    PaymentStatusDetails? paymentStatus,
    ApiError? error,
    bool clearError = false,
  }) {
    return AppointmentDetailsState(
      isLoading: isLoading ?? this.isLoading,
      isCancelling: isCancelling ?? this.isCancelling,
      details: details ?? this.details,
      paymentStatus: paymentStatus ?? this.paymentStatus,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final appointmentDetailsControllerProvider = StateNotifierProvider.autoDispose
    .family<AppointmentDetailsController, AppointmentDetailsState, int>((
      ref,
      id,
    ) {
      return AppointmentDetailsController(
        appointmentId: id,
        appointmentsRepository: ref.watch(appointmentsRepositoryProvider),
        paymentsRepository: ref.watch(paymentsRepositoryProvider),
      )..load();
    });

class AppointmentDetailsController
    extends StateNotifier<AppointmentDetailsState> {
  AppointmentDetailsController({
    required this.appointmentId,
    required AppointmentsRepository appointmentsRepository,
    required PaymentsRepository paymentsRepository,
  }) : _getAppointmentDetails = GetAppointmentDetails(appointmentsRepository),
       _cancelAppointment = CancelAppointment(appointmentsRepository),
       _getPaymentStatus = GetPaymentStatus(paymentsRepository),
       super(const AppointmentDetailsState());

  final int appointmentId;
  final GetAppointmentDetails _getAppointmentDetails;
  final CancelAppointment _cancelAppointment;
  final GetPaymentStatus _getPaymentStatus;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _getAppointmentDetails(appointmentId);
    await result.when(
      success: (details) async {
        state = state.copyWith(
          isLoading: false,
          details: details,
          clearError: true,
        );
        await loadPaymentStatus();
      },
      failure: (failure) async {
        state = state.copyWith(isLoading: false, error: failure.error);
      },
    );
  }

  Future<void> loadPaymentStatus() async {
    final paymentId = state.details?.paymentId;
    if (paymentId == null) return;

    final result = await _getPaymentStatus(paymentId);
    state = result.when(
      success: (status) => state.copyWith(paymentStatus: status),
      failure: (failure) => state.copyWith(error: failure.error),
    );
  }

  Future<bool> cancel({String? reason}) async {
    state = state.copyWith(isCancelling: true, clearError: true);
    final result = await _cancelAppointment(
      appointmentId: appointmentId,
      request: CancelAppointmentRequest(reason: reason),
    );

    return result.when(
      success: (details) {
        state = state.copyWith(
          isCancelling: false,
          details: details,
          clearError: true,
        );
        return true;
      },
      failure: (failure) {
        state = state.copyWith(isCancelling: false, error: failure.error);
        return false;
      },
    );
  }
}
