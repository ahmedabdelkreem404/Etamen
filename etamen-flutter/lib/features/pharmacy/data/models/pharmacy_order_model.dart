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
    super.statusLabelAr,
    super.statusLabelEn,
    super.paymentStatusLabelAr,
    super.paymentStatusLabelEn,
    super.serverCanCancel,
    super.serverCanPay,
    super.serverCanUploadProof,
    super.nextActionKey,
    super.nextActionLabelAr,
    super.nextActionLabelEn,
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
      statusLabelAr: json['status_label_ar']?.toString(),
      statusLabelEn: json['status_label_en']?.toString(),
      paymentStatusLabelAr: json['payment_status_label_ar']?.toString(),
      paymentStatusLabelEn: json['payment_status_label_en']?.toString(),
      serverCanCancel: _toBool(json['can_cancel']),
      serverCanPay: _toBool(json['can_pay']),
      serverCanUploadProof: _toBool(json['can_upload_proof']),
      nextActionKey: json['next_action_key']?.toString(),
      nextActionLabelAr: json['next_action_label_ar']?.toString(),
      nextActionLabelEn: json['next_action_label_en']?.toString(),
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

  static bool? _toBool(Object? value) {
    if (value == null) return null;
    if (value is bool) return value;
    if (value is num) return value != 0;
    final text = value.toString().toLowerCase();
    if (text == 'true' || text == '1') return true;
    if (text == 'false' || text == '0') return false;
    return null;
  }
}
