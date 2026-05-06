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
    super.invoice,
    super.rejectionReason,
    super.createdAt,
    super.updatedAt,
  });

  factory PaymentStatusModel.fromJson(Map<String, dynamic> json) {
    final payable = _asMap(json['payable']);
    final appointment = _asMap(json['appointment']);
    final proof = _asMap(json['proof']) ?? _asMap(json['latest_proof']);
    final metadata = _asMap(json['metadata']);

    return PaymentStatusModel(
      id: (json['id'] as num).toInt(),
      status: PaymentStatusEnum.fromWire(json['status']?.toString()),
      amount: (json['amount'] ?? '0.00').toString(),
      currency: (json['currency'] ?? 'EGP').toString(),
      methodType: (json['method_type'] ?? json['payment_method_type'])
          ?.toString(),
      payableType: (json['payable_type'] ?? payable?['type'])?.toString(),
      payableId: _toInt(json['payable_id'] ?? payable?['id']),
      appointmentStatus:
          (json['appointment_status'] ??
                  appointment?['status'] ??
                  payable?['appointment_status'])
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
