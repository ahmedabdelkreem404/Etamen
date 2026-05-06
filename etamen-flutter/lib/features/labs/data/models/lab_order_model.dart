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
