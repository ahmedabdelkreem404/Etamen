import 'package:etamen_app/features/pharmacy/data/models/pharmacy_order_item_model.dart';
import 'package:etamen_app/features/pharmacy/data/models/pharmacy_prescription_model.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_order.dart';

class PharmacyOrderModel extends PharmacyOrder {
  const PharmacyOrderModel({
    required super.id,
    required super.status,
    required super.items,
    super.orderNumber,
    super.pharmacyName,
    super.paymentStatus,
    super.paymentId,
    super.subtotal,
    super.discountTotal,
    super.grandTotal,
    super.currency,
    super.createdAt,
    super.prescription,
    super.notes,
  });

  factory PharmacyOrderModel.fromJson(Map<String, dynamic> json) {
    final pharmacy = _asMap(json['pharmacy']) ?? _asMap(json['provider']);
    final prescription = _asMap(json['prescription']);
    final items = (json['items'] is List ? json['items'] as List : const [])
        .whereType<Map>()
        .map(
          (item) => PharmacyOrderItemModel.fromJson(
            item.map((key, value) => MapEntry(key.toString(), value)),
          ),
        )
        .toList(growable: false);

    return PharmacyOrderModel(
      id: (json['id'] as num).toInt(),
      orderNumber: json['order_number']?.toString(),
      pharmacyName:
          (json['pharmacy_name'] ??
                  pharmacy?['name_ar'] ??
                  pharmacy?['name_en'] ??
                  pharmacy?['name'])
              ?.toString(),
      status: PharmacyOrderStatus.fromWire(
        (json['order_status'] ?? json['status'])?.toString(),
      ),
      paymentStatus: json['payment_status']?.toString(),
      paymentId: _toInt(json['payment_id'] ?? _asMap(json['payment'])?['id']),
      subtotal: json['subtotal']?.toString(),
      discountTotal: json['discount_total']?.toString(),
      grandTotal: json['grand_total']?.toString(),
      currency: (json['currency'] ?? 'EGP').toString(),
      createdAt: DateTime.tryParse((json['created_at'] ?? '').toString()),
      items: items,
      prescription: prescription == null
          ? null
          : PharmacyPrescriptionModel.fromJson(prescription),
      notes: json['notes']?.toString(),
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
