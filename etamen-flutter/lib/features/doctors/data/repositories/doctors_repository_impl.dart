import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/doctors/data/datasources/doctors_remote_data_source.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor_slot.dart';
import 'package:etamen_app/features/doctors/domain/repositories/doctors_repository.dart';

class DoctorsRepositoryImpl implements DoctorsRepository {
  const DoctorsRepositoryImpl(this._remoteDataSource);

  final DoctorsRemoteDataSource _remoteDataSource;

  @override
  Future<ApiResult<List<Doctor>>> listDoctors({
    int page = 1,
    int perPage = 20,
    String? search,
  }) {
    return _remoteDataSource.doctors(
      page: page,
      perPage: perPage,
      search: search,
    );
  }

  @override
  Future<ApiResult<Doctor>> getDoctor(int id) {
    return _remoteDataSource.doctor(id);
  }

  @override
  Future<ApiResult<List<DoctorSlot>>> getSlots(int doctorId) {
    return _remoteDataSource.slots(doctorId);
  }
}
