import 'package:etamen_app/features/appointments/data/models/appointment_model.dart';
import 'package:etamen_app/features/appointments/data/models/appointment_status_history_model.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment_details.dart';

class AppointmentDetailsModel extends AppointmentDetails {
  const AppointmentDetailsModel({
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
    super.canCancel,
    super.createdAt,
    super.problemDescription,
    super.notes,
    super.doctorBio,
    super.statusHistory,
  });

  factory AppointmentDetailsModel.fromJson(Map<String, dynamic> json) {
    final base = AppointmentModel.fromJson(json);
    final doctor =
        _asMap(json['doctor']) ??
        _asMap(json['doctor_profile']) ??
        _asMap(json['provider']);
    final histories =
        (json['status_history'] ??
                json['status_histories'] ??
                json['histories'] ??
                const [])
            as Object?;

    return AppointmentDetailsModel(
      id: base.id,
      appointmentNumber: base.appointmentNumber,
      doctorProfileId: base.doctorProfileId,
      appointmentSlotId: base.appointmentSlotId,
      consultationType: base.consultationType,
      price: base.price,
      currency: base.currency,
      status: base.status,
      paymentId: base.paymentId,
      doctorName: base.doctorName,
      specialty: base.specialty,
      startsAt: base.startsAt,
      endsAt: base.endsAt,
      paymentStatus: base.paymentStatus,
      location: base.location,
      canCancel: base.canCancel,
      createdAt: base.createdAt,
      problemDescription: json['problem_description']?.toString(),
      notes: json['notes']?.toString(),
      doctorBio: (doctor?['bio_ar'] ?? doctor?['bio_en'])?.toString(),
      statusHistory: _parseHistory(histories),
    );
  }

  static Map<String, dynamic>? _asMap(Object? value) {
    if (value is Map<String, dynamic>) return value;
    if (value is Map) {
      return value.map((key, item) => MapEntry(key.toString(), item));
    }
    return null;
  }

  static List<AppointmentStatusHistoryModel> _parseHistory(Object? value) {
    if (value is! List) return const [];
    return value
        .whereType<Map>()
        .map(
          (item) => AppointmentStatusHistoryModel.fromJson(
            item.map((key, value) => MapEntry(key.toString(), value)),
          ),
        )
        .toList(growable: false);
  }
}
