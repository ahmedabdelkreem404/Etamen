import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/appointments/data/models/book_appointment_request.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment.dart';

abstract class AppointmentsRepository {
  Future<ApiResult<Appointment>> book(BookAppointmentRequest request);
}
