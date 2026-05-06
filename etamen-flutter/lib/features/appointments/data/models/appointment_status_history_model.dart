import 'package:etamen_app/features/appointments/domain/entities/appointment_status_history.dart';

class AppointmentStatusHistoryModel extends AppointmentStatusHistory {
  const AppointmentStatusHistoryModel({
    required super.toStatus,
    super.fromStatus,
    super.reason,
    super.createdAt,
  });

  factory AppointmentStatusHistoryModel.fromJson(Map<String, dynamic> json) {
    return AppointmentStatusHistoryModel(
      fromStatus: json['from_status']?.toString(),
      toStatus: (json['to_status'] ?? json['status'] ?? 'unknown').toString(),
      reason: json['reason']?.toString(),
      createdAt: DateTime.tryParse((json['created_at'] ?? '').toString()),
    );
  }
}
