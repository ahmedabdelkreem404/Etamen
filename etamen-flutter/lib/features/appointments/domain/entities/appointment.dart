enum AppointmentStatus {
  draft,
  pendingPayment,
  pendingPaymentReview,
  confirmed,
  accepted,
  rejected,
  cancelledByPatient,
  cancelledByDoctor,
  completed,
  noShow,
  expired,
  unknown;

  static AppointmentStatus fromWire(String? value) {
    return switch (value) {
      'draft' => AppointmentStatus.draft,
      'pending_payment' => AppointmentStatus.pendingPayment,
      'pending_payment_review' => AppointmentStatus.pendingPaymentReview,
      'confirmed' => AppointmentStatus.confirmed,
      'accepted' => AppointmentStatus.accepted,
      'rejected' => AppointmentStatus.rejected,
      'cancelled_by_patient' => AppointmentStatus.cancelledByPatient,
      'cancelled_by_doctor' => AppointmentStatus.cancelledByDoctor,
      'completed' => AppointmentStatus.completed,
      'no_show' => AppointmentStatus.noShow,
      'expired' => AppointmentStatus.expired,
      _ => AppointmentStatus.unknown,
    };
  }
}

enum ConsultationType {
  clinic('clinic'),
  online('online');

  const ConsultationType(this.wireValue);

  final String wireValue;
}

class Appointment {
  const Appointment({
    required this.id,
    required this.doctorProfileId,
    required this.appointmentSlotId,
    required this.consultationType,
    required this.price,
    required this.currency,
    required this.status,
    this.appointmentNumber,
    this.paymentId,
  });

  final int id;
  final String? appointmentNumber;
  final int doctorProfileId;
  final int appointmentSlotId;
  final ConsultationType consultationType;
  final String price;
  final String currency;
  final AppointmentStatus status;
  final int? paymentId;

  bool get isPendingPayment => status == AppointmentStatus.pendingPayment;

  bool get isConfirmed => status == AppointmentStatus.confirmed;
}
