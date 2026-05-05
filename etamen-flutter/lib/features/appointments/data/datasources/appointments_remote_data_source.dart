import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/network/api_client.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/appointments/data/models/appointment_model.dart';
import 'package:etamen_app/features/appointments/data/models/book_appointment_request.dart';

class AppointmentsRemoteDataSource {
  AppointmentsRemoteDataSource(this._client);

  final ApiClient _client;

  Future<ApiResult<AppointmentModel>> book(BookAppointmentRequest request) {
    return _client.post<AppointmentModel>(
      ApiEndpoints.appointments,
      data: request.toJson(),
      parser: (raw) => AppointmentModel.fromJson(raw as Map<String, dynamic>),
    );
  }
}
