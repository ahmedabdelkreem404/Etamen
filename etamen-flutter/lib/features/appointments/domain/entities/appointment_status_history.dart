class AppointmentStatusHistory {
  const AppointmentStatusHistory({
    required this.toStatus,
    this.fromStatus,
    this.reason,
    this.createdAt,
  });

  final String? fromStatus;
  final String toStatus;
  final String? reason;
  final DateTime? createdAt;
}
