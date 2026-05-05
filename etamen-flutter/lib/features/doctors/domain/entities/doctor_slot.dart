class DoctorSlot {
  const DoctorSlot({
    required this.id,
    required this.doctorProfileId,
    required this.providerId,
    required this.startsAt,
    required this.endsAt,
    required this.status,
  });

  final int id;
  final int doctorProfileId;
  final int providerId;
  final DateTime startsAt;
  final DateTime endsAt;
  final String status;

  bool get isAvailable => status == 'available';
}
