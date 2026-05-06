import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/appointments/data/models/book_appointment_request.dart';
import 'package:etamen_app/features/appointments/data/models/cancel_appointment_request.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment_details.dart';

abstract class AppointmentsRepository {
  Future<ApiResult<Appointment>> book(BookAppointmentRequest request);

  Future<ApiResult<List<Appointment>>> getMyAppointments({
    int page = 1,
    int perPage = 20,
  });

  Future<ApiResult<AppointmentDetails>> getDetails(int appointmentId);

  Future<ApiResult<AppointmentDetails>> cancel({
    required int appointmentId,
    required CancelAppointmentRequest request,
  });
}
