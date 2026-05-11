import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';

class PaymentStatusModel extends PaymentStatusDetails {
  const PaymentStatusModel({
    required super.id,
    required super.status,
    required super.amount,
    required super.currency,
    required super.payableType,
    required super.payableId,
    super.methodType,
    super.appointmentStatus,
    super.radiologyOrderStatus,
    super.pharmacyOrderStatus,
    super.pharmacyPaymentStatus,
    super.labOrderStatus,
    super.labPaymentStatus,
    super.gymBookingStatus,
    super.coachBookingStatus,
    super.invoice,
    super.rejectionReason,
    super.createdAt,
    super.updatedAt,
  });

  factory PaymentStatusModel.fromJson(Map<String, dynamic> json) {
    final payable = _asMap(json['payable']);
    final appointment = _asMap(json['appointment']);
    final pharmacyOrder = _asMap(json['pharmacy_order']);
    final labOrder = _asMap(json['lab_order']);
    final radiologyOrder = _asMap(json['radiology_order']);
    final gymBooking = _asMap(json['gym_booking']);
    final coachBooking = _asMap(json['coach_booking']);
    final paymentMethod = _asMap(json['payment_method']);
    final proof = _asMap(json['proof']) ?? _asMap(json['latest_proof']);
    final metadata = _asMap(json['metadata']);

    return PaymentStatusModel(
      id: (json['id'] as num).toInt(),
      status: PaymentStatusEnum.fromWire(json['status']?.toString()),
      amount: (json['amount'] ?? '0.00').toString(),
      currency: (json['currency'] ?? 'EGP').toString(),
      methodType:
          (json['method_type'] ??
                  json['payment_method_type'] ??
                  paymentMethod?['type'])
              ?.toString(),
      payableType: (json['payable_type'] ?? payable?['type'])?.toString(),
      payableId: _toInt(json['payable_id'] ?? payable?['id']),
      appointmentStatus:
          (json['appointment_status'] ??
                  appointment?['status'] ??
                  payable?['appointment_status'])
              ?.toString(),
      radiologyOrderStatus:
          (json['radiology_order_status'] ??
                  radiologyOrder?['status'] ??
                  payable?['radiology_order_status'])
              ?.toString(),
      pharmacyOrderStatus:
          (json['pharmacy_order_status'] ??
                  pharmacyOrder?['order_status'] ??
                  pharmacyOrder?['status'] ??
                  payable?['pharmacy_order_status'])
              ?.toString(),
      pharmacyPaymentStatus:
          (json['pharmacy_payment_status'] ??
                  pharmacyOrder?['payment_status'] ??
                  payable?['pharmacy_payment_status'])
              ?.toString(),
      labOrderStatus:
          (json['lab_order_status'] ??
                  labOrder?['order_status'] ??
                  labOrder?['status'] ??
                  payable?['lab_order_status'])
              ?.toString(),
      labPaymentStatus:
          (json['lab_payment_status'] ??
                  labOrder?['payment_status'] ??
                  payable?['lab_payment_status'])
              ?.toString(),
      gymBookingStatus:
          (json['gym_booking_status'] ??
                  gymBooking?['status'] ??
                  payable?['gym_booking_status'])
              ?.toString(),
      coachBookingStatus:
          (json['coach_booking_status'] ??
                  coachBooking?['status'] ??
                  payable?['coach_booking_status'])
              ?.toString(),
      invoice: _asMap(json['invoice']),
      rejectionReason:
          (json['rejection_reason'] ??
                  proof?['rejection_reason'] ??
                  metadata?['rejection_reason'])
              ?.toString(),
      createdAt: DateTime.tryParse((json['created_at'] ?? '').toString()),
      updatedAt: DateTime.tryParse((json['updated_at'] ?? '').toString()),
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
}
