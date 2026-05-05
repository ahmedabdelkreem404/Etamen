import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor_slot.dart';

abstract class DoctorsRepository {
  Future<ApiResult<List<Doctor>>> listDoctors({
    int page,
    int perPage,
    String? search,
  });

  Future<ApiResult<Doctor>> getDoctor(int id);

  Future<ApiResult<List<DoctorSlot>>> getSlots(int doctorId);
}
