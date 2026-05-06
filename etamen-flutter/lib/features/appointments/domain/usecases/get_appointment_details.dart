import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment_details.dart';
import 'package:etamen_app/features/appointments/domain/repositories/appointments_repository.dart';

class GetAppointmentDetails {
  const GetAppointmentDetails(this._repository);

  final AppointmentsRepository _repository;

  Future<ApiResult<AppointmentDetails>> call(int appointmentId) {
    return _repository.getDetails(appointmentId);
  }
}
