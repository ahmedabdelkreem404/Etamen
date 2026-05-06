import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/appointments/data/datasources/appointments_remote_data_source.dart';
import 'package:etamen_app/features/appointments/data/models/book_appointment_request.dart';
import 'package:etamen_app/features/appointments/data/models/cancel_appointment_request.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment_details.dart';
import 'package:etamen_app/features/appointments/domain/repositories/appointments_repository.dart';

class AppointmentsRepositoryImpl implements AppointmentsRepository {
  const AppointmentsRepositoryImpl(this._remoteDataSource);

  final AppointmentsRemoteDataSource _remoteDataSource;

  @override
  Future<ApiResult<Appointment>> book(BookAppointmentRequest request) {
    return _remoteDataSource.book(request);
  }

  @override
  Future<ApiResult<List<Appointment>>> getMyAppointments({
    int page = 1,
    int perPage = 20,
  }) {
    return _remoteDataSource.getMyAppointments(page: page, perPage: perPage);
  }

  @override
  Future<ApiResult<AppointmentDetails>> getDetails(int appointmentId) {
    return _remoteDataSource.getDetails(appointmentId);
  }

  @override
  Future<ApiResult<AppointmentDetails>> cancel({
    required int appointmentId,
    required CancelAppointmentRequest request,
  }) {
    return _remoteDataSource.cancel(
      appointmentId: appointmentId,
      request: request,
    );
  }
}
