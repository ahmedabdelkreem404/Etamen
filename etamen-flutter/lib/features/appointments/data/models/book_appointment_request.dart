import 'package:etamen_app/features/appointments/domain/entities/appointment.dart';
import 'package:etamen_app/features/appointments/domain/entities/hospital_booking_context.dart';

class BookAppointmentRequest {
  const BookAppointmentRequest({
    required this.doctorProfileId,
    required this.appointmentSlotId,
    required this.consultationType,
    this.problemDescription,
    this.hospitalContext,
  });

  final int doctorProfileId;
  final int appointmentSlotId;
  final ConsultationType consultationType;
  final String? problemDescription;
  final HospitalBookingContext? hospitalContext;

  Map<String, dynamic> toJson() {
    return {
      'doctor_profile_id': doctorProfileId,
      'appointment_slot_id': appointmentSlotId,
      'consultation_type': consultationType.wireValue,
      if (hospitalContext != null) ...hospitalContext!.toRequestJson(),
      if (problemDescription != null && problemDescription!.trim().isNotEmpty)
        'problem_description': problemDescription!.trim(),
    };
  }
}
