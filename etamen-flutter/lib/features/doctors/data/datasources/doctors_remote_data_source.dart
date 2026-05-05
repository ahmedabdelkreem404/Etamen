import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/network/api_client.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/doctors/data/models/doctor_model.dart';
import 'package:etamen_app/features/doctors/data/models/doctor_slot_model.dart';

class DoctorsRemoteDataSource {
  DoctorsRemoteDataSource(this._client);

  final ApiClient _client;

  Future<ApiResult<List<DoctorModel>>> doctors({
    int page = 1,
    int perPage = 20,
    String? search,
  }) {
    return _client.get<List<DoctorModel>>(
      ApiEndpoints.doctors,
      queryParameters: {
        'page': page,
        'per_page': perPage,
        if (search != null && search.isNotEmpty) 'search': search,
      },
      parser: (raw) => (raw as List? ?? const [])
          .whereType<Map<String, dynamic>>()
          .map(DoctorModel.fromJson)
          .toList(growable: false),
    );
  }

  Future<ApiResult<DoctorModel>> doctor(int id) {
    return _client.get<DoctorModel>(
      ApiEndpoints.doctor(id),
      parser: (raw) => DoctorModel.fromJson(raw as Map<String, dynamic>),
    );
  }

  Future<ApiResult<List<DoctorSlotModel>>> slots(
    int doctorId, {
    int perPage = 30,
    DateTime? startDate,
    DateTime? endDate,
  }) {
    String? date(DateTime? value) => value?.toIso8601String().split('T').first;

    return _client.get<List<DoctorSlotModel>>(
      ApiEndpoints.doctorSlots(doctorId),
      queryParameters: {
        'per_page': perPage,
        if (date(startDate) != null) 'start_date': date(startDate),
        if (date(endDate) != null) 'end_date': date(endDate),
      },
      parser: (raw) => (raw as List? ?? const [])
          .whereType<Map<String, dynamic>>()
          .map(DoctorSlotModel.fromJson)
          .toList(growable: false),
    );
  }
}
