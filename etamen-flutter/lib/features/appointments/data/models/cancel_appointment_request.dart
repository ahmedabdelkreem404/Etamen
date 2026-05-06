class CancelAppointmentRequest {
  const CancelAppointmentRequest({this.reason});

  final String? reason;

  Map<String, dynamic> toJson() {
    return {if (reason?.trim().isNotEmpty == true) 'reason': reason!.trim()};
  }
}
