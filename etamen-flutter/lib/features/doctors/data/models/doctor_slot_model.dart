import 'package:etamen_app/features/doctors/domain/entities/doctor_slot.dart';

class DoctorSlotModel extends DoctorSlot {
  const DoctorSlotModel({
    required super.id,
    required super.doctorProfileId,
    required super.providerId,
    required super.startsAt,
    required super.endsAt,
    required super.status,
  });

  factory DoctorSlotModel.fromJson(Map<String, dynamic> json) {
    return DoctorSlotModel(
      id: (json['id'] as num).toInt(),
      doctorProfileId: (json['doctor_profile_id'] as num).toInt(),
      providerId: (json['provider_id'] as num).toInt(),
      startsAt: DateTime.parse(json['starts_at'].toString()),
      endsAt: DateTime.parse(json['ends_at'].toString()),
      status: (json['status'] ?? '').toString(),
    );
  }
}
