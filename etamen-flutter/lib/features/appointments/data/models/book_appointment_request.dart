import 'package:etamen_app/features/appointments/domain/entities/appointment.dart';

class BookAppointmentRequest {
  const BookAppointmentRequest({
    required this.doctorProfileId,
    required this.appointmentSlotId,
    required this.consultationType,
    this.problemDescription,
  });

  final int doctorProfileId;
  final int appointmentSlotId;
  final ConsultationType consultationType;
  final String? problemDescription;

  Map<String, dynamic> toJson() {
    return {
      'doctor_profile_id': doctorProfileId,
      'appointment_slot_id': appointmentSlotId,
      'consultation_type': consultationType.wireValue,
      if (problemDescription != null && problemDescription!.trim().isNotEmpty)
        'problem_description': problemDescription!.trim(),
    };
  }
}
