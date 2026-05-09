enum AppointmentStatus {
  draft,
  pendingPayment,
  pendingPaymentReview,
  confirmed,
  accepted,
  rejected,
  cancelled,
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
      'cancelled' => AppointmentStatus.cancelled,
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
    this.doctorName,
    this.specialty,
    this.startsAt,
    this.endsAt,
    this.paymentStatus,
    this.location,
    this.bookedThroughHospital = false,
    this.hospitalId,
    this.hospitalName,
    this.departmentId,
    this.departmentName,
    this.canCancel,
    this.createdAt,
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
  final String? doctorName;
  final String? specialty;
  final DateTime? startsAt;
  final DateTime? endsAt;
  final String? paymentStatus;
  final String? location;
  final bool bookedThroughHospital;
  final int? hospitalId;
  final String? hospitalName;
  final int? departmentId;
  final String? departmentName;
  final bool? canCancel;
  final DateTime? createdAt;

  bool get isPendingPayment => status == AppointmentStatus.pendingPayment;

  bool get isConfirmed => status == AppointmentStatus.confirmed;

  bool get isUpcoming {
    return status == AppointmentStatus.confirmed ||
        status == AppointmentStatus.accepted;
  }

  bool get isCompleted => status == AppointmentStatus.completed;

  bool get isCancelled {
    return status == AppointmentStatus.cancelled ||
        status == AppointmentStatus.cancelledByPatient ||
        status == AppointmentStatus.cancelledByDoctor ||
        status == AppointmentStatus.rejected ||
        status == AppointmentStatus.noShow ||
        status == AppointmentStatus.expired;
  }

  bool get isCancellable {
    if (canCancel != null) return canCancel!;
    return status == AppointmentStatus.confirmed ||
        status == AppointmentStatus.accepted ||
        status == AppointmentStatus.pendingPayment ||
        status == AppointmentStatus.pendingPaymentReview;
  }
}
