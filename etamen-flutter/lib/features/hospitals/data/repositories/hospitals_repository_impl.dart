import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor.dart';
import 'package:etamen_app/features/hospitals/data/datasources/hospitals_remote_data_source.dart';
import 'package:etamen_app/features/hospitals/domain/entities/hospital.dart';
import 'package:etamen_app/features/hospitals/domain/entities/hospital_department.dart';
import 'package:etamen_app/features/hospitals/domain/repositories/hospitals_repository.dart';

class HospitalsRepositoryImpl implements HospitalsRepository {
  const HospitalsRepositoryImpl(this._remoteDataSource);

  final HospitalsRemoteDataSource _remoteDataSource;

  @override
  Future<ApiResult<List<Hospital>>> listHospitals() {
    return _remoteDataSource.hospitals();
  }

  @override
  Future<ApiResult<Hospital>> getHospital(int id) {
    return _remoteDataSource.hospital(id);
  }

  @override
  Future<ApiResult<List<HospitalDepartment>>> getDepartments(int hospitalId) {
    return _remoteDataSource.departments(hospitalId);
  }

  @override
  Future<ApiResult<List<Doctor>>> getDepartmentDoctors({
    required int hospitalId,
    required int departmentId,
  }) {
    return _remoteDataSource.departmentDoctors(
      hospitalId: hospitalId,
      departmentId: departmentId,
    );
  }

  @override
  Future<ApiResult<List<Doctor>>> getHospitalDoctors(int hospitalId) {
    return _remoteDataSource.hospitalDoctors(hospitalId);
  }
}
