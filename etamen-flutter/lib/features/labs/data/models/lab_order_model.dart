import 'package:etamen_app/features/labs/data/models/lab_order_item_model.dart';
import 'package:etamen_app/features/labs/data/models/lab_result_model.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_order.dart';

class LabOrderModel extends LabOrder {
  const LabOrderModel({
    required super.id,
    required super.status,
    required super.items,
    super.orderNumber,
    super.labName,
    super.paymentStatus,
    super.paymentId,
    super.subtotal,
    super.grandTotal,
    super.currency,
    super.sampleCollectionMethod,
    super.homeAddress,
    super.createdAt,
    super.results,
    super.notes,
    super.statusLabelAr,
    super.statusLabelEn,
    super.paymentStatusLabelAr,
    super.paymentStatusLabelEn,
    super.serverCanCancel,
    super.serverCanPay,
    super.serverCanUploadProof,
    super.serverCanViewResultMetadata,
    super.nextActionKey,
    super.nextActionLabelAr,
    super.nextActionLabelEn,
  });

  factory LabOrderModel.fromJson(Map<String, dynamic> json) {
    final lab = _asMap(json['lab']) ?? _asMap(json['provider']);
    final items = (json['items'] is List ? json['items'] as List : const [])
        .whereType<Map>()
        .map(
          (item) => LabOrderItemModel.fromJson(
            item.map((key, value) => MapEntry(key.toString(), value)),
          ),
        )
        .toList(growable: false);
    final results =
        (json['results'] is List ? json['results'] as List : const [])
            .whereType<Map>()
            .map(
              (item) => LabResultModel.fromJson(
                item.map((key, value) => MapEntry(key.toString(), value)),
              ),
            )
            .toList(growable: false);

    return LabOrderModel(
      id: (json['id'] as num).toInt(),
      orderNumber: json['order_number']?.toString(),
      labName:
          (json['lab_name'] ??
                  lab?['name_ar'] ??
                  lab?['name_en'] ??
                  lab?['name'])
              ?.toString(),
      status: LabOrderStatus.fromWire(
        (json['order_status'] ?? json['status'])?.toString(),
      ),
      paymentStatus: json['payment_status']?.toString(),
      paymentId: _toInt(json['payment_id'] ?? _asMap(json['payment'])?['id']),
      subtotal: json['subtotal']?.toString(),
      grandTotal: json['grand_total']?.toString(),
      currency: (json['currency'] ?? 'EGP').toString(),
      sampleCollectionMethod:
          (json['sample_collection_method'] ?? json['collection_method'])
              ?.toString(),
      homeAddress: (json['collection_address'] ?? json['home_address'])
          ?.toString(),
      createdAt: DateTime.tryParse((json['created_at'] ?? '').toString()),
      items: items,
      results: results,
      notes: json['notes']?.toString(),
      statusLabelAr: json['status_label_ar']?.toString(),
      statusLabelEn: json['status_label_en']?.toString(),
      paymentStatusLabelAr: json['payment_status_label_ar']?.toString(),
      paymentStatusLabelEn: json['payment_status_label_en']?.toString(),
      serverCanCancel: _toBool(json['can_cancel']),
      serverCanPay: _toBool(json['can_pay']),
      serverCanUploadProof: _toBool(json['can_upload_proof']),
      serverCanViewResultMetadata: _toBool(json['can_view_result_metadata']),
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
