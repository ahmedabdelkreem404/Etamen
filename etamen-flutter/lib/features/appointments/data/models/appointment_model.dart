import 'package:etamen_app/features/appointments/domain/entities/appointment.dart';

class AppointmentModel extends Appointment {
  const AppointmentModel({
    required super.id,
    required super.doctorProfileId,
    required super.appointmentSlotId,
    required super.consultationType,
    required super.price,
    required super.currency,
    required super.status,
    super.appointmentNumber,
    super.paymentId,
  });

  factory AppointmentModel.fromJson(Map<String, dynamic> json) {
    return AppointmentModel(
      id: (json['id'] as num).toInt(),
      appointmentNumber: json['appointment_number']?.toString(),
      doctorProfileId: (json['doctor_profile_id'] as num).toInt(),
      appointmentSlotId: (json['appointment_slot_id'] as num).toInt(),
      consultationType: json['consultation_type'] == 'online'
          ? ConsultationType.online
          : ConsultationType.clinic,
      price: (json['price'] ?? '0.00').toString(),
      currency: (json['currency'] ?? 'EGP').toString(),
      status: AppointmentStatus.fromWire(json['status']?.toString()),
      paymentId: json['payment_id'] == null
          ? null
          : (json['payment_id'] as num).toInt(),
    );
  }
}
