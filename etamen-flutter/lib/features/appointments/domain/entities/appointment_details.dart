import 'package:etamen_app/features/appointments/domain/entities/appointment.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment_status_history.dart';

class AppointmentDetails extends Appointment {
  const AppointmentDetails({
    required super.id,
    required super.doctorProfileId,
    required super.appointmentSlotId,
    required super.consultationType,
    required super.price,
    required super.currency,
    required super.status,
    super.appointmentNumber,
    super.paymentId,
    super.doctorName,
    super.specialty,
    super.startsAt,
    super.endsAt,
    super.paymentStatus,
    super.location,
    super.bookedThroughHospital,
    super.hospitalId,
    super.hospitalName,
    super.departmentId,
    super.departmentName,
    super.canCancel,
    super.createdAt,
    this.problemDescription,
    this.notes,
    this.doctorBio,
    this.statusHistory = const [],
  });

  final String? problemDescription;
  final String? notes;
  final String? doctorBio;
  final List<AppointmentStatusHistory> statusHistory;
}
