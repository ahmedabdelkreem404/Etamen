import 'package:etamen_app/features/appointments/domain/entities/appointment.dart';

class AppointmentModel extends Appointment {
  const AppointmentModel({
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
  });

  factory AppointmentModel.fromJson(Map<String, dynamic> json) {
    final doctor =
        _asMap(json['doctor']) ??
        _asMap(json['doctor_profile']) ??
        _asMap(json['provider']);
    final specialty = _asMap(doctor?['specialty']);
    final slot = _asMap(json['slot']) ?? _asMap(json['appointment_slot']);
    final branch = _asMap(json['branch']) ?? _asMap(json['provider_branch']);
    final city = _asMap(branch?['city']);
    final area = _asMap(branch?['area']);
    final payment = _asMap(json['payment']);
    final hospital = _asMap(json['hospital']);
    final department =
        _asMap(json['department']) ?? _asMap(json['hospital_department']);

    return AppointmentModel(
      id: (json['id'] as num).toInt(),
      appointmentNumber: json['appointment_number']?.toString(),
      doctorProfileId: _toInt(json['doctor_profile_id'] ?? doctor?['id']) ?? 0,
      appointmentSlotId:
          _toInt(json['appointment_slot_id'] ?? slot?['id']) ?? 0,
      consultationType: json['consultation_type'] == 'online'
          ? ConsultationType.online
          : ConsultationType.clinic,
      price: (json['price'] ?? json['amount'] ?? payment?['amount'] ?? '0.00')
          .toString(),
      currency: (json['currency'] ?? 'EGP').toString(),
      status: AppointmentStatus.fromWire(json['status']?.toString()),
      paymentId: _toInt(json['payment_id'] ?? payment?['id']),
      doctorName: _firstString([
        json['doctor_name'],
        doctor?['name_ar'],
        doctor?['name_en'],
        doctor?['name'],
      ]),
      specialty: _firstString([
        json['specialty'],
        specialty?['name_ar'],
        specialty?['name_en'],
        specialty?['name'],
      ]),
      startsAt: _date(
        json['starts_at'] ?? json['start_at'] ?? slot?['starts_at'],
      ),
      endsAt: _date(json['ends_at'] ?? json['end_at'] ?? slot?['ends_at']),
      paymentStatus: (json['payment_status'] ?? payment?['status'])?.toString(),
      location: _buildLocation(branch: branch, city: city, area: area),
      bookedThroughHospital:
          json['booked_through_hospital'] == true ||
          _toInt(json['hospital_provider_id'] ?? hospital?['id']) != null,
      hospitalId: _toInt(json['hospital_provider_id'] ?? hospital?['id']),
      hospitalName: _firstString([
        hospital?['name_ar'],
        hospital?['name_en'],
        hospital?['name'],
      ]),
      departmentId: _toInt(json['hospital_department_id'] ?? department?['id']),
      departmentName: _firstString([
        department?['name_ar'],
        department?['name_en'],
        department?['name'],
      ]),
      canCancel: json['can_cancel'] is bool ? json['can_cancel'] as bool : null,
      createdAt: _date(json['created_at']),
    );
  }

  static Map<String, dynamic>? _asMap(Object? value) {
    if (value is Map<String, dynamic>) return value;
    if (value is Map) {
      return value.map((key, item) => MapEntry(key.toString(), item));
    }
    return null;
  }

  static int? _toInt(Object? value) {
    if (value == null) return null;
    if (value is num) return value.toInt();
    return int.tryParse(value.toString());
  }

  static DateTime? _date(Object? value) {
    if (value == null) return null;
    return DateTime.tryParse(value.toString());
  }

  static String? _firstString(List<Object?> values) {
    for (final value in values) {
      final text = value?.toString().trim();
      if (text != null && text.isNotEmpty && text != 'null') return text;
    }
    return null;
  }

  static String? _buildLocation({
    Map<String, dynamic>? branch,
    Map<String, dynamic>? city,
    Map<String, dynamic>? area,
  }) {
    final direct = _firstString([
      branch?['address_ar'],
      branch?['address_en'],
      branch?['address'],
      branch?['name_ar'],
      branch?['name_en'],
    ]);
    final cityName = _firstString([city?['name_ar'], city?['name_en']]);
    final areaName = _firstString([area?['name_ar'], area?['name_en']]);
    final parts = [
      direct,
      areaName,
      cityName,
    ].where((item) => item != null && item.isNotEmpty).cast<String>().toList();
    return parts.isEmpty ? null : parts.join(' - ');
  }
}
