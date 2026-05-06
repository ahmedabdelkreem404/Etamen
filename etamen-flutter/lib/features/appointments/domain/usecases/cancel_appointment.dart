import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/appointments/data/models/cancel_appointment_request.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment_details.dart';
import 'package:etamen_app/features/appointments/domain/repositories/appointments_repository.dart';

class CancelAppointment {
  const CancelAppointment(this._repository);

  final AppointmentsRepository _repository;

  Future<ApiResult<AppointmentDetails>> call({
    required int appointmentId,
    required CancelAppointmentRequest request,
  }) {
    return _repository.cancel(appointmentId: appointmentId, request: request);
  }
}
