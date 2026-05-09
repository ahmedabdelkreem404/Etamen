import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/network/api_client.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/doctors/data/models/doctor_model.dart';
import 'package:etamen_app/features/hospitals/data/models/hospital_department_model.dart';
import 'package:etamen_app/features/hospitals/data/models/hospital_model.dart';

class HospitalsRemoteDataSource {
  HospitalsRemoteDataSource(this._client);

  final ApiClient _client;

  Future<ApiResult<List<HospitalModel>>> hospitals() {
    return _client.get<List<HospitalModel>>(
      ApiEndpoints.hospitals,
      parser: (raw) => (raw as List? ?? const [])
          .whereType<Map<String, dynamic>>()
          .map(HospitalModel.fromJson)
          .toList(growable: false),
    );
  }

  Future<ApiResult<HospitalModel>> hospital(int id) {
    return _client.get<HospitalModel>(
      ApiEndpoints.hospital(id),
      parser: (raw) => HospitalModel.fromJson(raw as Map<String, dynamic>),
    );
  }

  Future<ApiResult<List<HospitalDepartmentModel>>> departments(int hospitalId) {
    return _client.get<List<HospitalDepartmentModel>>(
      ApiEndpoints.hospitalDepartments(hospitalId),
      parser: (raw) => (raw as List? ?? const [])
          .whereType<Map<String, dynamic>>()
          .map(HospitalDepartmentModel.fromJson)
          .toList(growable: false),
    );
  }

  Future<ApiResult<List<DoctorModel>>> hospitalDoctors(int hospitalId) {
    return _client.get<List<DoctorModel>>(
      ApiEndpoints.hospitalDoctors(hospitalId),
      parser: _doctors,
    );
  }

  Future<ApiResult<List<DoctorModel>>> departmentDoctors({
    required int hospitalId,
    required int departmentId,
  }) {
    return _client.get<List<DoctorModel>>(
      ApiEndpoints.hospitalDepartmentDoctors(hospitalId, departmentId),
      parser: _doctors,
    );
  }

  List<DoctorModel> _doctors(Object? raw) => (raw as List? ?? const [])
      .whereType<Map<String, dynamic>>()
      .map(DoctorModel.fromJson)
      .toList(growable: false);
}
