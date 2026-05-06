import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment.dart';
import 'package:etamen_app/features/appointments/domain/repositories/appointments_repository.dart';

class GetMyAppointments {
  const GetMyAppointments(this._repository);

  final AppointmentsRepository _repository;

  Future<ApiResult<List<Appointment>>> call({int page = 1, int perPage = 20}) {
    return _repository.getMyAppointments(page: page, perPage: perPage);
  }
}
