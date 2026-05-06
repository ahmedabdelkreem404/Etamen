import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/network/api_client.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/appointments/data/models/appointment_details_model.dart';
import 'package:etamen_app/features/appointments/data/models/appointment_model.dart';
import 'package:etamen_app/features/appointments/data/models/book_appointment_request.dart';
import 'package:etamen_app/features/appointments/data/models/cancel_appointment_request.dart';

class AppointmentsRemoteDataSource {
  AppointmentsRemoteDataSource(this._client);

  final ApiClient _client;

  Future<ApiResult<AppointmentModel>> book(BookAppointmentRequest request) {
    return _client.post<AppointmentModel>(
      ApiEndpoints.appointments,
      data: request.toJson(),
      parser: (raw) => AppointmentModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<List<AppointmentModel>>> getMyAppointments({
    int page = 1,
    int perPage = 20,
  }) {
    return _client.get<List<AppointmentModel>>(
      ApiEndpoints.appointments,
      queryParameters: {'page': page, 'per_page': perPage},
      parser: (raw) => _parseList(
        raw,
      ).map(AppointmentModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<AppointmentDetailsModel>> getDetails(int appointmentId) {
    return _client.get<AppointmentDetailsModel>(
      ApiEndpoints.appointment(appointmentId),
      parser: (raw) => AppointmentDetailsModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<AppointmentDetailsModel>> cancel({
    required int appointmentId,
    required CancelAppointmentRequest request,
  }) {
    return _client.post<AppointmentDetailsModel>(
      ApiEndpoints.cancelAppointment(appointmentId),
      data: request.toJson(),
      parser: (raw) => AppointmentDetailsModel.fromJson(_unwrapMap(raw)),
    );
  }

  static List<Map<String, dynamic>> _parseList(Object? raw) {
    final value = _unwrapCollection(raw);
    if (value is! List) return const [];
    return value
        .whereType<Map>()
        .map(
          (item) => item.map((key, value) => MapEntry(key.toString(), value)),
        )
        .toList(growable: false);
  }

  static Object? _unwrapCollection(Object? raw) {
    if (raw is Map) {
      return raw['data'] ??
          raw['items'] ??
          raw['appointments'] ??
          raw['results'];
    }
    return raw;
  }

  static Map<String, dynamic> _unwrapMap(Object? raw) {
    if (raw is Map<String, dynamic>) {
      final nested = raw['appointment'] ?? raw['data'];
      if (nested is Map<String, dynamic>) return nested;
      if (nested is Map) {
        return nested.map((key, value) => MapEntry(key.toString(), value));
      }
      return raw;
    }
    if (raw is Map) {
      return raw.map((key, value) => MapEntry(key.toString(), value));
    }
    return const {};
  }
}
