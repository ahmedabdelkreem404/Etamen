import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor.dart';
import 'package:etamen_app/features/hospitals/domain/entities/hospital.dart';
import 'package:etamen_app/features/hospitals/domain/entities/hospital_department.dart';

abstract class HospitalsRepository {
  Future<ApiResult<List<Hospital>>> listHospitals();

  Future<ApiResult<Hospital>> getHospital(int id);

  Future<ApiResult<List<HospitalDepartment>>> getDepartments(int hospitalId);

  Future<ApiResult<List<Doctor>>> getDepartmentDoctors({
    required int hospitalId,
    required int departmentId,
  });

  Future<ApiResult<List<Doctor>>> getHospitalDoctors(int hospitalId);
}
