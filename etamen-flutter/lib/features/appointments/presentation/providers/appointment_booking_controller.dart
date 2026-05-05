import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/providers/core_providers.dart';
import 'package:etamen_app/features/appointments/data/datasources/appointments_remote_data_source.dart';
import 'package:etamen_app/features/appointments/data/models/book_appointment_request.dart';
import 'package:etamen_app/features/appointments/data/repositories/appointments_repository_impl.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment.dart';
import 'package:etamen_app/features/appointments/domain/repositories/appointments_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final appointmentsRemoteDataSourceProvider =
    Provider<AppointmentsRemoteDataSource>((ref) {
      return AppointmentsRemoteDataSource(ref.watch(apiClientProvider));
    });

final appointmentsRepositoryProvider = Provider<AppointmentsRepository>((ref) {
  return AppointmentsRepositoryImpl(
    ref.watch(appointmentsRemoteDataSourceProvider),
  );
});

class AppointmentBookingState {
  const AppointmentBookingState({
    this.isLoading = false,
    this.appointment,
    this.error,
  });

  final bool isLoading;
  final Appointment? appointment;
  final ApiError? error;
}

final appointmentBookingControllerProvider =
    StateNotifierProvider.autoDispose<
      AppointmentBookingController,
      AppointmentBookingState
    >((ref) {
      return AppointmentBookingController(
        ref.watch(appointmentsRepositoryProvider),
      );
    });

class AppointmentBookingController
    extends StateNotifier<AppointmentBookingState> {
  AppointmentBookingController(this._repository)
    : super(const AppointmentBookingState());

  final AppointmentsRepository _repository;

  Future<Appointment?> book(BookAppointmentRequest request) async {
    state = const AppointmentBookingState(isLoading: true);
    final result = await _repository.book(request);
    return result.when(
      success: (appointment) {
        state = AppointmentBookingState(appointment: appointment);
        return appointment;
      },
      failure: (failure) {
        state = AppointmentBookingState(error: failure.error);
        return null;
      },
    );
  }
}
